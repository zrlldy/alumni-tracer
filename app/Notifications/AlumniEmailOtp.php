<?php

namespace App\Notifications;

use Filament\Auth\MultiFactor\Email\Notifications\VerifyEmailAuthentication;
use Illuminate\Notifications\Messages\MailMessage;

class AlumniEmailOtp extends VerifyEmailAuthentication
{
    public function toMail(object $notifiable): MailMessage
    {
        return BrandedEmailMessage::make('emails.otp', [
            'greeting' => 'Dear '.($notifiable->name ?: 'Alumni').',',
            'otpCode' => $this->code,
            'expiry' => trans_choice(':count minute|:count minutes', $this->codeExpiryMinutes),
        ], 'Your Alumni Tracer verification code');
    }
}
