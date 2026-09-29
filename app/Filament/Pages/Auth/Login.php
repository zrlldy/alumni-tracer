<?php

namespace App\Filament\Pages\Auth;

use DiogoGPinto\AuthUIEnhancer\Pages\Auth\Concerns\HasCustomLayout;
use Filament\Actions\Action;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Illuminate\Htmlable;
use Illuminate\Validation\ValidationException;
use Sujip\Filament\Turnstile\Contracts\TurnstileClientContract;
use Sujip\Filament\Turnstile\Exceptions\TurnstileException;

class Login extends BaseLogin
{
    use HasCustomLayout;

    /**
     * Turnstile token returned by Cloudflare.
     */
    public string $turnstileToken = '';

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
     */
    public function getSubheading(): ?string
    {
        return 'Sign in to continue to Alumni Tracer.';
    }

    /**
     * Add Turnstile to the login form.
     */
    public function form(Schema $schema): Schema
    {
        $client = app(TurnstileClientContract::class);

        $components = [
            $this->getEmailFormComponent(),
            $this->getPasswordFormComponent(),
            $this->getRememberFormComponent(),
        ];

        if ($client->isConfigured()) {
            $components[] = View::make('filament-turnstile::turnstile-raw')
                ->viewData([
                    'siteKey' => $client->siteKey(),
                ]);
        }

        return $schema->components($components);
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
     * Verify Turnstile before normal Filament authentication.
     */
    public function authenticate(): ?LoginResponse
    {
        $client = app(TurnstileClientContract::class);

        if ($client->isConfigured()) {
            $this->verifyTurnstileToken($client);
        }

        return parent::authenticate();
    }

    /**
     * Verify Cloudflare Turnstile token.
     */
    private function verifyTurnstileToken(TurnstileClientContract $client): void
    {
        if ($this->turnstileToken === '') {
            $this->resetTurnstile();

            throw ValidationException::withMessages([
                'turnstileToken' => 'Please complete the security challenge.',
            ]);
        }

        try {
            $result = $client->verify($this->turnstileToken);
        } catch (TurnstileException) {
            $this->resetTurnstile();

            throw ValidationException::withMessages([
                'turnstileToken' => 'The security challenge could not be verified. Please try again.',
            ]);
        }

        if (! $result->isSuccessful()) {
            $this->resetTurnstile();

            throw ValidationException::withMessages([
                'turnstileToken' => 'The security challenge failed. Please try again.',
            ]);
        }
    }

    /**
     * Reset the Turnstile widget after failure.
     */
    private function resetTurnstile(): void
    {
        $this->turnstileToken = '';

        $this->dispatch('turnstile.reset');
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