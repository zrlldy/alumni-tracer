<?php

namespace App\Filament\Pages;

use DiogoGPinto\AuthUIEnhancer\Pages\Auth\Concerns\HasCustomLayout;
use Filament\Actions\Action;
use Filament\Auth\Http\Responses\Contracts\RegistrationResponse;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Sujip\Filament\Turnstile\Contracts\TurnstileClientContract;
use Sujip\Filament\Turnstile\TurnstileInput;

class Register extends BaseRegister
{
    use HasCustomLayout;

    public function form(Schema $schema): Schema
    {
        $client = app(TurnstileClientContract::class);

        $components = [
            $this->getNameFormComponent(),
            $this->getEmailFormComponent(),
            $this->getPasswordFormComponent(),
            $this->getPasswordConfirmationFormComponent(),
        ];

        if ($client->isConfigured()) {
            $components[] = TurnstileInput::make('turnstileToken');
        }

        return $schema->components($components);
    }

    public function getSubheading(): string | \Illuminate\Contracts\Support\Htmlable | null
    {
        return null;
    }

    /**
     * Register button
     */
    public function getRegisterFormAction(): Action
    {
        return parent::getRegisterFormAction()
            ->label('Create account')
            ->extraAttributes([
                'class' => 'alumni-auth-submit',
            ]);
    }

    /**
     * Keep the normal Filament register form,
     * then add our custom footer below the Register button.
     */
    public function getFormContentComponent(): Component
    {
        return Form::make([
            EmbeddedSchema::make('form'),
        ])
            ->id('form')
            ->livewireSubmitHandler('register')
            ->footer([
                Actions::make($this->getFormActions())
                    ->alignment($this->getFormActionsAlignment())
                    ->fullWidth($this->hasFullWidthFormActions())
                    ->key('form-actions'),

                View::make('filament.auth.register-footer'),
            ]);
    }

    public function register(): ?RegistrationResponse
    {
        return parent::register();
    }
}