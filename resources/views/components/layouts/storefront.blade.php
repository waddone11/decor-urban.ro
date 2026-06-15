@props([
    'title' => null,
    'description' => 'Mobilier stradal și urban — bănci, coșuri de gunoi, jardiniere, locuri de joacă. Producător direct, livrare în toată țara.',
])

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
    <link rel="canonical" href="{{ url()->current() }}">
    <meta name="robots" content="index, follow">

    {{-- OpenGraph / social --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ config('contact.brand') }}">
    <meta property="og:title" content="{{ $fullTitle }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('images/logo.svg') }}">
    <meta name="twitter:card" content="summary">

    {{-- Date structurate: Organization (site-wide). --}}
    @php
        $ldOrganization = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => config('contact.brand'),
            'legalName' => config('company.legal_name') ?: null,
            'url' => url('/'),
            'logo' => asset('images/logo.svg'),
            'description' => config('company.supplier_label').' de mobilier urban și stradal: bănci, coșuri, jardiniere, stații, locuri de joacă.',
            'areaServed' => 'RO',
            'taxID' => config('company.cui') ?: null,
            'foundingDate' => config('company.founded') ?: null,
            'address' => config('company.address') ? array_filter([
                '@type' => 'PostalAddress',
                'streetAddress' => config('company.address'),
                'addressCountry' => 'RO',
            ]) : null,
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'contactType' => 'sales',
                'telephone' => config('contact.phone'),
                'email' => config('contact.email'),
                'areaServed' => 'RO',
                'availableLanguage' => 'Romanian',
            ],
        ]);
    @endphp
    <script type="application/ld+json">{!! json_encode($ldOrganization, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>

    @stack('head')

    {{-- Fonturi via Bunny Fonts (GDPR-friendly). Display: Plus Jakarta Sans, Body: Inter. --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|plus-jakarta-sans:600,700,800" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-full antialiased flex flex-col bg-surface text-ink">
    <x-storefront.header />

    <main class="flex-1">
        {{ $slot }}
    </main>

    <x-storefront.footer />

    @livewireScripts
</body>
</html>
