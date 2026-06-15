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

    <title>{{ $title ? $title.' — '.config('contact.brand') : config('contact.brand').' — Mobilier stradal & urban' }}</title>

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
