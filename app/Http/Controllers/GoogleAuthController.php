<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\AllowedEmailDomains;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function relink(): RedirectResponse
    {
        /** @var User|null $currentUser */
        $currentUser = request()->user();

        if (! $currentUser) {
            return redirect(Filament::getLoginUrl());
        }

        if (filled($currentUser->google_id)) {
            return redirect()->route('filament.portal.auth.profile');
        }

        session(['google_link_intent' => $currentUser->id]);

        return Socialite::driver('google')
            ->with(['prompt' => 'select_account consent'])
            ->redirect();
    }

    public function callback(): RedirectResponse
    {
        if (request()->has('error') || ! request()->has('code')) {
            session()->forget('google_link_intent');

            return $this->failure('Google sign-in was cancelled.');
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (InvalidStateException $exception) {
            session()->forget('google_link_intent');

            return $this->failure(
                'Your Google sign-in request expired or was already used. Please try signing in again.'
            );
        }

        $email = Str::lower(trim((string) $googleUser->getEmail()));
        $googleId = trim((string) $googleUser->getId());

        if (
            blank($email) ||
            ! filter_var($email, FILTER_VALIDATE_EMAIL) ||
            ! ($googleUser->user['email_verified'] ?? false)
        ) {
            session()->forget('google_link_intent');

            return $this->failure('Your Google account email is not verified.');
        }

        if (blank($googleId)) {
            session()->forget('google_link_intent');

            return $this->failure('Unable to verify your Google account identity.');
        }

        // Pull the intent so a failed domain check cannot leave a stale
        // account-linking request in the session.
        $linkIntentUserId = session()->pull('google_link_intent');

        // Enforce the same domain policy for both sign-in and account relinking.
        // Exact configured bootstrap Super Admin emails are domain-exempt.
        if (! AllowedEmailDomains::passes($email)) {
            return $this->failure(
                'Your Google email address is not authorized to access this system. Please use an approved email domain.'
            );
        }

        if ($linkIntentUserId) {
            return $this->handleRelink(
                (int) $linkIntentUserId,
                $googleUser,
                $email
            );
        }

        // Find accounts by both identifiers, including soft-deleted accounts.
        $userByGoogleId = User::withTrashed()
            ->where('google_id', $googleId)
            ->first();

        $userByEmail = User::withTrashed()
            ->whereRaw('LOWER(TRIM(email)) = ?', [$email])
            ->first();

        // Do not allow a deactivated account to be recreated or authenticated.
        if (
            ($userByGoogleId && $userByGoogleId->trashed()) ||
            ($userByEmail && $userByEmail->trashed())
        ) {
            return $this->failure(
                'This account has been deactivated. Please contact HR/Admin.'
            );
        }

        // Google ID and email must never resolve to two different accounts.
        if (
            $userByGoogleId &&
            $userByEmail &&
            $userByGoogleId->id !== $userByEmail->id
        ) {
            return $this->failure(
                'Your Google account and email are associated with different user profiles. Please contact HR/Admin.'
            );
        }

        $user = $userByGoogleId ?? $userByEmail;

        if ($user) {
            // A Google ID match is valid only while the account email matches
            // the verified email returned by Google.
            if (
                $userByGoogleId &&
                Str::lower(trim((string) $userByGoogleId->email)) !== $email
            ) {
                return $this->failure(
                    'Your Google account email does not match the email registered in your profile. Please contact HR/Admin.'
                );
            }

            // Never replace a Google ID already linked to another identity.
            if (
                filled($user->google_id) &&
                ! hash_equals((string) $user->google_id, $googleId)
            ) {
                return $this->failure(
                    'This Google account does not match the one linked to your profile. Please contact HR/Admin.'
                );
            }

            // Link only when the verified Google email exactly matches the
            // existing account's email and that account has no Google ID.
            if (blank($user->google_id)) {
                try {
                    $user->update(['google_id' => $googleId]);
                } catch (QueryException $exception) {
                    report($exception);

                    return $this->failure(
                        'This Google account could not be linked because it is already associated with another account.'
                    );
                }
            }
        } else {
            $name = trim((string) $googleUser->getName());

            if (blank($name)) {
                $name = $email;
            }

            // Do not create another account when the Google name is already
            // used. A name match is never sufficient to link accounts.
            $existingNameUser = User::withTrashed()
                ->whereRaw(
                    'LOWER(TRIM(name)) = ?',
                    [Str::lower($name)]
                )
                ->first();

            if ($existingNameUser) {
                if ($existingNameUser->trashed()) {
                    return $this->failure(
                        'A deactivated account already uses this name. Please contact HR/Admin.'
                    );
                }

                return $this->failure(
                    'An account with this name already exists. For security, Google sign-in cannot automatically link accounts using a name alone. Please use your registered email address or contact HR/Admin.'
                );
            }

            try {
                $user = User::query()->create([
                    'name' => $name,
                    'email' => $email,
                    'google_id' => $googleId,
                    'password' => Hash::make(Str::random(40)),
                    'email_verified_at' => now(),
                ]);
            } catch (QueryException $exception) {
                // A unique index may reject a concurrent duplicate request.
                // Do not silently attach the Google identity to another user.
                report($exception);

                return $this->failure(
                    'An account using this email, name, or Google identity already exists. Please sign in with your registered account or contact HR/Admin.'
                );
            }
        }

        if (
            $user instanceof FilamentUser &&
            ! $user->canAccessPanel(Filament::getCurrentOrDefaultPanel())
        ) {
            return $this->failure(
                'This account does not have access to this panel.'
            );
        }

        Filament::auth()->login($user, true);

        session()->regenerate();

        return redirect()->intended(Filament::getUrl());
    }

    /**
     * Link the authenticated account to a Google identity.
     */
    protected function handleRelink(
        int $userId,
        SocialiteUser $googleUser,
        string $email
    ): RedirectResponse {
        /** @var User|null $loggedInUser */
        $loggedInUser = request()->user();

        $currentUser = User::query()->find($userId);

        if (
            ! $currentUser ||
            ! $loggedInUser ||
            $currentUser->id !== $loggedInUser->id
        ) {
            return $this->failure(
                'Your session has changed. Please try linking your Google account again.'
            );
        }

        if (
            Str::lower(trim($email)) !==
            Str::lower(trim((string) $currentUser->email))
        ) {
            return $this->failure(
                "This Google account ({$email}) doesn't match your account email ({$currentUser->email}). Please use the Google account matching your registered email."
            );
        }

        $googleId = trim((string) $googleUser->getId());

        $alreadyLinkedElsewhere = User::withTrashed()
            ->where('google_id', $googleId)
            ->where('id', '!=', $currentUser->id)
            ->exists();

        if ($alreadyLinkedElsewhere) {
            return $this->failure(
                'This Google account is already linked to a different user.'
            );
        }

        try {
            $currentUser->update(['google_id' => $googleId]);
        } catch (QueryException $exception) {
            report($exception);

            return $this->failure(
                'This Google account could not be linked because it is already associated with another account.'
            );
        }

        Notification::make()
            ->title('Google account linked')
            ->success()
            ->send();

        return redirect()->route('filament.portal.auth.profile');
    }

    protected function failure(string $message): RedirectResponse
    {
        Notification::make()
            ->title('Google sign-in failed')
            ->body($message)
            ->danger()
            ->send();

        return redirect(Filament::getLoginUrl());
    }
}
