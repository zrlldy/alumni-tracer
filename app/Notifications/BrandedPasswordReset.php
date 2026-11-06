<?php

namespace App\Notifications;

use Filament\Auth\Notifications\ResetPassword as FilamentResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class BrandedPasswordReset extends FilamentResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        return BrandedEmailMessage::make('emails.password-reset', [
            'greeting' => 'Dear '.($notifiable->name ?: 'Alumni').',',
            'actionUrl' => $this->url,
            'actionText' => 'Reset Password',
            'expiry' => trans_choice(
                ':count minute|:count minutes',
                (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire'),
            ),
        ], 'Reset your password');
    }
}
