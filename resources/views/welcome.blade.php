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
    </head>
    <body class="landing-page antialiased">
        <a class="landing-skip" href="#main-content">Skip to content</a>
        @include('landing.header')
        <main id="main-content" tabindex="-1">
            @include('landing.hero')
            @include('landing.features')
            @include('landing.questions')
        </main>
        @include('landing.footer')
    </body>
</html>
