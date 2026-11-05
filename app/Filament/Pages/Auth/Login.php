<?php

namespace App\Filament\Pages\Auth;

use App\Models\TrustedDevice;
use App\Models\User;
use App\Support\ClientHints;
use App\Support\DeviceFingerprint;
use App\Support\RememberedAccountsCookie;
use App\Support\TrustedDeviceCookie;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DiogoGPinto\AuthUIEnhancer\Pages\Auth\Concerns\HasCustomLayout;
use Filament\Actions\Action;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\MultiFactor\Contracts\HasBeforeChallengeHook;
use Filament\Auth\MultiFactor\Contracts\MultiFactorAuthenticationProvider;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\RenderHook;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\View as SchemaView;
use Filament\Schemas\Schema;
use Filament\View\PanelsRenderHook;
use Illuminate\Auth\Events\Attempting;
use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use SensitiveParameter;
use Sujip\Filament\Turnstile\Contracts\TurnstileClientContract;
use Sujip\Filament\Turnstile\Exceptions\TurnstileException;
use Throwable;

class Login extends \Filament\Auth\Pages\Login
{
    use HasCustomLayout;

    protected const TRUSTED_DEVICE_COOKIE = 'trusted_device';

    protected const TRUSTED_DEVICE_DAYS = 30;

    /**
     * Turnstile token returned by Cloudflare.
     */
    public string $turnstileToken = '';

    public bool $pickingAccount = false;

    public array $rememberedAccounts = [];

    public function mount(): void
    {
        parent::mount();

        $this->rememberedAccounts = RememberedAccountsCookie::get();
        $this->pickingAccount = ! empty($this->rememberedAccounts);
    }

    public function getTitle(): string
    {
        return 'Alumni Tracer | Sign In';
    }

    public function getHeading(): string
    {
        if (filled($this->userUndertakingMultiFactorAuthentication)) {
            return 'Verify your identity';
        }

        return 'Welcome back';
    }

    public function getSubheading(): string|Htmlable|null
    {
        if (filled($this->userUndertakingMultiFactorAuthentication)) {
            return null;
        }

        if ($this->pickingAccount) {
            return "Who's signing in?";
        }

        return 'Sign in to continue to Alumni Tracer.';
    }

    /**
     * Add Turnstile to the normal login form.
     * Filament's MFA challenge form remains managed by the v5.4.1 parent page.
     */
    public function form(Schema $schema): Schema
    {
        $client = app(TurnstileClientContract::class);

        $components = [
            $this->getEmailFormComponent(),
            $this->getPasswordFormComponent(),
            $this->combinedRememberForgot(),
        ];

        if ($client->isConfigured()) {
            $components[] = SchemaView::make('filament-turnstile::turnstile-raw')
                ->viewData([
                    'siteKey' => $client->siteKey(),
                ]);
        }

        return $schema->components($components);
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label(__('filament-panels::auth/pages/login.form.email.label'))
            ->hiddenLabel()
            ->placeholder('Email address')
            ->email()
            ->required()
            ->autocomplete()
            ->autofocus()
            ->prefixIcon('heroicon-o-envelope');
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label(__('filament-panels::auth/pages/login.form.password.label'))
            ->hiddenLabel()
            ->placeholder('Password')
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->autocomplete('current-password')
            ->required()
            ->prefixIcon('heroicon-o-lock-closed');
    }

    protected function combinedRememberForgot(): Component
    {
        if (! filament()->hasPasswordReset()) {
            return $this->getRememberFormComponent();
        }

        return Grid::make([
            'default' => 2,
        ])
            ->schema([
                $this->getRememberFormComponent()
                    ->extraAttributes([
                        'class' => 'ee-auth-remember',
                    ]),

                Text::make(
                    new HtmlString(
                        Blade::render(
                            '<x-filament::link :href="filament()->getRequestPasswordResetUrl()">{{ __(\'filament-panels::auth/pages/login.actions.request_password_reset.label\') }}</x-filament::link>'
                        )
                    )
                )
                    ->extraAttributes([
                        'class' => 'ee-auth-forgot-password',
                    ]),
            ])
            ->extraAttributes([
                'class' => 'ee-auth-remember-forgot',
            ]);
    }

