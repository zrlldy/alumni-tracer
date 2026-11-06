<?php

use App\Livewire\VerifyEmail;
use App\Models\User;
use App\Notifications\BrandedEmailVerification;
use App\Support\SystemEncrypter;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    Filament::setCurrentPanel('online');
    config()->set('allowed-email.domains', ['example.test']);
    config()->set('app.developer_host', 'developer.example.test');
    config()->set('app.system_key', 'synthetic-verification-prompt-key');
    config()->set('app.super_admin_emails_encrypted', SystemEncrypter::encryptSuperAdminData(''));
    config()->set('system.seal', SystemEncrypter::getSeal());
});

it('renders the redesigned prompt for the signed-in unverified account', function () {
    $user = User::factory()->unverified()->create(['email' => 'alex.santos@example.test']);

    $this->actingAs($user)->get(Filament::getEmailVerificationPromptUrl())
        ->assertSee('Check your inbox')
        ->assertSee($user->email)
        ->assertSee('Resend verification email')
        ->assertSee('spam or junk folder')
        ->assertSee('Sign out')
        ->assertSee(Filament::getLogoutUrl(), false);
});

it('requires authentication before showing the verification prompt', function () {
    $this->get(Filament::getEmailVerificationPromptUrl())->assertRedirect(Filament::getLoginUrl());
});

it('redirects verified accounts away from the prompt', function () {
    $user = User::factory()->create(['email' => 'verified@example.test']);

    $this->actingAs($user)->get(Filament::getEmailVerificationPromptUrl())->assertRedirect(Filament::getUrl());
});

it('resends the existing branded signed link only to the signed-in account', function () {
    $this->freezeTime();
    $user = User::factory()->unverified()->create(['email' => 'alex.santos@example.test']);
    Notification::fake();

    Livewire::actingAs($user)->test(VerifyEmail::class)
        ->callAction('resendNotification')
        ->assertNotified("We've resent the email.");

    Notification::assertSentTo($user, BrandedEmailVerification::class, function (BrandedEmailVerification $notification) use ($user): bool {
        return $notification->url === Filament::getVerifyEmailUrl($user);
    });
    Notification::assertCount(1);
    expect($user->fresh())
        ->email_verified_at->toBeNull()
        ->user_allocation_id->toBeNull();
});

it('preserves the existing per-account resend limit', function () {
    $user = User::factory()->unverified()->create(['email' => 'alex.santos@example.test']);
    $key = 'filament-resend-email-verification:'.$user->id;
    RateLimiter::hit($key);
    RateLimiter::hit($key);
    Notification::fake();

    Livewire::actingAs($user)->test(VerifyEmail::class)
        ->callAction('resendNotification')
        ->assertNotified('Too many resend attempts');

    Notification::assertNothingSent();
});

it('signs out through the existing POST logout endpoint', function () {
    $user = User::factory()->unverified()->create(['email' => 'wrong-account@example.test']);

    $this->actingAs($user)->post(Filament::getLogoutUrl())->assertRedirect(Filament::getLoginUrl());

    $this->assertGuest();
});

it('escapes an account email rendered in the prompt', function () {
    $user = User::factory()->unverified()->create(['email' => 'alex@example.test']);
    $user->email = '<script>alert("email")</script>@example.test';

    Livewire::actingAs($user)->test(VerifyEmail::class)
        ->assertSee($user->email)
        ->assertDontSeeHtml($user->email);
});
