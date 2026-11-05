@include('emails.verify-email', [
    'greeting' => 'Dear '.($user->name ?: 'Alumni').',',
    'actionUrl' => $verificationUrl,
    'actionText' => 'Verify Email',
])
