<section class="landing-hero" aria-labelledby="landing-title">
    <div class="landing-container landing-hero-layout">
        <div class="landing-hero-copy">
            <p class="landing-product-description">Alumni records & employment tracing</p>
            <h1 id="landing-title">A clearer picture of life after graduation.</h1>
            <p class="landing-hero-description">Keep graduate records, employment updates, and tracing notes together. Give your team a shared place to follow what comes next.</p>
            <div class="landing-hero-actions flex flex-wrap items-center gap-4 sm:gap-6">
                <a href="{{ route('filament.online.auth.login') }}" class="landing-button">Sign in</a>
                <a href="{{ route('filament.online.auth.register') }}" class="landing-hero-account">Create an account</a>
            </div>
        </div>

        <div class="landing-hero-artwork">
            <img src="{{ asset('images/alumni-tracer-login.png') }}" alt="Illustration of graduates and professionals in front of a campus, connected across the Philippines" width="1254" height="1254" fetchpriority="high" decoding="async">
        </div>
    </div>
</section>
<div class="landing-tools-section">
    <nav class="landing-container landing-tool-index" aria-label="Explore Alumni Tracer">
            <a href="#directory-details" class="landing-index-item">
                <span class="landing-index-title">Alumni directory</span>
                <span class="landing-index-description">Contact details, programs & graduation</span>
            </a>
            <a href="#tracing-details" class="landing-index-item">
                <span class="landing-index-title">Employment tracing</span>
                <span class="landing-index-description">Employment status, dates & follow-ups</span>
            </a>
            <a href="#report-details" class="landing-index-item">
                <span class="landing-index-title">Reports & exports</span>
                <span class="landing-index-description">Focused reports, ready for Excel</span>
            </a>
    </nav>
</div>
