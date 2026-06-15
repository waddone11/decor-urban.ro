@php
    $whatsapp = config('contact.whatsapp');
    $waOferta = 'https://wa.me/'.$whatsapp;

    // Date companie (vezi config/company.php — unele reale, restul placeholder cu avertisment).
    $legalName = config('company.legal_name');
    $cui = config('company.cui');
    $regCom = config('company.reg_com');
    $address = config('company.address');
    $supplierLabel = config('company.supplier_label');
    $cpv = config('company.cpv');
    $seapPresent = config('company.seap_present');
    $years = (int) config('company.years');
    $projects = (int) config('company.projects');
    $references = config('company.references', []);
    $standards = config('company.standards', []);

    // SEO: titlu + descriere bogate pe keyword-uri reale.
    $metaTitle = 'Producător mobilier urban — bănci stradale, coșuri, mobilier stradal';
    $metaDescription = 'Decor Urban — '.$supplierLabel.' de mobilier urban și stradal: bănci, coșuri de gunoi, jardiniere, stații, locuri de joacă. Ofertăm pentru primării, școli și achiziții publice (SEAP/SICAP). Livrare în toată țara, cu factură.';
@endphp

<x-layouts.storefront :title="$metaTitle" :description="$metaDescription">

    {{-- JSON-LD specific homepage: FAQPage, BreadcrumbList, Product (featured). Organization e în layout. --}}
    @php
        $jsonFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

        $ldBreadcrumb = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [[
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Acasă',
                'item' => url('/'),
            ]],
        ];

        $ldFaq = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => collect($faqs)->map(fn ($f) => [
                '@type' => 'Question',
                'name' => $f['q'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
            ])->all(),
        ];

        $ldProducts = [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'itemListElement' => $featured->values()->map(fn ($p, $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'item' => array_filter([
                    '@type' => 'Product',
                    'name' => $p->name,
                    'sku' => $p->code ? ltrim($p->code, '#') : null,
                    'brand' => ['@type' => 'Brand', 'name' => config('contact.brand')],
                    'offers' => [
                        '@type' => 'Offer',
                        'availability' => 'https://schema.org/InStock',
                        'priceCurrency' => 'RON',
                        'price' => '0',
                    ],
                ]),
            ])->all(),
        ];
    @endphp
    @push('head')
        <script type="application/ld+json">{!! json_encode($ldBreadcrumb, $jsonFlags) !!}</script>
        <script type="application/ld+json">{!! json_encode($ldFaq, $jsonFlags) !!}</script>
        @if ($featured->isNotEmpty())
            <script type="application/ld+json">{!! json_encode($ldProducts, $jsonFlags) !!}</script>
        @endif
    @endpush

    {{-- 1. HERO ANIMAT (GSAP) --}}
    <section id="hero" aria-label="Mobilier urban — producător direct, fabricat în România" class="relative overflow-hidden">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-10 py-14 lg:grid-cols-2 lg:py-24">
                {{-- Text (pe mobil dedesubtul ilustrației) --}}
                <div class="order-2 lg:order-1 max-w-xl">
                    <p data-eyebrow class="hero-reveal inline-flex items-center gap-2 rounded-full bg-accent-soft px-3 py-1 text-sm font-semibold text-accent">
                        {{ ucfirst($supplierLabel) }} de mobilier urban
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
                        <x-button :href="$waOferta" variant="accent" size="lg" class="hero-reveal" data-cta>
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163a11.867 11.867 0 0 1-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.82 11.82 0 0 1 8.413 3.488 11.82 11.82 0 0 1 3.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 0 1-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 0 0 1.51 5.26l-.999 3.648 3.488-1.607z"/></svg>
                            Cere ofertă pe WhatsApp
                        </x-button>
                    </div>
                    <p data-stat class="hero-reveal mt-6 text-sm text-ink-muted">
                        {{ $stats['categories'] }} categorii · {{ $stats['products'] }} produse · livrare în toată țara
                    </p>
                </div>

                {{-- Ilustrație blueprint care se desenează (fără border/umbre — v2). --}}
                <div class="order-1 lg:order-2 relative">
                    <div class="relative aspect-square overflow-hidden rounded-card">
                        <div class="absolute inset-0 bg-gradient-to-br from-tint-sky/70 via-transparent to-tint-sand/50"></div>
                        <x-hero-illustration class="relative p-4 sm:p-8" />
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 2. TRUST STRIP — bandă teal, text inversat --}}
    <section data-scroll-reveal class="bg-accent text-white">
        <div class="mx-auto grid max-w-7xl grid-cols-2 divide-white/15 px-4 sm:px-6 lg:grid-cols-4 lg:divide-x lg:px-8">
            @php
                $trust = [
                    ['t' => ucfirst($supplierLabel), 's' => 'Fabricăm ce vindem', 'i' => 'M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21'],
                    ['t' => 'Livrare în toată țara', 's' => 'Transport oriunde în România', 'i' => 'M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12'],
                    ['t' => 'Plata ramburs', 's' => 'Plătești la livrare', 'i' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z'],
                    ['t' => 'Comandă pe WhatsApp', 's' => 'Răspuns rapid, ofertă pe loc', 'i' => 'M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z'],
                ];
            @endphp
            @foreach ($trust as $item)
                <div class="flex items-start gap-3 px-2 py-6 lg:px-6">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/15 text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['i'] }}" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-white">{{ $item['t'] }}</p>
                        <p class="text-xs text-white/70">{{ $item['s'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- 3. PENTRU INSTITUȚII (SEAP/SICAP) --}}
    <section id="institutii" data-scroll-reveal class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-20">
        <div class="overflow-hidden rounded-card border border-line bg-surface-card shadow-card">
            <div class="grid gap-0 lg:grid-cols-5">
                <div class="lg:col-span-3 p-8 sm:p-10 lg:p-12">
                    <x-section-heading
                        eyebrow="Pentru primării, școli și instituții"
                        title="Ofertăm pentru achiziții publice și licitații" />
                    <p class="mt-5 text-base leading-relaxed text-ink-soft">
                        Suntem {{ $supplierLabel }}, cu factură și livrare în toată țara. Pregătim oferte pentru
                        SEAP/SICAP, cu specificații tehnice, coduri CPV și termene clare. Fără intermediari —
                        discutați direct cu producătorul.
                    </p>

                    <ul class="mt-6 flex flex-wrap gap-2">
                        @foreach ([
                            'Factură fiscală',
                            'Livrare națională',
                            'Dimensiuni la cerere',
                            'Documentație tehnică pentru caietul de sarcini',
                        ] as $chip)
                            <li class="inline-flex items-center gap-1.5 rounded-full border border-line bg-tint-sky px-3 py-1.5 text-sm font-medium text-ink">
                                <svg class="h-4 w-4 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                                {{ $chip }}
                            </li>
                        @endforeach
                    </ul>

                    @if ($legalName || $cui || $cpv || $seapPresent)
                        <div class="mt-6 rounded-xl border border-line bg-tint-stone/60 p-4">
                            @if ($legalName)
                                <p class="text-sm font-semibold text-ink">{{ $legalName }}</p>
                            @endif
                            <div class="mt-1 flex flex-wrap items-center gap-x-5 gap-y-1 text-sm text-ink-soft">
                                @if ($cui)<span><span class="text-ink-muted">CUI</span> <span class="font-semibold text-ink">{{ $cui }}</span></span>@endif
                                @if ($regCom)<span><span class="text-ink-muted">Reg. Com.</span> <span class="font-semibold text-ink">{{ $regCom }}</span></span>@endif
                                @if ($cpv)<span><span class="text-ink-muted">Cod CPV</span> <span class="font-semibold text-ink">{{ $cpv }}</span></span>@endif
                                @if ($seapPresent)<span class="font-semibold text-accent">Prezenți în SEAP/SICAP</span>@endif
                            </div>
                            @if ($address)
                                <p class="mt-1 text-xs text-ink-muted">{{ $address }}</p>
                            @endif
                        </div>
                    @endif

                    <div class="mt-8">
                        <x-button :href="$waOferta" variant="primary" size="lg">Cere ofertă pentru licitație</x-button>
                    </div>
                </div>

                <div class="relative hidden items-center justify-center bg-accent p-12 text-white lg:col-span-2 lg:flex">
                    <svg class="h-40 w-40 opacity-90" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21" />
                    </svg>
                </div>
            </div>
        </div>
    </section>

    {{-- 4. EXPLOREAZĂ PE CATEGORII — iconițe animate (draw-on la scroll) --}}
    <section id="categorii" data-scroll-reveal class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pb-20">
        <x-section-heading
            eyebrow="Catalog"
            title="Explorează pe categorii"
            subtitle="Cele 11 categorii de mobilier urban — de la bănci și coșuri, la locuri de joacă și soluții custom." />

        <div data-cat-grid class="mt-10 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($categories as $category)
                <a href="#categorii" data-draw-on data-cat-card
                   class="group flex flex-col items-start gap-4 rounded-card border border-line bg-tint-sky p-6 transition-all duration-300 hover:-translate-y-1 hover:border-accent hover:shadow-card-hover">
                    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-surface-card text-accent shadow-sm transition-colors group-hover:bg-accent group-hover:text-white">
                        <x-category-icon :slug="$category->slug" class="h-8 w-8" />
                    </span>
                    <div>
                        <h3 class="text-base font-bold leading-tight text-ink">{{ $category->name }}</h3>
                        <p class="mt-1 text-sm text-ink-soft">{{ $category->products_count }} {{ $category->products_count === 1 ? 'produs' : 'produse' }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </section>

    {{-- 5. CUM LUCRĂM — bandă teal, timeline cu punct călător (GSAP) --}}
    <section id="proces" data-proces data-scroll-reveal class="bg-accent text-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-20">
            <div class="max-w-2xl">
                <p class="text-sm font-semibold uppercase tracking-wider text-white/70">Cum lucrăm</p>
                <h2 class="mt-2 text-3xl font-bold text-white sm:text-4xl">De la cerere la livrare, în 4 pași</h2>
                <p class="mt-3 text-base leading-relaxed text-white/80">Un proces simplu și transparent — discuți direct cu producătorul, totul confirmat în scris.</p>
            </div>

            <div class="relative mt-14">
                {{-- Track desktop (orizontal) — linia + punctul călător --}}
                <div class="pointer-events-none absolute inset-x-0 top-7 hidden lg:block" aria-hidden="true">
                    <div class="relative mx-[12.5%] h-0.5 bg-white/20">
                        <div data-line-h class="absolute inset-0 origin-left bg-white/80"></div>
                        <div data-point-h class="absolute left-0 top-1/2 h-3 w-3 -translate-x-1/2 -translate-y-1/2 rounded-full bg-white shadow-[0_0_12px_4px_rgba(255,255,255,0.7)]"></div>
                    </div>
                </div>
                {{-- Track mobil (vertical) --}}
                <div class="pointer-events-none absolute bottom-7 left-7 top-7 w-0.5 lg:hidden" aria-hidden="true">
                    <div class="relative h-full w-full bg-white/20">
                        <div data-line-v class="absolute inset-0 origin-top bg-white/80"></div>
                        <div data-point-v class="absolute left-1/2 top-0 h-3 w-3 -translate-x-1/2 -translate-y-1/2 rounded-full bg-white shadow-[0_0_12px_4px_rgba(255,255,255,0.7)]"></div>
                    </div>
                </div>

                {{-- Pași --}}
                <div class="relative grid gap-10 lg:grid-cols-4">
                    @foreach ([
                        ['n' => '1', 'icon' => 'cerere', 't' => 'Ceri ofertă', 'd' => 'Alegi din catalog sau ne spui ce ai nevoie — pe WhatsApp, email sau formular.'],
                        ['n' => '2', 'icon' => 'confirmare', 't' => 'Confirmăm', 'd' => 'Dimensiuni, finisaje, preț și termen — totul în scris, fără surprize.'],
                        ['n' => '3', 'icon' => 'productie', 't' => 'Producem', 'd' => 'În atelierul propriu, din materiale gândite pentru exterior.'],
                        ['n' => '4', 'icon' => 'livrare', 't' => 'Livrăm', 'd' => 'În toată țara, cu factură.'],
                    ] as $step)
                        <div data-step class="flex items-start gap-4 lg:flex-col lg:items-center lg:gap-0 lg:text-center">
                            <div class="relative shrink-0">
                                <span data-step-pulse class="pointer-events-none absolute inset-0 rounded-full ring-2 ring-white/70 opacity-0"></span>
                                <div data-step-circle class="relative flex h-14 w-14 items-center justify-center rounded-full border-2 border-white/70 bg-accent text-white">
                                    <x-dynamic-component :component="'icons.step.'.$step['icon']" class="h-7 w-7" />
                                </div>
                            </div>
                            <div data-step-label class="lg:mt-5">
                                <div class="flex items-center gap-2 lg:justify-center">
                                    <span class="text-xs font-bold text-white/60">{{ $step['n'] }}</span>
                                    <h3 class="text-lg font-bold text-white">{{ $step['t'] }}</h3>
                                </div>
                                <p class="mt-1 text-sm leading-relaxed text-white/75">{{ $step['d'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- 6. CALITATE & MATERIALE — iconițe material animate + sweep de finisaj --}}
    <section id="calitate" data-scroll-reveal data-quality class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-20">
        <div class="grid items-center gap-12 lg:grid-cols-2">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-accent">Calitate &amp; materiale</p>
                <h2 class="mt-2 text-3xl font-bold text-ink sm:text-4xl">
                    Făcut să stea
                    <span class="relative inline-block">afară<span data-underline class="absolute -bottom-1 left-0 h-[3px] w-full origin-left rounded-full bg-accent"></span></span>,
                    ani la rând
                </h2>
                <p class="mt-5 text-base leading-relaxed text-ink-soft">
                    Lemn tratat pentru exterior, metal vopsit electrostatic și inox — alegem materiale
                    rezistente la intemperii, uz intens și vandalism. Mobilier gândit pentru spații publice,
                    nu pentru un sezon.
                </p>
                <ul class="mt-6 flex flex-wrap gap-2">
                    <li class="inline-flex items-center gap-1.5 rounded-full border border-line bg-tint-sky px-3 py-1.5 text-sm font-medium text-ink">Rezistent la exterior</li>
                    <li class="inline-flex items-center gap-1.5 rounded-full border border-line bg-tint-sky px-3 py-1.5 text-sm font-medium text-ink">Finisaje durabile</li>
                    @foreach ($standards as $std)
                        <li class="inline-flex items-center gap-1.5 rounded-full border border-line bg-tint-sky px-3 py-1.5 text-sm font-medium text-ink">{{ $std }}</li>
                    @endforeach
                </ul>
            </div>

            {{-- Rândul de materiale + sweep de lumină (o singură trecere la intrarea în viewport) --}}
            <div data-quality-row class="relative grid grid-cols-3 gap-4 overflow-hidden rounded-card">
                @foreach ([
                    ['icon' => 'lemn', 't' => 'Lemn tratat', 's' => 'pentru exterior', 'bg' => 'bg-tint-sand'],
                    ['icon' => 'metal', 't' => 'Metal vopsit', 's' => 'electrostatic', 'bg' => 'bg-tint-sky'],
                    ['icon' => 'inox', 't' => 'Inox', 's' => 'anti-coroziune', 'bg' => 'bg-tint-stone'],
                ] as $m)
                    <div class="rounded-card border border-line {{ $m['bg'] }} p-6 text-center">
                        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-surface-card text-accent shadow-sm">
                            <x-dynamic-component :component="'icons.material.'.$m['icon']" class="h-7 w-7" />
                        </span>
                        <p class="mt-3 text-base font-bold text-ink">{{ $m['t'] }}</p>
                        <p class="mt-1 text-xs text-ink-soft">{{ $m['s'] }}</p>
                    </div>
                @endforeach

                {{-- Sweep: gradient de lumină care trece o dată peste rând --}}
                <div data-quality-sweep aria-hidden="true"
                     class="pointer-events-none absolute inset-y-0 -left-1/3 w-1/3 -skew-x-12 bg-gradient-to-r from-transparent via-white/55 to-transparent opacity-0"></div>
            </div>
        </div>
    </section>

    {{-- 7. PRODUCEM LA COMANDA TA (custom) --}}
    <section id="custom" data-scroll-reveal class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pb-20">
        <div class="overflow-hidden rounded-card bg-primary px-8 py-12 text-white sm:px-12 lg:px-16 lg:py-16">
            <div class="grid items-center gap-8 lg:grid-cols-3">
                <div class="lg:col-span-2">
                    <p class="text-sm font-semibold uppercase tracking-wider text-white/60">Custom</p>
                    <h2 class="mt-2 text-3xl font-bold sm:text-4xl">Producem la comanda ta</h2>
                    <p class="mt-4 max-w-2xl text-base leading-relaxed text-white/80">
                        Dimensiuni custom, culori RAL la alegere și personalizare cu stema localității sau
                        logo. Spune-ne ce ai nevoie — proiectăm și fabricăm exact pentru spațiul tău.
                    </p>
                </div>
                <div class="lg:justify-self-end">
                    <x-button :href="$waOferta" variant="accent" size="lg">Cere ofertă custom</x-button>
                </div>
            </div>
        </div>
    </section>

    {{-- 8. PRODUSE FEATURED --}}
    <section id="catalog" data-scroll-reveal class="bg-surface-card border-y border-line">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-20">
            <div class="flex items-end justify-between gap-4">
                <x-section-heading
                    eyebrow="Recomandate"
                    title="Produse din catalog"
                    subtitle="O selecție din gama noastră. Toate disponibile la comandă, cu ofertă personalizată." />
                <x-button href="#categorii" variant="ghost" size="sm" class="hidden shrink-0 sm:inline-flex">Vezi tot catalogul →</x-button>
            </div>

            <div class="mt-10 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($featured as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        </div>
    </section>

    {{-- 9. SOCIAL PROOF — contoare count-up --}}
    <section id="cifre" data-scroll-reveal class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-20">
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @php
                $counters = array_values(array_filter([
                    ['v' => $stats['products'], 'suffix' => '+', 'l' => 'produse în catalog'],
                    ['v' => $stats['categories'], 'suffix' => '', 'l' => 'categorii'],
                    $years > 0 ? ['v' => $years, 'suffix' => '+', 'l' => 'ani de experiență'] : null,
                    $projects > 0 ? ['v' => $projects, 'suffix' => '+', 'l' => 'proiecte livrate'] : null,
                ]));
            @endphp
            @foreach ($counters as $c)
                <div class="rounded-card border border-line bg-surface-card p-6 text-center shadow-card">
                    <p class="text-4xl font-extrabold text-accent sm:text-5xl">
                        <span data-countup data-count-to="{{ $c['v'] }}">{{ $c['v'] }}</span>{{ $c['suffix'] }}
                    </p>
                    <p class="mt-2 text-sm text-ink-soft">{{ $c['l'] }}</p>
                </div>
            @endforeach
        </div>

        <p class="mt-8 text-center text-base text-ink-soft">
            Clienți: primării, școli și firme din toată țara.
            @if (! empty($references))
                <span class="mt-3 block text-sm text-ink-muted">{{ implode(' · ', $references) }}</span>
            @endif
        </p>
    </section>

    {{-- 10. FAQ — acordeon (schema FAQPage în <head>) --}}
    <section id="faq" data-scroll-reveal class="border-t border-line bg-surface-card">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 py-20">
            <x-section-heading
                align="center"
                eyebrow="Întrebări frecvente"
                title="Tot ce trebuie să știi înainte de comandă" />

            <div class="mt-10 divide-y divide-line rounded-card border border-line bg-surface" x-data="{ active: 0 }">
                @foreach ($faqs as $i => $faq)
                    <div>
                        <h3>
                            <button type="button"
                                    @click="active === {{ $i }} ? active = null : active = {{ $i }}"
                                    :aria-expanded="active === {{ $i }} ? 'true' : 'false'"
                                    aria-controls="faq-panel-{{ $i }}"
                                    class="flex w-full items-center justify-between gap-4 px-5 py-5 text-left">
                                <span class="text-base font-semibold text-ink">{{ $faq['q'] }}</span>
                                <svg class="h-5 w-5 shrink-0 text-accent transition-transform duration-200" :class="active === {{ $i }} && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>
                        </h3>
                        <div id="faq-panel-{{ $i }}" x-show="active === {{ $i }}" x-collapse x-cloak>
                            <p class="px-5 pb-5 text-sm leading-relaxed text-ink-soft">{{ $faq['a'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 11. CTA FINAL --}}
    <section id="cta" data-scroll-reveal class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-20">
        <div class="rounded-card border border-line bg-tint-sky px-8 py-14 text-center sm:px-12">
            <h2 class="text-3xl font-bold text-ink sm:text-4xl">Pregătit să ceri o ofertă?</h2>
            <p class="mx-auto mt-4 max-w-xl text-base text-ink-soft">
                {{ ucfirst($supplierLabel) }}, cu factură și livrare în toată țara. Răspundem rapid și pregătim
                oferta pe specificațiile tale — inclusiv pentru SEAP/SICAP.
            </p>
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <x-button :href="$waOferta" variant="accent" size="lg">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163a11.867 11.867 0 0 1-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.82 11.82 0 0 1 8.413 3.488 11.82 11.82 0 0 1 3.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 0 1-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 0 0 1.51 5.26l-.999 3.648 3.488-1.607z"/></svg>
                    Cere ofertă pe WhatsApp
                </x-button>
                <x-button href="#categorii" variant="outline" size="lg">Răsfoiește catalogul</x-button>
            </div>
        </div>
    </section>
</x-layouts.storefront>
