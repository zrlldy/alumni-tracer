<?php

use App\Filament\Pages\Register;
use App\Models\User;
use App\Notifications\BrandedEmailVerification;
use App\Support\SystemEncrypter;
use App\Support\Terms;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    Filament::setCurrentPanel('online');
    config()->set('allowed-email.domains', ['example.test']);
    config()->set('app.developer_host', 'developer.example.test');
    config()->set('app.system_key', 'synthetic-terms-key');
    config()->set('app.super_admin_emails_encrypted', SystemEncrypter::encryptSuperAdminData(''));
    config()->set('system.seal', SystemEncrypter::getSeal());
});

it('publishes every supplied paragraph without requiring an account', function () {
    expect(hash('sha256', implode("\n", array_column(Terms::document(), 'text'))))
        ->toBe('148e540487aecf2c4077fdd76c3fa31e04cc512fe51f043e0d457f4fd33accb6');
    $response = $this->get(route('terms.show'))->assertOk();
    foreach (Terms::document() as $block) {
        $response->assertSee($block['text']);
    }
    $response->assertSee('Effective Date: December 2026')->assertDontSee('Agree and continue');
    $this->get('/')->assertOk()->assertSee(route('terms.show'));
    $this->get(Filament::getRegistrationUrl())->assertOk()->assertSee(route('terms.show'));
});

it('requires authentication for reviewing and saving acceptance', function () {
    $this->get(route('terms.accept'))->assertRedirect(Filament::getLoginUrl());
    $this->post(route('terms.store'), ['terms' => 1, 'version' => Terms::VERSION])
        ->assertRedirect(Filament::getLoginUrl());
});

it('requires agreement and the version the user reviewed', function (array $payload, string $field) {
    $user = User::factory()->withoutTermsAcceptance()->create(['email' => 'pending@example.test']);
    $this->actingAs($user)->from(route('terms.accept'))->post(route('terms.store'), $payload)
        ->assertRedirect(route('terms.accept'))->assertSessionHasErrors($field);
    expect($user->fresh()->terms_accepted_at)->toBeNull();
})->with([
    'unchecked' => [['version' => Terms::VERSION], 'terms'],
    'declined' => [['terms' => 0, 'version' => Terms::VERSION], 'terms'],
    'missing version' => [['terms' => 1], 'version'],
    'outdated version' => [['terms' => 1, 'version' => 'old-version'], 'version'],
]);

it('records only the signed-in account agreement and preserves its timestamp on repeat submissions', function () {
    $this->freezeTime();
    $user = User::factory()->withoutTermsAcceptance()->create(['email' => 'agree@example.test']);
    $other = User::factory()->withoutTermsAcceptance()->create();
    $this->actingAs($user)->post(route('terms.store'), [
        'terms' => 1, 'version' => Terms::VERSION, 'user_id' => $other->id,
        'terms_accepted_at' => '2000-01-01', 'user_allocation_id' => 999,
    ])->assertRedirect(Filament::getUrl());
    expect($user->fresh()->terms_accepted_version)->toBe(Terms::VERSION)
        ->and($user->fresh()->terms_accepted_at->toDateTimeString())->toBe(now()->toDateTimeString())
        ->and($user->fresh()->user_allocation_id)->toBeNull()
        ->and($other->fresh()->terms_accepted_at)->toBeNull();
    $acceptedAt = $user->fresh()->terms_accepted_at;
    $this->travel(1)->hour();
    $this->post(route('terms.store'), ['terms' => 1, 'version' => Terms::VERSION])->assertRedirect();
    expect($user->fresh()->terms_accepted_at->equalTo($acceptedAt))->toBeTrue();
    $this->get(route('terms.accept'))->assertRedirect(Filament::getUrl());
});

it('requires current acceptance before protected pages while allowing review and sign-out', function () {
    $user = User::factory()->withoutTermsAcceptance()->create(['email' => 'existing@example.test']);
    $this->actingAs($user)->get(Filament::getUrl())->assertRedirect(route('terms.accept'));
    $this->get(route('filament.online.auth.profile'))->assertRedirect(route('terms.accept'));
    $this->get(route('terms.accept'))->assertOk()->assertSee('Agree and continue')->assertSee('Sign out');
    $this->post(route('filament.online.auth.logout'))->assertRedirect();
    $this->assertGuest();
});

it('requires a new agreement for outdated versions and incomplete acceptance records', function (array $attributes) {
    $user = User::factory()->create(['email' => 'outdated@example.test', ...$attributes]);
    $this->actingAs($user)->get(Filament::getUrl())->assertRedirect(route('terms.accept'));
})->with([
    'old version' => [['terms_accepted_version' => 'old-version']],
    'missing timestamp' => [['terms_accepted_at' => null]],
]);

