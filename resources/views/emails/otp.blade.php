@include('emails.notification', [
    'title' => $title ?? 'Your verification code',
    'greeting' => $greeting ?? 'Dear Alumni,',
    'intro' => $intro ?? 'Please use the verification code below to confirm your email address.',
    'heading' => $heading ?? 'OTP code',
    'otpCode' => $otpCode ?? null,
    'note' => $note ?? (isset($expiry) ? "This code expires in {$expiry}." : null),
    'footer' => $footer ?? null,
])
