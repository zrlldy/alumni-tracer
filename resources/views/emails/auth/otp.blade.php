@include('emails.otp', [
    'greeting' => 'Dear '.($user->name ?: 'Alumni').',',
    'otpCode' => $otp,
    'expiry' => trans_choice(':count minute|:count minutes', (int) $expiresInMinutes),
])
