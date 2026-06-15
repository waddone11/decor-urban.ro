@php
    $whatsapp = config('contact.whatsapp');
@endphp

<x-layouts.storefront>
    {{-- 1. HERO ANIMAT (GSAP) --}}
    <section id="hero" aria-label="Mobilier urban — producător direct, fabricat în România" class="relative overflow-hidden">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-10 py-14 lg:grid-cols-2 lg:py-24">
                {{-- Text (pe mobil dedesubtul ilustrației) --}}
                <div class="order-2 lg:order-1 max-w-xl">
                    <p data-eyebrow class="hero-reveal inline-flex items-center gap-2 rounded-full bg-accent-soft px-3 py-1 text-sm font-semibold text-accent">
                        Producător direct de mobilier urban
                    </p>
                    <h1 class="mt-5 text-4xl font-extrabold leading-[1.08] text-ink sm:text-5xl lg:text-6xl">
                        <span data-word class="hero-reveal inline-block">Mobilier</span>
                        <span data-word class="hero-reveal inline-block">urban</span>
                        <span data-word class="hero-reveal inline-block">care</span>
                        <span data-word class="hero-reveal inline-block text-accent">durează.</span>
                    </h1>
                    <p data-lead class="hero-reveal mt-5 text-lg leading-relaxed text-ink-soft">
                        Bănci, coșuri, jardiniere, stații și locuri de joacă — proiectate și fabricate
                        de noi, pentru primării, școli și spații private.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <x-button href="#categorii" variant="primary" size="lg" class="hero-reveal" data-cta>Vezi catalogul</x-button>
                        <x-button :href="'https://wa.me/'.$whatsapp" variant="accent" size="lg" class="hero-reveal" data-cta>
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163a11.867 11.867 0 0 1-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.82 11.82 0 0 1 8.413 3.488 11.82 11.82 0 0 1 3.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 0 1-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 0 0 1.51 5.26l-.999 3.648 3.488-1.607z"/></svg>
                            Cere ofertă pe WhatsApp
                        </x-button>
                    </div>
                    <p data-stat class="hero-reveal mt-6 text-sm text-ink-muted">
                        {{ $stats['categories'] }} categorii · {{ $stats['products'] }} produse · livrare în toată țara
                    </p>
                </div>

                {{-- Ilustrație blueprint care se desenează --}}
                <div class="order-1 lg:order-2 relative">
                    <div class="relative aspect-square overflow-hidden rounded-card border border-line bg-surface-card shadow-card-hover">
                        <div class="absolute inset-0 bg-gradient-to-br from-tint-sky/60 via-transparent to-tint-sand/50"></div>
                        <x-hero-illustration class="relative p-4 sm:p-8" />
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 2. TRUST STRIP --}}
    <section data-scroll-reveal class="border-y border-line bg-surface-card">
        <div class="mx-auto grid max-w-7xl grid-cols-2 gap-px px-4 sm:px-6 lg:grid-cols-4 lg:px-8">
            @php
                $trust = [
                    ['t' => 'Producător direct', 's' => 'Fabricăm ce vindem', 'i' => 'M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21'],
                    ['t' => 'Livrare în toată țara', 's' => 'Transport oriunde în România', 'i' => 'M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12'],
                    ['t' => 'Plata ramburs', 's' => 'Plătești la livrare', 'i' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z'],
                    ['t' => 'Comandă pe WhatsApp', 's' => 'Răspuns rapid, ofertă pe loc', 'i' => 'M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z'],
                ];
            @endphp
            @foreach ($trust as $item)
                <div class="flex items-start gap-3 bg-surface-card px-2 py-6 lg:px-6">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-accent-soft text-accent">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['i'] }}" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-ink">{{ $item['t'] }}</p>
                        <p class="text-xs text-ink-muted">{{ $item['s'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- 3. CATEGORII FEATURED --}}
    <section id="categorii" data-scroll-reveal class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-20">
        <x-section-heading
            eyebrow="Catalog"
            title="Explorează pe categorii"
            subtitle="Cele 11 categorii de mobilier urban — de la bănci și coșuri, la locuri de joacă și soluții custom." />

        <div class="mt-10 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($categories as $category)
                <x-category-card :category="$category" />
            @endforeach
        </div>
    </section>

    {{-- 4. PRODUSE FEATURED --}}
    <section data-scroll-reveal class="bg-surface-card border-y border-line">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-20">
            <div class="flex items-end justify-between gap-4">
                <x-section-heading
                    eyebrow="Recomandate"
                    title="Produse din catalog"
                    subtitle="O selecție din gama noastră. Toate disponibile la comandă, cu ofertă personalizată." />
                <x-button href="#" variant="ghost" size="sm" class="hidden shrink-0 sm:inline-flex">Vezi tot catalogul →</x-button>
            </div>

            <div class="mt-10 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($featured as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        </div>
    </section>

    {{-- 5. DESPRE / DE CE NOI --}}
    <section data-scroll-reveal class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-20">
        <div class="grid items-center gap-12 lg:grid-cols-2">
            <div>
                <x-section-heading
                    eyebrow="De ce Decor Urban"
                    title="Calitate de producător, pentru spații publice" />
                <p class="mt-5 text-base leading-relaxed text-ink-soft">
                    Lucrăm direct cu primării, școli, parcuri și firme. Fără intermediari: proiectăm,
                    fabricăm și livrăm mobilier urban robust, gândit pentru utilizare intensă în exterior.
                </p>
                <ul class="mt-6 space-y-3">
                    @foreach ([
                        'Producție proprie, materiale rezistente la exterior',
                        'Soluții custom — dimensiuni și finisaje la cerere',
                        'Experiență cu instituții publice și proiecte mari',
                    ] as $point)
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-accent text-white">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </span>
                            <span class="text-base text-ink">{{ $point }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-8">
                    <x-button :href="'https://wa.me/'.$whatsapp" variant="primary" size="lg">Cere o ofertă</x-button>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div class="rounded-card border border-line bg-tint-sand p-6 text-center">
                    <p class="text-3xl font-bold text-ink">127+</p>
                    <p class="mt-1 text-sm text-ink-soft">produse</p>
                </div>
                <div class="rounded-card border border-line bg-tint-sky p-6 text-center">
                    <p class="text-3xl font-bold text-ink">11</p>
                    <p class="mt-1 text-sm text-ink-soft">categorii</p>
                </div>
                <div class="rounded-card border border-line bg-tint-stone p-6 text-center">
                    <p class="text-3xl font-bold text-ink">100%</p>
                    <p class="mt-1 text-sm text-ink-soft">producător direct</p>
                </div>
            </div>
        </div>
    </section>
</x-layouts.storefront>
