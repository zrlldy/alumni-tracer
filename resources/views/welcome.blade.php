<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Manage alumni records, maintain employment tracing details, and export focused reports with Alumni Tracer.">
        <meta name="color-scheme" content="light dark">
        <title>Alumni Tracer</title>
        <link rel="icon" href="{{ asset('favicon.ico') }}">
        <link rel="stylesheet" href="{{ asset('fonts/filament/filament/inter/index.css') }}">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <noscript><style>.landing-loader { display: none !important; }</style></noscript>
    </head>
    <body class="landing-page antialiased">
        <div class="landing-loader" data-landing-loader aria-hidden="true">
            <div class="landing-loader-brand">
                <img src="{{ asset('images/favicon.png') }}" alt="" width="1640" height="945" fetchpriority="high">
                <span>Alumni Tracer</span>
                <span class="landing-loader-line"></span>
            </div>
        </div>
        <a class="landing-skip" href="#main-content">Skip to content</a>
        @include('landing.header')
        <main id="main-content" tabindex="-1">
            @include('landing.hero')
            @include('landing.features')
            @include('landing.connection')
            @include('landing.process')
            @include('landing.questions')
        </main>
        @include('landing.footer')
    </body>
</html>
