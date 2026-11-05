@include('emails.notification', [
    'title' => $title ?? 'Reset your password',
    'eyebrow' => $eyebrow ?? 'Account security',
    'greeting' => $greeting ?? 'Dear Alumni,',
    'heading' => $heading ?? 'Reset your password',
    'bodyText' => $bodyText ?? 'Use the button below to choose a new password for your Alumni Tracer account.',
    'actionUrl' => $actionUrl ?? null,
    'actionText' => $actionText ?? 'Reset password',
    'note' => $note ?? (isset($expiry) ? "This link expires in {$expiry}. If you did not request a reset, you can ignore this message." : 'If you did not request a reset, you can ignore this message.'),
    'footer' => $footer ?? null,
])
