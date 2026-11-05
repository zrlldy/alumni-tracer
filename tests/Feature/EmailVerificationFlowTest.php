<?php

use App\Models\User;
use App\Notifications\AlumniWelcome;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(LazilyRefreshDatabase::class);

it('verifies the signed email link for the signed-in account', function () {
    $this->withoutVite();
    config()->set('allowed-email.domains', ['example.test']);
    config()->set('app.developer_host', 'developer.example.test');
    Filament::setCurrentPanel('online');

    $user = User::factory()->unverified()->create([
        'email' => 'synthetic-verify@example.test',
    ]);
    $verificationUrl = Filament::getVerifyEmailUrl($user);
    Notification::fake();

    $this->actingAs($user)->get($verificationUrl)
        ->assertRedirect(Filament::getUrl());

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    Notification::assertSentTo($user, AlumniWelcome::class);
});
