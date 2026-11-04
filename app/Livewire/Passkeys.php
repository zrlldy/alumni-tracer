<?php
// app/Livewire/Passkeys.php

namespace App\Livewire;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Spatie\LaravelPasskeys\Livewire\PasskeysComponent;

class Passkeys extends PasskeysComponent implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public function deletePasskeyAction(): Action
    {
        return Action::make('deletePasskey')
            ->label('Remove')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Remove this passkey?')
            ->modalDescription("You'll need another sign-in method next time on this device.")
            ->modalSubmitActionLabel('Remove')
            ->action(function (array $arguments) {
                $this->currentUser()
                    ->passkeys()
                    ->where('id', $arguments['passkeyId'] ?? null)
                    ->delete();

                Notification::make()
                    ->title('Passkey removed')
                    ->success()
                    ->send();
            });
    }
}