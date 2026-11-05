@include('emails.notification', [
    'title' => $title ?? 'Reset your password',
    'greeting' => $greeting ?? 'Dear Alumni,',
    'heading' => $heading ?? 'Forgot your password?',
    'bodyText' => $bodyText ?? 'You requested a password reset for your account. Use the button below to choose a new password.',
    'actionUrl' => $actionUrl ?? null,
    'actionText' => $actionText ?? 'Reset password',
    'note' => $note ?? (isset($expiry) ? "This link expires in {$expiry}." : null),
    'footer' => $footer ?? null,
])
