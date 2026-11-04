<?php

use App\Models\User;
use App\Notifications\AlumniEmailOtp;
use App\Notifications\AlumniWelcome;
use Filament\Auth\MultiFactor\Email\EmailAuthentication;
use Filament\Auth\Notifications\ResetPassword;
use Filament\Auth\Notifications\VerifyEmail;
use Filament\Facades\Filament;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Notification;

it('renders Filament password reset and verification URLs in the branded templates', function () {
    $user = new User(['name' => 'Jerold', 'email' => 'jerold@example.test']);

    $reset = app(ResetPassword::class, ['token' => 'reset-token']);
    $reset->url = 'https://alumni.example.test/reset/reset-token?email=jerold%40example.test';
    $verification = app(VerifyEmail::class);
    $verification->url = 'https://alumni.example.test/verify/42/signed-hash';

    $resetMail = $reset->toMail($user);
    $verificationMail = $verification->toMail($user);

    expect($resetMail)->toBeInstanceOf(MailMessage::class)
        ->and($resetMail->view)->toBe('emails.password-reset')
        ->and($resetMail->viewData['actionUrl'])->toBe($reset->url)
        ->and($verificationMail)->toBeInstanceOf(MailMessage::class)
        ->and($verificationMail->view)->toBe('emails.verify-email')
        ->and($verificationMail->viewData['actionUrl'])->toBe($verification->url);
});

it('sends the Filament email code and expiry through the branded OTP template', function () {
    $user = new User(['name' => 'Jerold', 'email' => 'jerold@example.test']);
    $provider = Filament::getDefaultPanel()->getMultiFactorAuthenticationProviders()['email_code'];
    expect($provider)->toBeInstanceOf(EmailAuthentication::class)
        ->and($provider->getCodeNotification())->toBe(AlumniEmailOtp::class)
        ->and($provider->getCodeExpiryMinutes())->toBe(2);
    $provider->generateCodesUsing(fn (): string => '617204');

    Notification::fake();

    expect($provider->sendCode($user))->toBeTrue();

    Notification::assertSentTo($user, AlumniEmailOtp::class, function (AlumniEmailOtp $notification): bool {
        $mail = $notification->toMail(new User(['email' => 'jerold@example.test']));

        return $notification->code === '617204'
            && $notification->codeExpiryMinutes === 2
            && $mail->view === 'emails.otp'
            && $mail->viewData['otpCode'] === '617204'
            && $mail->viewData['expiry'] === '2 minutes';
    });
});

it('embeds both branded images as inline PNGs when a notification is sent', function () {
    $user = new User(['name' => 'Jerold', 'email' => 'jerold@example.test']);
    $user->notifyNow(new AlumniEmailOtp('617204', 2));

    $sent = app('mail.manager')->mailer()->getSymfonyTransport()->messages()[0];
    $mime = $sent->getOriginalMessage()->toString();

    expect($mime)
        ->toContain('multipart/related')
        ->toContain('image/png')
        ->toContain('617204')
        ->toContain('alumni-tracer-email-logo@alumnitracer')
        ->toContain('alumni-tracer-email-mascot@alumnitracer');
});

it('sends the alumni welcome only after the user is verified', function () {
    Notification::fake();
    $user = new User(['name' => 'Jerold', 'email' => 'jerold@example.test']);

    event(new Registered($user));
    Notification::assertNotSentTo($user, AlumniWelcome::class);

    event(new Verified($user));
    Notification::assertSentTo($user, AlumniWelcome::class, function (AlumniWelcome $notification): bool {
        $mail = $notification->toMail(new User(['name' => 'Jerold', 'email' => 'jerold@example.test']));

        return $mail->view === 'emails.welcome'
            && $mail->viewData['actionUrl'] === route('filament.online.pages.dashboard');
    });
});
