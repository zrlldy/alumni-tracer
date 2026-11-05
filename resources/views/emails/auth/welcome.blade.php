@include('emails.welcome', [
    'greeting' => 'Hi '.($user->name ?: 'there').',',
    'actionUrl' => $profileUrl,
    'actionText' => 'Visit Alumni Tracer',
])
