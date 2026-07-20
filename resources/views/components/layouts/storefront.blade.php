@props([
    'title' => null,
    'description' => 'Mobilier stradal și urban — bănci, coșuri de gunoi, jardiniere, locuri de joacă. Producător direct, livrare în toată țara.',
    'canonical' => null, // implicit url()->current() (fără query string)
    'ogImage' => null,   // implicit logo; paginile produs/categorie trimit imaginea primary
    'ogType' => 'website',
])

@php
    $canonicalUrl = $canonical ?? url()->current();
    $ogImageUrl = $ogImage ?? asset('images/social-card.svg');
@endphp

<!DOCTYPE html>
<html lang="ro" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $description }}">

    {{-- Marchează JS activ devreme: stările inițiale ale hero-ului se aplică doar cu .js. --}}
    <script>document.documentElement.classList.add('js');</script>

    @php
        $fullTitle = $title ? $title.' — '.config('contact.brand') : config('contact.brand').' — Mobilier stradal & urban';
    @endphp
    <title>{{ $fullTitle }}</title>
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta name="robots" content="index, follow">

    {{-- OpenGraph / social --}}
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:site_name" content="{{ config('contact.brand') }}">
    <meta property="og:title" content="{{ $fullTitle }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $ogImageUrl }}">
    <meta property="og:locale" content="ro_RO">
    <meta name="twitter:title" content="{{ $fullTitle }}">
    <meta name="twitter:description" content="{{ $description }}">
    <meta name="twitter:image" content="{{ $ogImageUrl }}">
    <meta name="twitter:card" content="{{ $ogImage ? 'summary_large_image' : 'summary' }}">
    @if (config('business.verification.google'))
        <meta name="google-site-verification" content="{{ config('business.verification.google') }}">
    @endif
    @if (config('business.verification.bing'))
        <meta name="msvalidate.01" content="{{ config('business.verification.bing') }}">
    @endif
    @if (config('business.verification.facebook'))
        <meta name="facebook-domain-verification" content="{{ config('business.verification.facebook') }}">
    @endif

    <script type="application/ld+json">{!! json_encode(\App\Support\JsonLd::business(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    <x-storefront.tracking />

    @stack('head')

    {{-- Fonturi via Bunny Fonts (GDPR-friendly). Body: Inter · Display: Plus Jakarta Sans · Logo: Space Grotesk.
         Comutare font logo: schimbă --font-logo în app.css ȘI familia de mai jos,
         ex. „bricolage-grotesque:800" sau „archivo:700". --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|plus-jakarta-sans:600,700,800|space-grotesk:700" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-full antialiased flex flex-col bg-surface text-ink">
    <x-storefront.header />

    <main class="flex-1">
        {{ $slot }}
    </main>

    <x-storefront.footer />

    <x-storefront.cookie-consent />

    @livewireScripts
</body>
</html>
