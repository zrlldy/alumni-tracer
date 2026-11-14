<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="color-scheme" content="light dark">
        <meta name="description" content="Terms and Conditions for the CTU – Tabogon Extension Online Alumni Tracer System for Employability Status.">
        <title>Terms and Conditions · Alumni Tracer</title>
        <link rel="icon" href="{{ asset('favicon.ico') }}">
        <link rel="stylesheet" href="{{ asset('fonts/filament/filament/inter/index.css') }}">
        @vite('resources/css/app.css')
    </head>
    <body class="terms-page">
        <a href="#terms-content" class="terms-skip">Skip to terms</a>
        <header class="terms-header">
            <a href="{{ route('welcome') }}" class="terms-brand">Alumni Tracer</a>
            <a href="{{ auth()->check() ? route('filament.online.pages.dashboard') : route('filament.online.auth.login') }}">{{ auth()->check() ? 'Return to portal' : 'Sign in' }}</a>
        </header>
        <main id="terms-content" class="terms-layout" tabindex="-1">
            <aside class="terms-index">
                <nav aria-label="Terms sections">
                    <p>On this page</p>
                    @include('legal.terms-index')
                </nav>
            </aside>
            <article class="terms-document">
                <details class="terms-mobile-index">
                    <summary>On this page</summary>
                    <nav aria-label="Terms sections">@include('legal.terms-index')</nav>
                </details>
                <header class="terms-title">
                    <h1>{{ $document[0]['text'] }}</h1>
                    <p class="terms-subtitle">{{ $document[1]['text'] }}</p>
                    <p>{{ $document[2]['text'] }}</p>
                    <div class="terms-meta">
                        <p>{{ $document[3]['text'] }}</p>
                        <p>Version {{ \App\Support\Terms::VERSION }}</p>
                    </div>
                </header>
                @if ($acceptance)
                    <p class="terms-intro">Please review these terms and confirm your agreement below to continue to your account.</p>
                @endif
                @foreach (array_slice($document, 4, preserve_keys: true) as $index => $block)
                    @if ($block['type'] === 'heading')
                        <h2 id="section-{{ $index }}">{{ $block['text'] }}</h2>
                    @else
                        <p>{{ $block['text'] }}</p>
                    @endif
                @endforeach
                @if ($acceptance)
                    <section class="terms-acceptance" aria-labelledby="acceptance-heading">
                        <h2 id="acceptance-heading">Your agreement</h2>
                        <form method="POST" action="{{ route('terms.store') }}">
                            @csrf
                            <input type="hidden" name="version" value="{{ \App\Support\Terms::VERSION }}">
                            <label class="terms-checkbox" for="terms">
                                <input id="terms" name="terms" type="checkbox" value="1" required @if($errors->has('terms')) aria-invalid="true" aria-describedby="terms-error" @endif>
                                <span>I agree to the Terms and Conditions.</span>
                            </label>
                            @error('terms')<p id="terms-error" class="terms-error" role="alert">{{ $message }}</p>@enderror
                            @error('version')<p class="terms-error" role="alert">{{ $message }}</p>@enderror
                            <button class="terms-button" type="submit">Agree and continue</button>
                        </form>
                        <form method="POST" action="{{ route('filament.online.auth.logout') }}">
                            @csrf
                            <button class="terms-signout" type="submit">Sign out</button>
                        </form>
                    </section>
                @endif
                <footer class="terms-footer">
                    <a href="{{ route('welcome') }}">Alumni Tracer home</a>
                    <a href="#terms-content">Back to top</a>
                </footer>
            </article>
        </main>
    </body>
</html>
