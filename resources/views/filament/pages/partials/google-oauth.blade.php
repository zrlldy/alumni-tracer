{{-- resources/views/filament/pages/partials/google-oauth.blade.php --}}
@php($user = $this->getUser())

<div class="p-4">
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>

                <h4 class="font-semibold text-gray-950 dark:text-white">
                    Google Sign-In
                </h4>
            </div>

            <button
                type="button"
                wire:click="mountAction('toggleGoogleOAuth')"
                class="mt-1 inline-flex items-center gap-1.5 text-sm font-medium transition-colors
                    {{ $user->google_oauth_enabled
                        ? 'text-danger-600 dark:text-danger-400 hover:text-danger-500'
                        : 'text-primary-600 dark:text-primary-400 hover:text-primary-500' }}"
            >
                @if ($user->google_oauth_enabled)
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 9.9-1"/>
                    </svg>
                    Turn off
                @else
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    Turn on
                @endif
            </button>
            {{-- 
            @if ($user->google_oauth_enabled)
            @if ($user->google_id)
                <span class="mx-1 text-gray-300 dark:text-gray-600">&middot;</span>
                <button
                    type="button"
                    wire:click="mountAction('unlinkGoogleAccount')"
                    class="text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors"
                >
                    Unlink account
                </button>
            @else
                <span class="mx-1 text-gray-300 dark:text-gray-600">&middot;</span>
                
                <a href="{{ route('auth.google.relink') }}"
                    class="text-sm font-medium text-primary-500 hover:text-primary-500 transition-colors"
                >
                    Link Google Account
                </a>
            @endif
            @endif
            --}}

            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                @if ($user->google_id)
                    Sign in using your linked Google account instead of your password.
                @else
                    Sign in with Google once to link your account, then manage it here.
                @endif
            </p>
        </div>

        <span class="shrink-0 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
            {{ $user->google_oauth_enabled
                ? 'bg-success-50 dark:bg-success-500/10 text-success-700 dark:text-success-400'
                : 'bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-gray-400' }}"
        >
            {{ $user->google_oauth_enabled ? 'Enabled' : 'Disabled' }}
        </span>
    </div>
</div>