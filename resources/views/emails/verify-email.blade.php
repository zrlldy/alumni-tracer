@include('emails.notification', [
    'title' => $title ?? 'Verify your email',
    'eyebrow' => $eyebrow ?? 'Alumni account',
    'greeting' => $greeting ?? 'Dear Alumni,',
    'heading' => $heading ?? 'Verify your email address',
    'bodyText' => $bodyText ?? 'Confirm this email address to finish setting up your Alumni Tracer account.',
    'actionUrl' => $actionUrl ?? null,
    'actionText' => $actionText ?? 'Verify email',
    'showActionLink' => true,
    'note' => $note ?? 'If you did not create this account, you can ignore this message.',
    'footer' => $footer ?? null,
])
