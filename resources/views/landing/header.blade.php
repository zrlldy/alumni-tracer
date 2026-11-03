<header class="landing-header" id="top">
    <div class="landing-container landing-header-inner">
        <a href="#top" class="landing-brand" aria-label="Alumni Tracer home">
            <img src="{{ asset('images/alumni-whitelogo.svg') }}" alt="Alumni Tracer" width="112" height="65">
        </a>
        <nav class="landing-desktop-navigation hidden lg:flex" aria-label="Main navigation">
            <a href="#about">The platform</a>
            <a href="#connection">Your next chapter</a>
            <a href="#how-it-works">How it works</a>
        </nav>
        <div class="landing-header-actions">
            <button class="landing-theme-button" type="button" data-landing-theme aria-label="Switch to dark theme" aria-pressed="false" hidden>
                <svg class="landing-moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M20.6 13A8.6 8.6 0 0 1 11 3.4 8.6 8.6 0 1 0 20.6 13Z"/></svg>
                <svg class="landing-sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M2 12h2m16 0h2M5 5l1.5 1.5m11 11L19 19M5 19l1.5-1.5m11-11L19 5"/></svg>
            </button>
            <a class="landing-button landing-button-small hidden sm:inline-flex" href="{{ route('filament.online.auth.login') }}">Sign in</a>
            <button class="landing-menu-button lg:hidden" type="button" data-landing-menu aria-expanded="false" aria-controls="landing-mobile-navigation" hidden>
                <span data-landing-menu-label>Menu</span>
            </button>
        </div>
        <nav id="landing-mobile-navigation" class="landing-mobile-navigation lg:hidden" aria-label="Mobile navigation">
            <a href="#about">The platform</a>
            <a href="#connection">Your next chapter</a>
            <a href="#how-it-works">How it works</a>
            <a href="#questions">Questions</a>
            <a href="{{ route('filament.online.auth.login') }}">Sign in</a>
            <a href="{{ route('filament.online.auth.register') }}">Create an account</a>
        </nav>
    </div>
</header>
