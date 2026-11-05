<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AlumniWelcome extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @return array<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return BrandedEmailMessage::make('emails.welcome', [
            'greeting' => 'Hi '.($notifiable->name ?: 'there').',',
            'actionUrl' => route('filament.online.pages.dashboard'),
            'actionText' => 'Visit Alumni Tracer',
        ], 'Welcome to Alumni Tracer');
    }
}