    /**
     * Authenticate using Filament v5.4.1's multi-factor provider API.
     */
    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $isMfaStep = filled($this->userUndertakingMultiFactorAuthentication);

        $client = app(TurnstileClientContract::class);

        // Turnstile is required for the password step, not for the MFA code step.
        if ($client->isConfigured() && ! $isMfaStep) {
            $this->verifyTurnstileToken($client);
        }

        $data = $this->form->getState();

        /** @var SessionGuard $authGuard */
        $authGuard = Filament::auth();

        $authProvider = $authGuard->getProvider();
        $credentials = $this->getCredentialsFromFormData($data);
        $remember = (bool) ($data['remember'] ?? false);

        $this->fireAttemptingEvent($authGuard, $credentials, $remember);

        $user = $authProvider->retrieveByCredentials($credentials);

        if (
            ! ($user instanceof User) ||
            ! $authProvider->validateCredentials($user, $credentials)
        ) {
            $this->userUndertakingMultiFactorAuthentication = null;

            $this->fireFailedEvent($authGuard, $user, $credentials);
            $this->throwFailureValidationException();
        }

        // Check panel access before exposing an MFA challenge.
        if (! $this->isUserAllowedToAccessPanel($user)) {
            $this->userUndertakingMultiFactorAuthentication = null;

            $this->fireFailedEvent($authGuard, $user, $credentials);
            $this->throwFailureValidationException();
        }

        $mfaPassed = false;

        if ($isMfaStep) {
            // Filament v5.4.1 stores only the encrypted auth identifier here.
            $challengeUserIdentifier = $this->getMfaChallengeUserIdentifier();

            if (
                $challengeUserIdentifier === null ||
                ! hash_equals(
                    (string) $challengeUserIdentifier,
                    (string) $user->getAuthIdentifier(),
                )
            ) {
                $this->rejectMultiFactorChallenge(
                    'Your sign-in challenge has expired. Please sign in again.'
                );
            }

            // Do not accept an old challenge if MFA was disabled during the flow.
            if ($this->getEnabledMultiFactorProviders($user) === []) {
                $this->rejectMultiFactorChallenge(
                    'No verification method is available. Please contact the administrator.'
                );
            }

            if ($this->isMultiFactorChallengeRateLimited($user)) {
                return null;
            }

            // This is Filament v5.4.1's built-in challenge schema and validation.
            $this->multiFactorChallengeForm->validate();

            $mfaPassed = true;
        } elseif (! $this->isDeviceTrusted($user)) {
            $enabledProviders = $this->getEnabledMultiFactorProviders($user);
            $provider = $enabledProviders === [] ? null : reset($enabledProviders);

            // MFA is required only when the user has an enabled MFA provider.
            // If the user has not configured 2FA, continue with normal password login.
            if ($provider instanceof MultiFactorAuthenticationProvider) {
                // v5.4.1 expects an encrypted identifier, not the newer identifier/userKey array.
                $this->userUndertakingMultiFactorAuthentication = encrypt(
                    $user->getAuthIdentifier()
                );

                if ($provider instanceof HasBeforeChallengeHook) {
                    $provider->beforeChallenge($user);
                }

                $this->multiFactorChallengeForm->fill();

                return null;
            }
        }

        $userIdentifier = (string) $user->getAuthIdentifier();

        // Recheck the password, identity, and panel access at the moment of login.
        if (! $authGuard->attemptWhen(
            $credentials,
            fn (Authenticatable $candidate): bool => hash_equals(
                $userIdentifier,
                (string) $candidate->getAuthIdentifier(),
            ) && $this->isUserAllowedToAccessPanel($candidate),
            $remember,
        )) {
            $this->userUndertakingMultiFactorAuthentication = null;

            $this->throwFailureValidationException();
        }

        session()->regenerate();

        // Trust the device only after successful MFA and authentication.
        if ($mfaPassed) {
            $this->trustDevice($authGuard->user());
        }

