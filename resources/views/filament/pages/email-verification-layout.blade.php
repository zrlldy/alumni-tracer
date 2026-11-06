<x-filament-panels::layout.base :livewire="$livewire">
    <div class="verification-layout">
        <section class="verification-content" aria-label="Email verification">
            <header class="verification-header">
                <img src="{{ asset('images/alumni-darklogo.svg') }}" alt="" width="96" height="56" class="verification-logo-light" />
                <img src="{{ asset('images/alumni-whitelogo.svg') }}" alt="" width="96" height="56" class="verification-logo-dark" />
                <span>Alumni Tracer</span>
            </header>

            <main id="fi-main-content" class="verification-main" tabindex="-1">
                {{ $slot }}
            </main>

            <footer class="verification-footer">© {{ now()->year }} Alumni Tracer</footer>
        </section>

        <aside class="verification-community" aria-labelledby="verification-community-title">
            <picture>
                <source media="(min-width: 1024px)" srcset="{{ asset('images/alumni-tracer-verification.jpg') }}" />
                <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="" width="1254" height="1254" class="verification-artwork" fetchpriority="high" />
            </picture>
            <div class="verification-community-copy">
                <h2 id="verification-community-title">Connected beyond graduation.</h2>
            </div>
        </aside>
    </div>
</x-filament-panels::layout.base>
