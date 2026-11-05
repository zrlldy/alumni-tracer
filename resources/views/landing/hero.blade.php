<section class="landing-hero" aria-labelledby="landing-title" data-sc-act="flow" data-hero-scene>
    <svg class="hero-orbit" viewBox="0 0 1440 760" fill="none" aria-hidden="true">
        <path d="M-80 590C160 120 1210 760 1510 110"/>
        <path d="M-50 420C330 870 1080 -150 1510 420"/>
        <circle cx="265" cy="360" r="5"/><circle cx="1174" cy="332" r="5"/>
    </svg>

    <div class="hero-layer hero-school-card" data-hero-layer="school" aria-hidden="true">
        <div class="hero-school-card-face">
            <img src="{{ asset('images/alumni-whitelogo.svg') }}" alt="" width="120" height="69">
            <span>A connection for life</span>
        </div>
    </div>

    <div class="hero-layer hero-graduates" data-hero-layer="graduates">
        <div class="hero-photo-paper" aria-hidden="true"></div>
        <img src="{{ asset('images/alumni-graduates.png') }}" alt="Illustration of a graduate alongside fellow alumni" width="760" height="760" fetchpriority="high">
    </div>

    <div class="hero-layer hero-cap" data-hero-layer="cap" aria-hidden="true">
        <svg viewBox="0 0 300 230" fill="none">
            <ellipse cx="150" cy="200" rx="94" ry="12" fill="#003049" opacity=".08"/>
            <path d="M69 99L153 75L230 101L220 153C180 184 118 183 79 153Z" fill="#003049"/>
            <path d="M153 75L230 101L220 153C199 169 176 176 153 176Z" fill="#071923"/>
            <path d="M18 81L150 25L284 81L152 140Z" fill="#003049"/>
            <path d="M18 81L152 140L284 81V90L152 149L18 90Z" fill="#071923"/>
            <path d="M24 81L150 30L278 81" stroke="white" stroke-opacity=".22" stroke-width="2"/>
            <path d="M153 83L252 111V173" stroke="#FCBF49" stroke-width="5" stroke-linecap="round"/>
            <circle cx="153" cy="83" r="7" fill="#D62828"/>
            <path d="M252 170L242 201H262Z" fill="#FCBF49"/>
            <path d="M252 180V201" stroke="#F77F00" stroke-width="2"/>
        </svg>
    </div>

    <div class="hero-layer hero-diploma" data-hero-layer="diploma" aria-hidden="true">
        <div class="hero-diploma-paper"><span></span><i></i><b></b></div>
    </div>

    <div class="landing-container landing-hero-copy">
        <h1 id="landing-title">Your next chapter.<br><span>Still connected.</span></h1>
        <p>Stay connected with your school as your career takes shape. Your alumni journey continues here.</p>
        <div class="landing-hero-actions">
            <a href="{{ route('filament.online.auth.login') }}" class="landing-button">Sign in</a>
            <a href="{{ route('filament.online.auth.register') }}" class="landing-text-link">Create an account</a>
        </div>
    </div>
</section>
<nav class="landing-container landing-tool-index" aria-label="Explore the platform">
    <a href="#directory-details">Alumni records</a>
    <a href="#tracing-details">Career journeys</a>
    <a href="#connection">Your school connection</a>
    <a href="#report-details">Reports & insights</a>
</nav>
