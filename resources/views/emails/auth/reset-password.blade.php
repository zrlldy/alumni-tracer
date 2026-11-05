@include('emails.password-reset', [
    'greeting' => 'Dear '.($user->name ?: 'Alumni').',',
    'actionUrl' => $resetUrl,
    'actionText' => 'Reset Password',
])
