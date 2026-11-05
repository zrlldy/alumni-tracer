<?php

use App\Models\User;
use Illuminate\Support\Facades\Mail;

it('renders each named email template', function (string $template, array $data, string $expectedContent) {
    $html = app('mailer')->render("emails.{$template}", $data);

    expect($html)
        ->toContain($expectedContent)
        ->toContain('#FCBF49')
        ->toContain('viewport')
        ->toContain('data:image/png;base64,')
        ->not->toContain('<svg')
        ->not->toContain('alumni-tracer.test');

    expect(substr_count($html, 'data:image/png;base64,'))->toBe(2);
})->with([
    'one-time passcode' => ['otp', ['otpCode' => '617204'], '617204'],
    'email verification' => ['verify-email', ['actionUrl' => 'https://example.test/verify', 'actionText' => 'Verify Email'], 'Verify Email'],
    'password reset' => ['password-reset', ['actionUrl' => 'https://example.test/reset', 'actionText' => 'Reset Password'], 'Reset Password'],
    'welcome' => ['welcome', [], 'Welcome to Alumni Tracer'],
]);

it('escapes user supplied email copy', function () {
    $html = app('mailer')->render('emails.notification', ['heading' => '<script>alert("x")</script>']);

    expect($html)
        ->toContain('&lt;script&gt;')
        ->not->toContain('<script>alert');
});

it('describes the email code as a sign-in step', function () {
    $html = app('mailer')->render('emails.otp', ['otpCode' => '617204', 'expiry' => '2 minutes']);

    expect($html)
        ->toContain('finish signing in')
        ->toContain('Do not share this code')
        ->not->toContain('confirm your email address');
});

it('renders every legacy auth email with the shared graduate design', function () {
    $user = new User(['name' => 'Alex']);

    foreach ([
        'auth.otp' => [['user' => $user, 'otp' => '617204', 'expiresInMinutes' => 2], '617204'],
        'auth.verify-email' => [['user' => $user, 'verificationUrl' => 'https://example.test/verify'], 'https://example.test/verify'],
        'auth.reset-password' => [['user' => $user, 'resetUrl' => 'https://example.test/reset'], 'https://example.test/reset'],
        'auth.welcome' => [['user' => $user, 'profileUrl' => 'https://example.test/online'], 'https://example.test/online'],
    ] as $template => [$data, $expectedContent]) {
        $html = app('mailer')->render("emails.{$template}", $data);

        expect($html)
            ->toContain('GRADUATE RECORDS')
            ->toContain($expectedContent)
            ->toContain('data:image/png;base64,');
    }
});

it('sends the logo and mascot as inline PNG attachments', function () {
    $sent = Mail::mailer('array')->send('emails.otp', ['otpCode' => '617204'], function ($message) {
        $message->to('preview@example.test')->subject('Email template test');
    });
    $mime = $sent->getOriginalMessage()->toString();

    expect($mime)
        ->toContain('multipart/related')
        ->toContain('image/png')
        ->toContain('Content-ID:');

    expect(substr_count($mime, 'Content-ID:'))->toBe(2);
});
