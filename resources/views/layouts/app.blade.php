<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#344723">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php
            // Pages set "title" (brand added automatically) or "seo_title" (used as-is),
            // "description", and optionally "og_image" and "robots".
            // @section values arrive HTML-escaped; decode so {{ }} below escapes them only once.
            $section = fn (string $name) => trim(html_entity_decode($__env->yieldContent($name), ENT_QUOTES | ENT_HTML5));
            $seoTitle = $section('seo_title') ?: \App\Support\Seo::title($section('title') ?: config('site.name'));
            $seoDescription = \App\Support\Seo::description($section('description'));
            $seoImage = trim($__env->yieldContent('og_image')) ?: asset('icons/icon-512.png');
            $canonical = url()->current();
        @endphp
        <title>{{ $seoTitle }}</title>
        <meta name="description" content="{{ $seoDescription }}">
        <meta name="robots" content="@yield('robots', 'index, follow, max-image-preview:large')">
        <link rel="canonical" href="{{ $canonical }}">

        {{-- Link previews on WhatsApp, Facebook, LinkedIn and X --}}
        <meta property="og:site_name" content="{{ config('site.name') }}">
        <meta property="og:type" content="@yield('og_type', 'website')">
        <meta property="og:title" content="{{ $seoTitle }}">
        <meta property="og:description" content="{{ $seoDescription }}">
        <meta property="og:url" content="{{ $canonical }}">
        <meta property="og:image" content="{{ $seoImage }}">
        <meta property="og:locale" content="en_US">
        <meta name="twitter:card" content="summary_large_image">

        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="48x48">
        <link rel="icon" href="{{ asset('icons/favicon-32.png') }}" type="image/png" sizes="32x32">
        <link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon.png') }}">
        <link rel="manifest" href="{{ asset('site.webmanifest') }}">

        {!! \App\Support\Seo::jsonLd([\App\Support\Seo::organization(), \App\Support\Seo::website()]) !!}
        @stack('structured-data')

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,600;1,9..144,400&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/css/theme.css', 'resources/js/app.js'])
    </head>
    <body class="flex min-h-screen flex-col bg-cream-50 pb-16 font-sans text-charcoal-900 lg:pb-0">
        <a class="skip-link" href="#main">Skip to content</a>

        <x-site-navbar />

        <main id="main" class="flex-1">
            @yield('content')
        </main>

        <x-site-footer />

        <x-site-bottom-nav />

        <x-language-switcher />

        <div id="google_translate_element" class="notranslate" style="display:none"></div>
        <script>
            function googleTranslateElementInit() {
                new google.translate.TranslateElement({
                    pageLanguage: 'en',
                    includedLanguages: 'zh-CN,fr,it,es,ar',
                    autoDisplay: false,
                }, 'google_translate_element');
            }
        </script>
        <script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit" async></script>
    </body>
</html>
