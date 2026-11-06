<?php

namespace App\Notifications;

use Filament\Auth\Notifications\VerifyEmail as FilamentVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class BrandedEmailVerification extends FilamentVerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        return BrandedEmailMessage::make('emails.verify-email', [
            'greeting' => 'Dear '.($notifiable->name ?: 'Alumni').',',
            'actionUrl' => $this->url,
            'actionText' => 'Verify Email',
        ], 'Verify your email');
    }
}
