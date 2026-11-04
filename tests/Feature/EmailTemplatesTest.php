<?php

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
