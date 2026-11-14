<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Support\AllowedEmailDomains;
use App\Support\SystemSecurity;
use App\Support\Terms;
use Database\Factories\UserFactory;
use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthentication;
use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthenticationRecovery;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Auth\MultiFactor\Email\Concerns\InteractsWithEmailAuthentication;
use Filament\Auth\MultiFactor\Email\Contracts\HasEmailAuthentication;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\LaravelPasskeys\Models\Concerns\HasPasskeys;
use Spatie\LaravelPasskeys\Models\Concerns\InteractsWithPasskeys;

#[Fillable([
    'user_allocation_id',
    'avatar_img',
    'name',
    'email',
    'email_verified_at',
    'password',
    'password_set_at',
    'app_authentication_secret',
    'google_id',
    'google_oauth_enabled',
])]
#[Hidden(['password', 'remember_token'])]
// class User extends Authenticatable implements HasAppAuthentication, HasEmailAuthentication, MustVerifyEmail
class User extends Authenticatable implements FilamentUser, HasAppAuthentication, HasAvatar, HasEmailAuthentication, HasPasskeys, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, InteractsWithAppAuthentication, InteractsWithAppAuthenticationRecovery, InteractsWithEmailAuthentication, InteractsWithPasskeys, Notifiable, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getFilamentAvatarUrl(): ?string
    {
        $image = $this->avatar_img;

        return filled($image)
            ? asset('storage/'.ltrim($image, '/'))
            : null;
    }

    public function hasAcceptedCurrentTerms(): bool
    {
        return $this->terms_accepted_version === Terms::VERSION
            && $this->terms_accepted_at !== null;
    }

    public function acceptCurrentTerms(): void
    {
        if ($this->hasAcceptedCurrentTerms()) {
            return;
        }

        $this->forceFill([
            'terms_accepted_version' => Terms::VERSION,
            'terms_accepted_at' => now(),
        ])->saveOrFail();
    }

    protected static function booted(): void
    {
        static::deleting(function (User $user) {
            return ! self::isProtectedEmail($user->email);
        });

        static::forceDeleting(function (User $user) {
            return ! self::isProtectedEmail($user->email);
        });

        static::updating(function (User $user) {
            // getOriginal() gets the email as it was BEFORE this update attempt
            if (
                in_array($user->getOriginal('email'), self::protectedEmails())
                && $user->isDirty('email')
            ) {
                return false; // cancels the entire update
            }
        });

        static::saving(function (User $user) {
            if (
                $user->exists
                && in_array($user->getOriginal('email'), self::protectedEmails())
                && $user->isDirty('email')
            ) {
                $user->email = $user->getOriginal('email'); // silently revert just the email
            }
            if ($user->exists && $user->isDirty('email') && filled($user->google_id)) {
                $user->google_id = null;
            }
        });
    }

    public function alumnis(): HasMany
    {
        return $this->hasMany(Alumni::class);
    }

    public function userAllocation()
    {
        return $this->belongsTo(UserAllocation::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        $email = strtolower(trim($this->email));

        if (SystemSecurity::isBootstrapSuperAdmin($this)) {
            return true;
        }

        if (request()->getHost() === config('app.developer_host')) {
            return in_array(
                $email,
                self::developerEmails(),
                true,
            );
        }

        if ($panel->getId() !== 'online') {
            return false;
        }

        return AllowedEmailDomains::passes($email);
    }

    public static function protectedEmails(): array
    {
        return SystemSecurity::superAdminEmails();
    }

    protected static function isProtectedEmail(string $email): bool
    {
        return in_array(
            strtolower(trim($email)),
            array_map('strtolower', self::protectedEmails()),
            true,
        );
    }

    public static function developerEmails(): array
    {
        return SystemSecurity::developerEmails();
    }
}
