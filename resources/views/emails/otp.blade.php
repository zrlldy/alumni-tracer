@include('emails.notification', [
    'title' => $title ?? 'Your sign-in code',
    'eyebrow' => $eyebrow ?? 'Account security',
    'greeting' => $greeting ?? 'Dear Alumni,',
    'intro' => $intro ?? 'Use this code to finish signing in to Alumni Tracer.',
    'heading' => $heading ?? 'Confirm your sign-in',
    'otpCode' => $otpCode ?? null,
    'note' => $note ?? (isset($expiry) ? "This code expires in {$expiry}. Do not share this code with anyone." : 'Do not share this code with anyone.'),
    'footer' => $footer ?? null,
])
