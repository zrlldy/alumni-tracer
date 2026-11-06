<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use App\Models\TrustedDevice;
use App\Support\SessionPayloadReader;
use App\Support\TrustedDeviceCookie;
use App\Traits\RoleBasedAccessControl;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Auth\Pages\EditProfile as PagesEditProfile;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Illuminate\Auth\SessionGuard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\On;

class EditProfile extends PagesEditProfile
{

    protected static null|string $slug = 'settings';

    protected static ?string $title = 'Settings';

    /*
    |--------------------------------------------------------------------------
    | Profile Initialization
    |--------------------------------------------------------------------------
    */

    protected function userHasPassword(): bool
    {
        return filled($this->getUser()->password_set_at);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $user = $this->getUser();

        $data['student_id'] = $user->student_id;
        $data['avatar_img'] = $user->avatar_img;

        return $data;
    }

    /*
    |--------------------------------------------------------------------------
    | Profile Form
    |--------------------------------------------------------------------------
    */

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Personal Information')
                    ->description('Update your account details.')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 5,
                        ])
                            ->schema([

                                // Profile Picture
                                $this->getAvatarFormComponent()
                                    ->columnSpan([
                                        'default' => 1,
                                        'md' => 1,
                                    ]),

                                // Personal Information
                                Section::make()
                                    ->columns(1)
                                    ->columnSpan([
                                        'default' => 1,
                                        'md' => 2,
                                    ])
                                    ->secondary()
                                    ->schema([

                                        TextInput::make('student_id')
                                            ->label('Student ID')
                                            // ->placeholder('Enter your Student ID')
                                            ->maxLength(255)
                                            ->autocomplete('off')
                                            ->copyable(copyMessage: 'Copied!', copyMessageDuration: 1500),
                                            // ->helperText(
                                            //     'You can update your Student ID here.'
                                            // ),

                                        $this->getNameFormComponent(),

                                        $this->getEmailFormComponent(),
                                    ]),

                                // Password Settings
                                Section::make()
                                    ->columns(1)
                                    ->columnSpan([
                                        'default' => 1,
                                        'md' => 2,
                                    ])
                                    ->secondary()
                                    ->schema([
                                        $this->getPasswordFormComponent(),

                                        $this->getPasswordConfirmationFormComponent(),

                                        $this->getCurrentPasswordFormComponent(),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Profile Picture Upload
    |--------------------------------------------------------------------------
    */

    protected function getAvatarFormComponent(): FileUpload
    {
        return FileUpload::make('avatar_img')
            ->label('Profile Picture')
            ->image()
            ->avatar()
            ->disk('public')
            ->directory('images')
            ->visibility('public')
            ->acceptedFileTypes([
                'image/jpeg',
                'image/png',
                'image/webp',
            ])
            ->maxSize(5120)
            ->imageEditor()
            ->preventFilePathTampering()
            ->helperText(
                'Upload a profile picture. JPEG, PNG or WebP. Maximum 5 MB.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Password Components
    |--------------------------------------------------------------------------
    */

    protected function getPasswordFormComponent(): TextInput
    {
        $hasPassword = $this->userHasPassword();

        return parent::getPasswordFormComponent()
            ->label(
                $hasPassword
                    ? 'New Password'
                    : 'Create Password'
            )
            ->helperText(
                $hasPassword
                    ? null
                    : "You signed in with Google and don't have a password yet. Set one here if you'd also like to be able to log in with your email and password."
            );
    }

    protected function getPasswordConfirmationFormComponent(): TextInput
    {
        return parent::getPasswordConfirmationFormComponent()
            ->label(
                $this->userHasPassword()
                    ? 'Confirm New Password'
                    : 'Confirm Password'
            );
    }

    protected function getCurrentPasswordFormComponent(): Component
    {
        $component = parent::getCurrentPasswordFormComponent();

        if (! $this->userHasPassword()) {
            return $component
                ->hidden()
                ->required(false)
                ->rule(null)
                ->dehydrated(false);
        }

        return $component;
    }

    /*
    |--------------------------------------------------------------------------
    | Save Profile
    |--------------------------------------------------------------------------
    */

    /**
     * Save profile picture and Student ID.
     *
     * Both fields are persisted directly to the users table.
     *
     * forceFill() allows these fields to be saved even when they
     * are not included in the User model's $fillable array.
     */
    protected function handleRecordUpdate(
        Model $record,
        array $data,
    ): Model {
        $oldAvatar = $record->getAttribute('avatar_img');

        // Extract profile fields for explicit persistence.
        $profileData = Arr::only($data, [
            'avatar_img',
            'student_id',
        ]);

        // Remove them from normal mass assignment.
        $data = Arr::except($data, [
            'avatar_img',
            'student_id',
        ]);

        if (array_key_exists('student_id', $profileData)) {
            $studentId = $profileData['student_id'];

            $profileData['student_id'] = filled($studentId)
                ? trim((string) $studentId)
                : null;
        }

        // Assign profile fields before the parent saves the record.
        if (filled($profileData) || count($profileData) > 0) {
            $record->forceFill($profileData);
        }

        /*
         * Preserve Filament's native profile update and
         * email-change verification functionality.
         *
         * The parent update saves the dirty profile attributes
         * together with the other submitted form data.
         */
        $updatedRecord = parent::handleRecordUpdate(
            $record,
            $data,
        );

        // Delete the replaced image only after the database commits.
        $newAvatar = $updatedRecord->getAttribute('avatar_img');

        if (
            filled($oldAvatar) &&
            $oldAvatar !== $newAvatar
        ) {
            DB::afterCommit(
                static function () use ($oldAvatar): void {
                    Storage::disk('public')->delete($oldAvatar);
                }
            );
        }

        return $updatedRecord;
    }

    /**
     * Prepare profile data before saving.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (filled($data['password'] ?? null)) {
            $data['password_set_at'] = now();
        }

        return $data;
    }

    /*
    |--------------------------------------------------------------------------
    | Settings Tabs
    |--------------------------------------------------------------------------
    */

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('ProfileTabs')
                    ->tabs([

                        Tab::make('Personal Information')
                            ->key('personal-information')
                            ->icon('heroicon-o-user')
                            ->schema([
                                $this->getFormContentComponent(),
                            ]),

                        Tab::make('Trusted Devices')
                            ->key('trusted-devices')
                            ->icon('heroicon-o-device-phone-mobile')
                            ->schema([
                                Section::make('Trusted Devices')
                                    ->description(
                                        'Devices that can skip the two-factor authentication step.'
                                    )
                                    ->compact()
                                    ->secondary()
                                    ->schema([
                                        View::make(
                                            'filament.pages.partials.trusted-devices'
                                        ),
                                    ]),
                            ]),

                        Tab::make('Active Sessions')
                            ->key('active-sessions')
                            ->icon('heroicon-o-computer-desktop')
                            ->schema([
                                Section::make('Active Sessions')
                                    ->description(
                                        'Devices currently signed in to your account.'
                                    )
                                    ->compact()
                                    ->secondary()
                                    ->schema([
                                        View::make(
                                            'filament.pages.partials.active-sessions'
                                        ),
                                    ]),
                            ]),

                        Tab::make('2FA')
                            ->key('two-factor')
                            ->icon('heroicon-o-shield-check')
                            ->schema(
                                Arr::wrap(
                                    $this->getMultiFactorAuthenticationContentComponent()
                                )
                            ),

                        Tab::make('Passwordless')
                            ->key('passwordless')
                            ->icon('heroicon-o-key')
                            ->schema([
                                Section::make('Passkey')
                                    ->description(
                                        'Sign in without a password using your fingerprint, face, or a security key.'
                                    )
                                    ->compact()
                                    ->secondary()
                                    ->schema([
                                        View::make(
                                            'filament.pages.partials.passkeys'
                                        ),
                                    ]),

                                Section::make('OAuth 2.0')
                                    ->description(
                                        'Manage third-party sign-in options for your account.'
                                    )
                                    ->compact()
                                    ->secondary()
                                    ->schema([
                                        View::make(
                                            'filament.pages.partials.google-oauth'
                                        ),
                                    ]),
                            ]),
                    ])
                    ->persistTabInQueryString('q'),
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Passkey Notifications
    |--------------------------------------------------------------------------
    */

    #[On('passkeyActionFailed')]
    public function handlePasskeyActionFailed(string $message): void
    {
        Notification::make()
            ->title('Passkey action failed')
            ->body($message)
            ->warning()
            ->send();
    }

    /*
    |--------------------------------------------------------------------------
    | Trusted Devices
    |--------------------------------------------------------------------------
    */

    public function getTrustedDevices()
    {
        TrustedDevice::query()
            ->where('user_id', $this->getUser()->id)
            ->where('expires_at', '<=', now())
            ->delete();

        return TrustedDevice::query()
            ->where('user_id', $this->getUser()->id)
            ->orderByDesc('updated_at')
            ->get();
    }

    public function revokeDeviceAction(): Action
    {
        return Action::make('revokeDevice')
            ->label('Revoke')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Remove trusted device')
            ->modalDescription(
                "You'll need to verify with MFA next time you sign in from this device."
            )
            ->modalSubmitActionLabel('Remove device')
            ->action(function (array $arguments): void {
                $this->performDeviceRevoke(
                    (int) ($arguments['deviceId'] ?? 0)
                );
            });
    }

    public function revokeOtherDevicesAction(): Action
    {
        return Action::make('revokeOtherDevices')
            ->label('Remove all other devices')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Remove all other trusted devices')
            ->modalDescription(
                'Every device except this one will need to verify with MFA next time they sign in.'
            )
            ->modalSubmitActionLabel('Remove devices')
            ->action(function (): void {
                TrustedDevice::query()
                    ->where('user_id', $this->getUser()->id)
                    ->where(
                        'id',
                        '!=',
                        session('current_trusted_device_id')
                    )
                    ->delete();

                Notification::make()
                    ->title('Other trusted devices removed')
                    ->success()
                    ->send();
            });
    }

    protected function performDeviceRevoke(int $deviceId): void
    {
        $device = TrustedDevice::query()
            ->where('user_id', $this->getUser()->id)
            ->where('id', $deviceId)
            ->first();

        if (! $device) {
            return;
        }

        $wasCurrentDevice = $device->id === session(
            'current_trusted_device_id'
        );

        $device->delete();

        if ($wasCurrentDevice) {
            session()->forget('current_trusted_device_id');

            $raw = request()->cookie('trusted_device');

            $map = $raw
                ? (json_decode($raw, true) ?: [])
                : [];

            unset($map[(string) $this->getUser()->id]);

            $map = TrustedDeviceCookie::prune($map);

            if (empty($map)) {
                cookie()->queue(
                    cookie()->forget('trusted_device')
                );
            } else {
                cookie()->queue(
                    cookie()->make(
                        'trusted_device',
                        json_encode($map),
                        60 * 24 * 30,
                        path: '/',
                        secure: true,
                        httpOnly: true,
                        sameSite: 'lax',
                    )
                );
            }
        }

        Notification::make()
            ->title('Device removed')
            ->success()
            ->send();
    }

    /*
    |--------------------------------------------------------------------------
    | Active Sessions
    |--------------------------------------------------------------------------
    */

    public function getActiveSessions()
    {
        return DB::table('sessions')
            ->where('user_id', $this->getUser()->id)
            ->orderByDesc('last_activity')
            ->get()
            ->map(function ($session) {
                $session->is_current_device =
                    $session->id === session()->getId();

                $session->last_active = Carbon::createFromTimestamp(
                    $session->last_activity
                )->diffForHumans();

                $session->client_hints = SessionPayloadReader::readHints(
                    $session->payload ?? null
                );

                return $session;
            });
    }

    public function logoutSessionAction(): Action
    {
        return Action::make('logoutSession')
            ->label('Log out')
            ->color('danger')
            ->icon('heroicon-o-arrow-right-on-rectangle')
            ->requiresConfirmation()
            ->modalHeading('Log out from this device?')
            ->modalDescription(
                'You will be signed out immediately and redirected to the login page.'
            )
            ->modalSubmitActionLabel('Log Out')
            ->action(function (array $arguments): void {
                $this->logoutSession(
                    $arguments['sessionId'] ?? null
                );
            });
    }

    public function logoutSession(?string $sessionId): void
    {
        if ($sessionId !== session()->getId()) {
            return;
        }

        Filament::auth()->logout();

        session()->invalidate();
        session()->regenerateToken();

        $this->redirect(
            Filament::getLoginUrl(),
            navigate: true
        );
    }

    public function revokeSessionAction(): Action
    {
        return Action::make('revokeSession')
            ->label('Log out')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Log out from this device?')
            ->modalSubmitActionLabel('Log Out')
            ->schema([
                TextInput::make('password')
                    ->label('Current password')
                    ->password()
                    ->revealable()
                    ->required(),
            ])
            ->action(function (
                array $data,
                array $arguments
            ): void {
                $this->performRevoke(
                    $data['password'],
                    $arguments['sessionId'] ?? null
                );
            });
    }

    protected function performRevoke(
        string $password,
        ?string $sessionId
    ): void {
        $user = $this->getUser();

        if (! Hash::check($password, $user->password)) {
            Notification::make()
                ->title('Incorrect password')
                ->danger()
                ->send();

            return;
        }

        $query = DB::table('sessions')
            ->where('user_id', $user->id);

        if ($sessionId) {
            $query->where('id', $sessionId);
        } else {
            $query->where('id', '!=', session()->getId());
        }

        $query->delete();

        $user->setRememberToken(Str::random(60));
        $user->save();

        $guard = Filament::auth();

        if (
            $guard instanceof SessionGuard &&
            request()->cookie($guard->getRecallerName())
        ) {
            $guard->login($user, true);
        }

        Notification::make()
            ->title(
                $sessionId
                    ? 'Device logged out'
                    : 'Logged out of all other devices'
            )
            ->success()
            ->send();

        $this->form->fill($this->form->getState());
    }

    /*
    |--------------------------------------------------------------------------
    | Google OAuth
    |--------------------------------------------------------------------------
    */

    public function toggleGoogleOAuthAction(): Action
    {
        return Action::make('toggleGoogleOAuth')
            ->label(
                fn (): string => $this->getUser()->google_oauth_enabled
                    ? 'Disable'
                    : 'Enable'
            )
            ->color(
                fn (): string => $this->getUser()->google_oauth_enabled
                    ? 'danger'
                    : 'success'
            )
            ->requiresConfirmation()
            ->modalHeading(
                fn (): string => $this->getUser()->google_oauth_enabled
                    ? 'Disable Google sign-in?'
                    : 'Enable Google sign-in?'
            )
            ->modalDescription(
                fn (): string => $this->getUser()->google_oauth_enabled
                    ? "You'll no longer be able to sign in using your Google account."
                    : 'You will be able to sign in using your Google account again.'
            )
            ->modalSubmitActionLabel(
                fn (): string => $this->getUser()->google_oauth_enabled
                    ? 'Disable'
                    : 'Enable'
            )
            ->action(function (): void {
                $user = $this->getUser();

                $user->update([
                    'google_oauth_enabled' => ! $user->google_oauth_enabled,
                ]);

                Notification::make()
                    ->title(
                        $user->google_oauth_enabled
                            ? 'Google sign-in enabled'
                            : 'Google sign-in disabled'
                    )
                    ->success()
                    ->send();
            });
    }

    public function unlinkGoogleAccountAction(): Action
    {
        return Action::make('unlinkGoogleAccount')
            ->label('Unlink Google Account')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Unlink your Google account?')
            ->modalDescription(
                "You'll need to sign in with your Google account again to relink it in the future."
            )
            ->modalSubmitActionLabel('Unlink')
            ->action(function (): void {
                $this->getUser()->update([
                    'google_id' => null,
                ]);

                Notification::make()
                    ->title('Google account unlinked')
                    ->success()
                    ->send();
            });
    }
}