it('allows an accepted account into its existing roleless flow and exposes the terms menu', function () {
    $user = User::factory()->create(['email' => 'accepted@example.test']);
    $this->actingAs($user)->get(Filament::getUrl())->assertRedirect(route('filament.online.pages.get-started'));
    $this->get(route('filament.online.pages.get-started'))->assertOk()->assertSee(route('terms.show'));
});

it('does not create a password account without acceptance', function () {
    Livewire::test(Register::class)->fillForm([
        'name' => 'Synthetic Registrant', 'email' => 'register@example.test',
        'password' => 'synthetic-password-123', 'passwordConfirmation' => 'synthetic-password-123',
        'terms' => false,
    ])->call('register')->assertHasFormErrors(['terms' => 'accepted']);
    $this->assertDatabaseMissing('users', ['email' => 'register@example.test']);
});

it('records acceptance with password registration and sends verification', function () {
    Notification::fake();
    $this->freezeTime();
    Livewire::test(Register::class)->fillForm([
        'name' => 'Synthetic Registrant', 'email' => 'register@example.test',
        'password' => 'synthetic-password-123', 'passwordConfirmation' => 'synthetic-password-123',
        'terms' => true,
    ])->call('register')->assertHasNoFormErrors();
    $user = User::where('email', 'register@example.test')->sole();
    expect($user->terms_accepted_version)->toBe(Terms::VERSION)
        ->and($user->terms_accepted_at->toDateTimeString())->toBe(now()->toDateTimeString())
        ->and($user->email_verified_at)->toBeNull();
    Notification::assertSentTo($user, BrandedEmailVerification::class);
});

it('rejects password registration when its displayed terms version is stale', function () {
    Livewire::test(Register::class)->fillForm([
        'name' => 'Synthetic Registrant', 'email' => 'stale@example.test',
        'password' => 'synthetic-password-123', 'passwordConfirmation' => 'synthetic-password-123',
        'terms' => true, 'terms_version' => 'old-version',
    ])->call('register')->assertHasFormErrors(['terms_version' => 'in']);
    $this->assertDatabaseMissing('users', ['email' => 'stale@example.test']);
});

it('applies acceptance to Google sign-in for new and existing accounts', function (bool $existing, bool $accepted) {
    $googleUser = (new GoogleUser)->map([
        'id' => 'synthetic-google-id', 'name' => 'Synthetic Google User', 'email' => 'google@example.test',
    ]);
    $googleUser->user = ['email_verified' => true];
    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('user')->once()->andReturn($googleUser);
    Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);
    if ($existing) {
        $factory = User::factory();
        if (! $accepted) {
            $factory = $factory->withoutTermsAcceptance();
        }
        $factory->create(['email' => 'google@example.test', 'google_id' => 'synthetic-google-id']);
    }
    $this->get(route('auth.google.callback', ['code' => 'synthetic-code']))
        ->assertRedirect($accepted ? Filament::getUrl() : route('terms.accept'));
    $user = User::where('email', 'google@example.test')->sole();
    $this->assertAuthenticatedAs($user);
    expect($user->hasAcceptedCurrentTerms())->toBe($accepted);
    $this->get(Filament::getUrl())->assertRedirect($accepted ? route('filament.online.pages.get-started') : route('terms.accept'));
})->with([
    'new' => [false, false],
    'existing without agreement' => [true, false],
    'existing with agreement' => [true, true],
]);

it('keeps email verification available before acceptance and still requires it after agreement', function () {
    $user = User::factory()->withoutTermsAcceptance()->unverified()->create(['email' => 'unverified@example.test']);
    $this->actingAs($user)->get(Filament::getEmailVerificationPromptUrl())->assertOk();
    $this->post(route('terms.store'), ['terms' => 1, 'version' => Terms::VERSION])
        ->assertRedirect(Filament::getUrl());
    $this->get(Filament::getUrl())->assertRedirect(Filament::getEmailVerificationPromptUrl());
    expect($user->fresh()->email_verified_at)->toBeNull();
});

it('rechecks acceptance on subsequent Livewire requests from a previously allowed page', function () {
    $user = User::factory()->create(['email' => 'mounted@example.test']);
    $response = $this->actingAs($user)->get(route('filament.online.pages.get-started'))->assertOk();
    preg_match('/wire:snapshot="([^"]+)"/', $response->getContent(), $matches);
    expect($matches)->toHaveCount(2);
    $snapshot = html_entity_decode($matches[1], ENT_QUOTES);
    $user->forceFill(['terms_accepted_version' => 'old-version'])->save();

    $this->postJson(route('default-livewire.update'), [
        'components' => [[
            'snapshot' => $snapshot,
            'updates' => [],
            'calls' => [['path' => '', 'method' => '$refresh', 'params' => []]],
        ]],
    ], ['X-Livewire' => 'true'])->assertRedirect(route('terms.accept'));
});
