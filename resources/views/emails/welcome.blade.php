@include('emails.notification', [
    'title' => $title ?? 'Welcome to Alumni Tracer',
    'greeting' => $greeting ?? 'Hi there,',
    'heading' => $heading ?? 'Welcome to Alumni Tracer',
    'bodyText' => $bodyText ?? 'We are glad you are part of our alumni community. Update your profile and stay connected with fellow alumni.',
    'actionUrl' => $actionUrl ?? null,
    'actionText' => $actionText ?? 'Complete your profile',
    'note' => $note ?? null,
    'footer' => $footer ?? null,
])
