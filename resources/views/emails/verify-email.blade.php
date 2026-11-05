@include('emails.notification', [
    'title' => $title ?? 'Verify your email',
    'greeting' => $greeting ?? 'Dear Alumni,',
    'heading' => $heading ?? 'Verify your email',
    'bodyText' => $bodyText ?? 'Please verify your email address to complete your registration and secure your account.',
    'actionUrl' => $actionUrl ?? null,
    'actionText' => $actionText ?? 'Verify email',
    'note' => $note ?? null,
    'footer' => $footer ?? null,
])
