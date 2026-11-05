@include('emails.notification', [
    'title' => $title ?? 'Welcome to Alumni Tracer',
    'eyebrow' => $eyebrow ?? 'Alumni account',
    'greeting' => $greeting ?? 'Hi there,',
    'heading' => $heading ?? 'Welcome to Alumni Tracer',
    'bodyText' => $bodyText ?? 'Your email is confirmed. Sign in to see the account features available to you.',
    'actionUrl' => $actionUrl ?? null,
    'actionText' => $actionText ?? 'Visit Alumni Tracer',
    'note' => $note ?? null,
    'footer' => $footer ?? null,
])
