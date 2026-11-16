<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\AllowedEmailDomains;
use App\Support\Terms;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class GoogleAuthController extends Controller
{
    /**
     * Start Google login for an existing account.
     * This route must never create a new account.
     */
    public function redirect(): RedirectResponse
    {
        $this->clearGoogleFlowSession();

        session()->put('google_auth_intent', 'login');

        return Socialite::driver('google')->redirect();
    }

    /**
     * Start Google registration only after the current terms have been accepted.
     */
    public function signupRedirect(): RedirectResponse
    {
        if (session('google_signup_terms_version') !== Terms::VERSION) {
            $this->clearGoogleFlowSession();

            return redirect()->route('terms.google-signup');
        }

        // A registration attempt must not inherit an old account-link request.
        session()->forget('google_link_intent');
        session()->put('google_auth_intent', 'signup');

        return Socialite::driver('google')
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    /**
     * Start Google account relinking for the currently authenticated user.
     */
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

        // Do not let a previous login or registration intent affect relinking.
        $this->clearGoogleFlowSession();
        session(['google_link_intent' => $currentUser->id]);

        return Socialite::driver('google')
            ->with(['prompt' => 'select_account consent'])
            ->redirect();
    }

    /**
     * Handle Google login, registration, and account-relink callbacks.
     */
    public function callback(): RedirectResponse
    {
        if (request()->has('error') || ! request()->has('code')) {
            return $this->failure('Google sign-in was cancelled.');
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (InvalidStateException $exception) {
            report($exception);

            return $this->failure(
                'Your Google sign-in request expired or was already used. Please try signing in again.'
            );
        }

        // Account relinking is a separate authenticated operation.
        $linkIntentUserId = session()->pull('google_link_intent');

        if ($linkIntentUserId) {
            return $this->handleRelink(
                (int) $linkIntentUserId,
                $googleUser,
                Str::lower(trim((string) $googleUser->getEmail()))
            );
        }

        $authIntent = session()->pull('google_auth_intent', 'login');
        $signupTermsAccepted = session('google_signup_terms_version') === Terms::VERSION;

        // Recheck the accepted version on callback, not only before redirecting
        // to Google. This prevents an old or missing session acceptance from
        // being used to create an account.
        if ($authIntent === 'signup' && ! $signupTermsAccepted) {
            $this->clearGoogleFlowSession();

            return redirect()->route('terms.google-signup');
        }

        if ($authIntent !== 'signup') {
            session()->forget('google_signup_terms_version');
        }

        $email = Str::lower(trim((string) $googleUser->getEmail()));
        $googleId = trim((string) $googleUser->getId());

        if (
            blank($email) ||
            ! filter_var($email, FILTER_VALIDATE_EMAIL) ||
            ! ($googleUser->user['email_verified'] ?? false)
        ) {
            return $this->failure('Your Google account email is not verified.');
        }

        if (blank($googleId)) {
            return $this->failure('Unable to verify your Google account identity.');
        }

        // Enforce the same domain policy for both sign-in and registration.
        // Exact configured bootstrap Super Admin emails are domain-exempt.
        if (! AllowedEmailDomains::passes($email)) {
            return $this->failure(
                'Your Google email address is not authorized to access this system. Please use an approved email domain.'
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
                'This account has been deactivated. Please contact Admin.'
            );
        }

        // Google ID and email must never resolve to two different accounts.
        if (
            $userByGoogleId &&
            $userByEmail &&
            $userByGoogleId->id !== $userByEmail->id
        ) {
            return $this->failure(
                'Your Google account and email are associated with different user profiles. Please contact Admin.'
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
                    'Your Google account email does not match the email registered in your profile. Please contact Admin.'
                );
            }

            // Never replace a Google ID already linked to another identity.
            if (
                filled($user->google_id) &&
                ! hash_equals((string) $user->google_id, $googleId)
            ) {
                return $this->failure(
                    'This Google account does not match the one linked to your profile. Please contact Admin.'
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
            // Normal Google login must never silently register a new account.
            if ($authIntent !== 'signup') {
                return $this->failure(
                    'No account was found for this Google account. Please register first using the Google registration option.'
                );
            }

            $name = trim((string) $googleUser->getName());

            if (blank($name)) {
                $name = $email;
            }

            try {
                // Create the new account and record its terms acceptance in
                // one transaction so neither operation is saved alone.
                $user = DB::transaction(function () use ($name, $email, $googleId): User {
                    $newUser = User::query()->create([
                        'name' => $name,
                        'email' => $email,
                        'google_id' => $googleId,
                        'password' => Hash::make(Str::random(40)),
                        'email_verified_at' => now(),
                    ]);

                    $newUser->acceptCurrentTerms();

                    return $newUser;
                });
            } catch (QueryException $exception) {
                // A unique index may reject a concurrent duplicate request.
                // Do not silently attach the Google identity to another user.
                report($exception);

                return $this->failure(
                    'An account using this email or Google identity already exists. Please sign in with your registered account or contact Admin.'
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

        // If Google registration matched an existing account, persist the
        // acceptance made immediately before starting this OAuth flow.
        if ($authIntent === 'signup' && ! $user->hasAcceptedCurrentTerms()) {
            $user->acceptCurrentTerms();
        }

        Filament::auth()->login($user, true);

        session()->regenerate();
        $this->clearGoogleFlowSession();

        if (! $user->hasAcceptedCurrentTerms()) {
            return redirect()->route('terms.accept');
        }

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
            blank($email) ||
            ! filter_var($email, FILTER_VALIDATE_EMAIL) ||
            ! ($googleUser->user['email_verified'] ?? false)
        ) {
            return $this->failure('Your Google account email is not verified.');
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

        if (blank($googleId)) {
            return $this->failure('Unable to verify your Google account identity.');
        }

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

        $this->clearGoogleFlowSession();

        Notification::make()
            ->title('Google account linked')
            ->success()
            ->send();

        return redirect()->route('filament.portal.auth.profile');
    }

    /**
     * Remove transient state used by Google login, signup, and relinking.
     */
    protected function clearGoogleFlowSession(): void
    {
        session()->forget([
            'google_link_intent',
            'google_auth_intent',
            'google_signup_terms_version',
        ]);
    }

    protected function failure(string $message): RedirectResponse
    {
        $this->clearGoogleFlowSession();

        Notification::make()
            ->title('Google sign-in failed')
            ->body($message)
            ->danger()
            ->send();

        return redirect(Filament::getLoginUrl());
    }
}
