<?php

use App\Filament\Pages\GetStarted;
use App\Models\Role;
use App\Models\User;
use App\Models\UserAllocation;
use App\Support\SystemEncrypter;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    Filament::setCurrentPanel('online');
    config()->set('allowed-email.domains', ['example.test']);
    config()->set('app.developer_host', 'developer.example.test');
    config()->set('app.system_key', 'synthetic-get-started-key');
    config()->set('app.super_admin_emails_encrypted', SystemEncrypter::encryptSuperAdminData(''));
    config()->set('system.seal', SystemEncrypter::getSeal());
});

it('shows access instructions and the thirty-day notice to a roleless account', function () {
    $user = User::factory()->create(['email' => 'alex@example.test', 'user_allocation_id' => null]);

    $this->actingAs($user)->get(GetStarted::getUrl())
        ->assertSee('Welcome to Alumni Tracer')
        ->assertSee('Waiting for a role')
        ->assertSee('Alumni and graduates')
        ->assertSee('School staff')
        ->assertSee('Contact a system administrator')
        ->assertSee('30 days')
        ->assertSee('deleted')
        ->assertSee($user->email);

    $this->assertModelExists($user);
    expect($user->fresh()->user_allocation_id)->toBeNull();
});

it('redirects the roleless dashboard request to the absolute onboarding route', function () {
    $user = User::factory()->create(['email' => 'pending@example.test', 'user_allocation_id' => null]);

    $this->actingAs($user)->get(route('filament.online.pages.dashboard'))
        ->assertRedirect(GetStarted::getUrl());
});

it('requires sign-in and email verification before onboarding', function () {
    $this->get(GetStarted::getUrl())->assertRedirect(Filament::getLoginUrl());

    $user = User::factory()->unverified()->create(['email' => 'unverified@example.test']);

    $this->actingAs($user)->get(GetStarted::getUrl())
        ->assertRedirect(Filament::getEmailVerificationPromptUrl());
});

it('keeps instructions available on return visits while the account has no role', function () {
    $user = User::factory()->create([
        'email' => 'returning@example.test',
        'user_allocation_id' => null,
        'created_at' => now()->subDays(15),
    ]);

    $this->actingAs($user)->get(GetStarted::getUrl())->assertSee('Waiting for a role');
});

it('hides and denies onboarding after an administrator assigns a role', function () {
    $user = User::factory()->create(['email' => 'assigned@example.test', 'user_allocation_id' => null]);
    $component = Livewire::actingAs($user)->test(GetStarted::class);
    $role = Role::query()->create(['name' => 'Synthetic staff', 'access_page' => [], 'action' => []]);
    $allocation = UserAllocation::query()->create([
        'role_id' => $role->id,
        'management_scope' => 'all_program',
        'max_users' => 10,
    ]);
    $user->update(['user_allocation_id' => $allocation->id]);

    expect(GetStarted::shouldRegisterNavigation())->toBeFalse();
    $component->call('$refresh')->assertStatus(404);
    $this->get(GetStarted::getUrl())->assertNotFound();
});

it('escapes the signed-in account details', function () {
    $user = User::factory()->create([
        'name' => '<script>alert("name")</script>',
        'email' => 'safe@example.test',
        'user_allocation_id' => null,
    ]);

    $this->actingAs($user)->get(GetStarted::getUrl())
        ->assertSee($user->email)
        ->assertDontSee($user->name, false);
});
