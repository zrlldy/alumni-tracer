<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Support\AllowedEmailDomains;
use App\Support\Terms;
use Closure;
use DiogoGPinto\AuthUIEnhancer\Pages\Auth\Concerns\HasCustomLayout;
use Filament\Actions\Action;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
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

        $components[] = Checkbox::make('terms')
            ->label(new HtmlString('I agree to the <a href="'.e(route('terms.show')).'" target="_blank" rel="noopener noreferrer" class="underline">Terms and Conditions</a>.'))
            ->accepted()
            ->validationMessages(['accepted' => 'Please agree to the Terms and Conditions to create an account.'])
            ->dehydrated(false);
        $components[] = Hidden::make('terms_version')
            ->default(Terms::VERSION)
            ->required()
            ->in([Terms::VERSION])
            ->dehydrated(false);

        return $schema->components($components);
    }

    /**
     * Prevent duplicate names, including soft-deleted accounts.
     *
     * Filament evaluates the outer zero-argument closure and passes the
     * returned Laravel validation closure to the validator. Passing the
     * three-argument validator closure directly can make Filament try to
     * inject $attribute while evaluating the component configuration.
     */
    protected function getNameFormComponent(): Component
    {
        return parent::getNameFormComponent()
            ->rules([
                fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                    $name = Str::lower(trim((string) $value));

                    if ($name === '') {
                        return;
                    }

                    $exists = User::withTrashed()
                        ->whereRaw('LOWER(TRIM(name)) = ?', [$name])
                        ->exists();

                    if ($exists) {
                        $fail('An account with this name already exists.');
                    }
                },
            ]);
    }

    /**
     * Validate the email domain and prevent duplicate email registrations.
     * Configured Super Admin emails are exempt from the domain restriction.
     */
    protected function getEmailFormComponent(): Component
    {
        return parent::getEmailFormComponent()
            ->rules([
                fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                    $email = Str::lower(trim((string) $value));

                    if ($email === '') {
                        return;
                    }

                    if (! AllowedEmailDomains::passes($email)) {
                        $fail(
                            'Registration is not allowed using this email address. Please use an authorized email domain.'
                        );
                    }
                },
                fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                    $email = Str::lower(trim((string) $value));

                    if ($email === '') {
                        return;
                    }

                    $exists = User::withTrashed()
                        ->whereRaw('LOWER(TRIM(email)) = ?', [$email])
                        ->exists();

                    if ($exists) {
                        $fail('An account with this email address already exists.');
                    }
                },
            ]);
    }

    /**
     * Normalize registration data before creating the account.
     */
    protected function mutateFormDataBeforeRegister(array $data): array
    {
        $data['name'] = trim((string) $data['name']);
        $data['email'] = Str::lower(trim((string) $data['email']));

        return $data;
    }

    protected function handleRegistration(array $data): Model
    {
        return DB::transaction(function () use ($data): Model {
            /** @var User $user */
            $user = parent::handleRegistration($data);
            $user->acceptCurrentTerms();

            return $user;
        });
    }

    public function getSubheading(): string|Htmlable|null
    {
        return null;
    }

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
}
