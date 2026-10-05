<?php
// app/Http/Controllers/GoogleAuthController.php
namespace App\Http\Controllers;

use App\Models\User;
// use App\Support\AllowedEmailDomains;
// use App\Support\ClientHints;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Contracts\User as SocialiteUser;

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
            return $this->failure('Google sign-in was cancelled.');
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (InvalidStateException $e) {
            return $this->failure('Your Google sign-in request expired or was already used. Please try signing in again.');
        }

        $email = $googleUser->getEmail();

        if (blank($email) || ! ($googleUser->user['email_verified'] ?? false)) {
            return $this->failure('Your Google account email is not verified.');
        }

        $linkIntentUserId = session()->pull('google_link_intent');

        if ($linkIntentUserId) {
            return $this->handleRelink((int) $linkIntentUserId, $googleUser, $email);
        }

        // if (! AllowedEmailDomains::passes($email)) {
        //     return $this->failure('Google sign-in is limited to company email addresses only.');
        // }

        $user = User::withTrashed()->where('email', $email)->first();

        if ($user && $user->trashed()) {
            return $this->failure('This account has been deactivated. Please contact HR/Admin.');
        }

        // if ($user && ! $user->google_oauth_enabled) {
        //     return $this->failure('Google sign-in has been disabled for this account. Please use your password instead.');
        // }

        if ($user) {
            if (blank($user->google_id)) {
                $user->update(['google_id' => $googleUser->getId()]);
            } elseif ($user->google_id !== $googleUser->getId()) {
                return $this->failure('This Google account does not match the one linked to your profile. Please contact HR/Admin if this is unexpected.');
            }
        } else {
            $user = User::query()->create([
                'name' => $googleUser->getName() ?: $email,
                'email' => $email,
                'google_id' => $googleUser->getId(),
                // Random & unguessable — this can never be typed into a
                // login form and matched. `password_set_at` (left null
                // here) is the real signal for "has this user chosen
                // their own password", not this column.
                'password' => Hash::make(Str::random(40)),
                'email_verified_at' => now(),
            ]);
        }

        if (
            $user instanceof FilamentUser
            && ! $user->canAccessPanel(Filament::getCurrentOrDefaultPanel())
        ) {
            return $this->failure('This account does not have access to this panel.');
        }

        Filament::auth()->login($user, true);

        session()->regenerate();

        // ClientHints::rememberInSession(request());

        return redirect()->intended(Filament::getUrl());
    }

    protected function handleRelink(int $userId, SocialiteUser $googleUser, string $email): RedirectResponse
    {
        /** @var User|null $loggedInUser */
        $loggedInUser = request()->user();

        $currentUser = User::find($userId);

        if (! $currentUser || ! $loggedInUser || $currentUser->id !== $loggedInUser->id) {
            return $this->failure('Your session has changed. Please try linking your Google account again.');
        }

        if (strtolower($email) !== strtolower($currentUser->email)) {
            return $this->failure(
                "This Google account ({$email}) doesn't match your account email ({$currentUser->email}). Please use the Google account matching your registered email."
            );
        }

        $alreadyLinkedElsewhere = User::query()
            ->where('google_id', $googleUser->getId())
            ->where('id', '!=', $currentUser->id)
            ->exists();

        if ($alreadyLinkedElsewhere) {
            return $this->failure('This Google account is already linked to a different user.');
        }

        $currentUser->update(['google_id' => $googleUser->getId()]);

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