<?php

namespace App\Filament\Pages\Auth;

use DiogoGPinto\AuthUIEnhancer\Pages\Auth\Concerns\HasCustomLayout;
use Filament\Actions\Action;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

class Login extends BaseLogin
{
    use HasCustomLayout;

    public function getTitle(): string
    {
        return 'Alumni Tracer | Sign In';
    }

    public function getHeading(): string
    {
        return 'Welcome back';
    }

    /**
     * Remove Filament's default:
     * "or sign up for an account"
     *
     * We will render the registration link at the bottom instead.
     */
    public function getSubheading(): ?string
    {
        return 'Sign in to continue to Alumni Tracer.';
    }

    /**
     * Email field
     */
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Email address')
            ->placeholder('you@example.com')
            ->email()
            ->required()
            ->autocomplete('username')
            ->autofocus()
            ->extraInputAttributes([
                'class' => 'alumni-auth-input',
            ]);
    }

    /**
     * Password field
     *
     * We intentionally remove the default "Forgot password?"
     * hint because it will be moved to the bottom.
     */
    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Password')
            ->placeholder('Enter your password')
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->autocomplete('current-password')
            ->required()
            ->extraInputAttributes([
                'class' => 'alumni-auth-input',
            ]);
    }

    /**
     * Remember me
     */
    protected function getRememberFormComponent(): Component
    {
        return Checkbox::make('remember')
            ->label('Remember me')
            ->extraAttributes([
                'class' => 'alumni-auth-remember',
            ]);
    }

    /**
     * Sign in button
     */
    protected function getAuthenticateFormAction(): Action
    {
        return parent::getAuthenticateFormAction()
            ->label('Sign in')
            ->extraAttributes([
                'class' => 'alumni-auth-submit',
            ]);
    }

    /**
     * Keep the normal login form action,
     * then add our custom bottom links.
     */
    public function getFormContentComponent(): Component
    {
        return Form::make([
            EmbeddedSchema::make('form'),
        ])
            ->id('form')
            ->livewireSubmitHandler('authenticate')
            ->footer([
                Actions::make($this->getFormActions())
                    ->alignment($this->getFormActionsAlignment())
                    ->fullWidth($this->hasFullWidthFormActions())
                    ->key('form-actions'),

                View::make('filament.auth.login-footer')
                    ->visible(fn (): bool => blank(
                        $this->userUndertakingMultiFactorAuthentication
                    )),
            ])
            ->visible(fn (): bool => blank(
                $this->userUndertakingMultiFactorAuthentication
            ));
    }
}