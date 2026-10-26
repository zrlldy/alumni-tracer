<footer class="landing-footer">
    <div class="landing-container landing-footer-top">
        <div>
            <h2>Back to your alumni records.</h2>
            <p>Sign in to continue working with your team.</p>
        </div>
        <div class="flex flex-wrap items-center gap-6">
            <a href="{{ route('filament.online.auth.login') }}" class="landing-button">Sign in</a>
            <a href="{{ route('filament.online.auth.register') }}" class="landing-text-link">Create an account</a>
        </div>
    </div>
    <div class="landing-container landing-footer-bottom">
        <a href="#top" class="landing-brand" aria-label="Alumni Tracer home">
            <img class="landing-brand-image" src="{{ asset('images/alumni-whitelogo.svg') }}" alt="Alumni Tracer" width="128" height="74">
        </a>
        <p>© {{ now()->year }} Alumni Tracer</p>
        <a href="#top" class="landing-text-link">Back to top</a>
    </div>
</footer>
