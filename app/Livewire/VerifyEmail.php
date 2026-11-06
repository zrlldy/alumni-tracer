<?php

namespace App\Livewire;

use Filament\Actions\Action;
use Filament\Auth\Pages\EmailVerification\EmailVerificationPrompt;
use Filament\Support\Icons\Heroicon;

class VerifyEmail extends EmailVerificationPrompt
{
    protected static string $layout = 'filament.pages.email-verification-layout';

    protected string $view = 'livewire.verify-email';

    public function resendNotificationAction(): Action
    {
        return parent::resendNotificationAction()
            ->button()
            ->label('Resend verification email')
            ->icon(Heroicon::OutlinedPaperAirplane)
            ->size('lg')
            ->extraAttributes(['class' => 'verification-resend']);
    }
}