        $this->rememberClientHints();

        if ($remember) {
            $map = RememberedAccountsCookie::remember($authGuard->user());
            RememberedAccountsCookie::queue($map);
        }

        $this->userUndertakingMultiFactorAuthentication = null;

        return app(LoginResponse::class);
    }

    /**
     * @return array<MultiFactorAuthenticationProvider>
     */
    protected function getEnabledMultiFactorProviders(
        Authenticatable $user
    ): array {
        return array_filter(
            Filament::getMultiFactorAuthenticationProviders(),
            fn (MultiFactorAuthenticationProvider $provider): bool => $provider->isEnabled($user),
        );
    }

    /**
     * Safely resolve the encrypted identifier used by Filament v5.4.1.
     */
    private function getMfaChallengeUserIdentifier(): mixed
    {
        if (blank($this->userUndertakingMultiFactorAuthentication)) {
            return null;
        }

        try {
            return decrypt($this->userUndertakingMultiFactorAuthentication);
        } catch (Throwable) {
            return null;
        }
    }

    protected function isUserAllowedToAccessPanel(Authenticatable $user): bool
    {
        if (! ($user instanceof FilamentUser)) {
            return true;
        }

        return $user->canAccessPanel(Filament::getCurrentOrDefaultPanel());
    }

    protected function fireAttemptingEvent(
        Guard $guard,
        #[SensitiveParameter] array $credentials,
        bool $remember,
    ): void {
        event(app(Attempting::class, [
            'guard' => property_exists($guard, 'name') ? $guard->name : '',
            'credentials' => $credentials,
            'remember' => $remember,
        ]));
    }

    protected function rejectMultiFactorChallenge(string $message): never
    {
        $this->userUndertakingMultiFactorAuthentication = null;

        $this->resetTurnstile();

        throw ValidationException::withMessages([
            'data.email' => $message,
        ]);
    }

    private function verifyTurnstileToken(
        TurnstileClientContract $client
    ): void {
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

    private function resetTurnstile(): void
    {
        $this->turnstileToken = '';

        $this->dispatch('turnstile.reset');
    }

    protected function throwFailureValidationException(): never
    {
        $this->resetTurnstile();

        parent::throwFailureValidationException();
    }

    protected function rememberClientHints(): void
    {
        ClientHints::rememberInSession(request());

        $hints = ClientHints::capture(request());

        session([
            'ch_model' => $hints['model'],
            'ch_platform' => $hints['platform'],
            'ch_platform_version' => $hints['platformVersion'],
        ]);
    }

    protected function trustDevice(Authenticatable $user): void
    {
        $token = Str::random(64);
        $hints = ClientHints::capture(request());

        $device = TrustedDevice::query()->create([
            'user_id' => $user->getAuthIdentifier(),
            'token' => hash('sha256', $token),
            'fingerprint' => DeviceFingerprint::compute(request()),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'device_model' => $hints['model'],
            'device_platform' => $hints['platform'],
            'device_platform_version' => $hints['platformVersion'],
            'expires_at' => now()->addDays(static::TRUSTED_DEVICE_DAYS),
        ]);

        session([
            'current_trusted_device_id' => $device->id,
        ]);

        $map = $this->pruneTrustedDeviceMap($this->getTrustedDeviceMap());
        $map[(string) $user->getAuthIdentifier()] = $token;

        Cookie::queue(
            Cookie::make(
                static::TRUSTED_DEVICE_COOKIE,
                json_encode($map, JSON_THROW_ON_ERROR),
                60 * 24 * static::TRUSTED_DEVICE_DAYS,
                path: '/',
                secure: true,
                httpOnly: true,
                sameSite: 'lax',
            )
        );
    }

    protected function pruneTrustedDeviceMap(array $map): array
    {
        return TrustedDeviceCookie::prune($map);
    }

    protected function getTrustedDeviceMap(): array
    {
        $raw = request()->cookie(static::TRUSTED_DEVICE_COOKIE);

        if (! is_string($raw) || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    protected function isDeviceTrusted(Authenticatable $user): bool
    {
        $token = $this->getTrustedDeviceMap()[
            (string) $user->getAuthIdentifier()
        ] ?? null;

        if (! is_string($token) || $token === '') {
            return false;
        }

        $device = TrustedDevice::query()
            ->where('user_id', $user->getAuthIdentifier())
            ->where('token', hash('sha256', $token))
            ->where('expires_at', '>', now())
            ->first();

        if (! $device) {
            return false;
        }

        // Do not trust legacy records that have no fingerprint.
        if (! is_string($device->fingerprint) || $device->fingerprint === '') {
            return false;
        }

        if (! hash_equals(
            $device->fingerprint,
            DeviceFingerprint::compute(request()),
        )) {
            return false;
        }

        session([
            'current_trusted_device_id' => $device->id,
        ]);

        return true;
    }

    public function selectAccount(string $email): void
    {
        // Do not let account selection interrupt an active MFA challenge.
        if (filled($this->userUndertakingMultiFactorAuthentication)) {
            return;
        }

        $user = User::firstWhere('email', $email);

        if (! $user) {
            $this->forgetAccount($email);

            return;
        }

        if (! $this->isUserAllowedToAccessPanel($user)) {
            $this->throwFailureValidationException();
        }

        if ($this->isDeviceTrusted($user)) {
            // A valid trusted-device token is the credential for this one-click flow.
            Filament::auth()->login($user, true);

            session()->regenerate();

            $this->rememberClientHints();

            $map = RememberedAccountsCookie::remember($user);
            RememberedAccountsCookie::queue($map);

            $this->redirect(Filament::getUrl());

            return;
        }

        // Untrusted device: require email/password, then MFA.
        $this->data['email'] = $email;
        $this->data['password'] = '';

        $this->pickingAccount = false;
    }

    public function forgetAccount(string $email): void
    {
        $map = RememberedAccountsCookie::get();

        $userId = collect($map)->search(
            fn ($entry): bool => ($entry['email'] ?? null) === $email,
        );

        if ($userId !== false) {
            $map = RememberedAccountsCookie::forget($map, $userId);
            RememberedAccountsCookie::queue($map);
        }

        $this->rememberedAccounts = $map;
        $this->pickingAccount = ! empty($map);
    }

    public function forgetAccountAction(): Action
    {
        return Action::make('forgetAccount')
            ->label('Remove')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Remove this account?')
            ->modalDescription(
                'This account will no longer appear in the list on this device. You can always sign back in with your email and password.'
            )
            ->modalSubmitActionLabel('Remove')
            ->action(function (array $arguments): void {
                $this->forgetAccount($arguments['email'] ?? '');
            });
    }

    public function showDifferentAccountForm(): void
    {
        if (filled($this->userUndertakingMultiFactorAuthentication)) {
            return;
        }

        $this->data['email'] = '';
        $this->data['password'] = '';

        $this->pickingAccount = false;
    }

    #[On('passkeyActionFailed')]
    public function handlePasskeyActionFailed(string $message): void
    {
        Notification::make()
            ->title('Passkey sign-in failed')
            ->body($message)
            ->warning()
            ->send();
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                RenderHook::make(PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE),

                $this->getAccountPickerContentComponent(),

                $this->getFormContentComponent(),

                // This component is supplied by Filament v5.4.1.
                $this->getMultiFactorChallengeFormContentComponent(),

                RenderHook::make(PanelsRenderHook::AUTH_LOGIN_FORM_AFTER),
            ]);
    }

    public function getAccountPickerContentComponent(): Component
    {
        return SchemaView::make('filament.pages.partials.account-picker')
            ->viewData([
                'accounts' => $this->rememberedAccounts,
            ])
            ->visible(
                fn (): bool => $this->pickingAccount
                    && blank($this->userUndertakingMultiFactorAuthentication)
            );
    }

    public function getFormContentComponent(): Component
    {
        return parent::getFormContentComponent()
            ->visible(
                fn (): bool => ! $this->pickingAccount
                    && blank($this->userUndertakingMultiFactorAuthentication)
            );
    }
}
