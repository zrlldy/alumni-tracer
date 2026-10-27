<footer class="landing-footer" data-sc-act="flow">
    <div class="landing-container landing-close">
        <p class="landing-context" data-chapter-summary>Your career journey. Your school connection.</p>
        <h2 data-sc-cue="0 1 0 0">New chapter.<br>Same connection.</h2>
        <p>Sign in to Alumni Tracer, or create an account to access the platform.</p>
        <div class="landing-close-actions">
            <a href="{{ route('filament.online.auth.login') }}" class="landing-button">Sign in</a>
            <a href="{{ route('filament.online.auth.register') }}" class="landing-text-link">Create an account</a>
        </div>
    </div>
    <div class="landing-container landing-footer-bottom">
        <a href="#top" class="landing-brand" aria-label="Alumni Tracer home"><img src="{{ asset('images/alumni-whitelogo.svg') }}" alt="Alumni Tracer" width="112" height="65"></a>
        <p>© {{ now()->year }} Alumni Tracer</p>
        <a href="#top" class="landing-text-link">Back to top</a>
    </div>
</footer>
