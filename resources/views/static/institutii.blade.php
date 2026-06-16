@php
    $brand = config('contact.brand');
    $supplierLabel = config('company.supplier_label');
    $whatsapp = config('contact.whatsapp');
    $waOferta = 'https://wa.me/'.$whatsapp.'?text='.rawurlencode('Bună ziua, doresc o ofertă pentru o achiziție publică / licitație.');
    $legalName = config('company.legal_name');
    $cui = config('company.cui');
    $regCom = config('company.reg_com');
    $address = config('company.address');
    $cpv = config('company.cpv');
    $seapPresent = config('company.seap_present');
    $standards = config('company.standards', []);

    $metaTitle = 'Mobilier urban pentru primării și instituții';
    $metaDescription = $brand.' — '.$supplierLabel.' de mobilier urban și stradal pentru primării, școli și instituții. Oferte pentru licitații SEAP/SICAP, coduri CPV, documentație tehnică, livrare în toată țara.';

    $institutiiFaqs = [
        ['q' => 'Cum cumpărăm prin SEAP/SICAP?', 'a' => 'Pregătim o ofertă cu specificații tehnice și coduri CPV și trimitem documentația pentru caietul de sarcini. Ne contactați și vă ghidăm prin proces.'],
        ['q' => 'Emiteți factură și oferiți garanție?', 'a' => 'Da — factură fiscală și garanție conform legislației, cu livrare în toată țara.'],
        ['q' => 'Faceți dimensiuni și culori personalizate?', 'a' => 'Da, producem la comandă: dimensiuni custom și culori RAL la alegere, plus personalizare cu stema localității sau logo.'],
        ['q' => 'Care e termenul de livrare?', 'a' => 'Variază după produs și cantitate; îl confirmăm în scris în ofertă.'],
    ];
@endphp

<x-layouts.storefront :title="$metaTitle" :description="$metaDescription">
    <x-seo.jsonld :data="\App\Support\JsonLd::breadcrumb([
        ['name' => 'Acasă', 'url' => url('/')],
        ['name' => 'Instituții', 'url' => route('institutii')],
    ])" />

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <x-storefront.breadcrumb :items="[
            ['label' => 'Acasă', 'url' => url('/')],
            ['label' => 'Instituții'],
        ]" class="mb-6" />

        <header class="max-w-3xl">
            <p class="inline-flex items-center rounded-full bg-accent-soft px-3 py-1 text-sm font-semibold text-accent">Pentru primării, școli și instituții</p>
            <h1 class="mt-4 text-3xl font-bold text-ink sm:text-4xl">Mobilier urban pentru primării, școli și instituții</h1>
            <p class="mt-4 text-lg leading-relaxed text-ink-soft">
                Suntem {{ $supplierLabel }} de mobilier urban și stradal, cu factură și livrare în toată țara.
                Lucrați direct cu producătorul — fără intermediari, fără adaos suplimentar.
            </p>
        </header>

        <div class="mt-10 grid gap-6 lg:grid-cols-3">
            {{-- Achiziții publice / SEAP --}}
            <section class="rounded-card border border-line bg-surface-card p-6 shadow-card lg:col-span-2">
                <h2 class="text-xl font-bold text-ink">Achiziții publice și licitații (SEAP/SICAP)</h2>
                <p class="mt-3 leading-relaxed text-ink-soft">
                    Pregătim oferte pentru SEAP/SICAP cu specificații tehnice, termene clare și documentație
                    pentru caietul de sarcini. Vă ajutăm să identificați produsele potrivite și să pregătiți
                    dosarul de achiziție.
                </p>
                <ul class="mt-5 flex flex-wrap gap-2">
                    @foreach (['Factură fiscală', 'Livrare națională', 'Dimensiuni la cerere', 'Documentație tehnică pentru caietul de sarcini', 'Garanție'] as $chip)
                        <li class="inline-flex items-center gap-1.5 rounded-full border border-line bg-tint-sky px-3 py-1.5 text-sm font-medium text-ink">
                            <svg class="h-4 w-4 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            {{ $chip }}
                        </li>
                    @endforeach
                </ul>
                @if ($cpv || $seapPresent)
                    <div class="mt-5 flex flex-wrap items-center gap-x-5 gap-y-1 text-sm">
                        @if ($cpv)<span><span class="text-ink-muted">Cod CPV</span> <span class="font-semibold text-ink">{{ $cpv }}</span></span>@endif
                        @if ($seapPresent)<span class="font-semibold text-accent">Prezenți în SEAP/SICAP</span>@endif
                    </div>
                @endif
            </section>

            {{-- De ce direct de la producător --}}
            <section class="rounded-card border border-line bg-surface-card p-6 shadow-card">
                <h2 class="text-xl font-bold text-ink">Direct de la producător</h2>
                <ul class="mt-4 space-y-3 text-sm text-ink-soft">
                    @foreach ([
                        'Preț corect, fără adaos de intermediar',
                        'Dimensiuni custom și culori RAL la alegere',
                        'Personalizare cu stema localității sau logo',
                        'Consultanță tehnică pentru proiect',
                        'Materiale gândite pentru exterior și uz intens',
                    ] as $item)
                        <li class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            {{ $item }}
                        </li>
                    @endforeach
                </ul>
                @if (! empty($standards))
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($standards as $std)
                            <span class="inline-flex items-center rounded-full border border-line bg-tint-sky px-3 py-1 text-xs font-medium text-ink">{{ $std }}</span>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>

        {{-- Identitate legală (doar dacă setată) --}}
        @if ($legalName || $cui || $regCom || $address)
            <div class="mt-6 rounded-card border border-line bg-tint-stone/60 p-5">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-ink-muted">Date firmă</h2>
                <div class="mt-2 flex flex-wrap items-center gap-x-6 gap-y-1 text-sm text-ink-soft">
                    @if ($legalName)<span class="font-semibold text-ink">{{ $legalName }}</span>@endif
                    @if ($cui)<span><span class="text-ink-muted">CUI</span> <span class="font-semibold text-ink">{{ $cui }}</span></span>@endif
                    @if ($regCom)<span><span class="text-ink-muted">Reg. Com.</span> <span class="font-semibold text-ink">{{ $regCom }}</span></span>@endif
                </div>
                @if ($address)<p class="mt-1 text-xs text-ink-muted">{{ $address }}</p>@endif
            </div>
        @endif

        {{-- CTA --}}
        <div class="mt-8 flex flex-wrap items-center gap-3">
            <x-button :href="$waOferta" variant="accent" size="lg" target="_blank" rel="noopener">Cere ofertă pentru licitație</x-button>
            <x-button :href="route('contact')" variant="outline" size="lg">Contact</x-button>
            <x-button :href="route('proiecte')" variant="ghost" size="lg">Vezi lucrări livrate →</x-button>
        </div>

        {{-- FAQ instituții --}}
        <section class="mt-16 max-w-3xl">
            <h2 class="text-xl font-bold text-ink">Întrebări frecvente — instituții</h2>
            <div class="mt-6 divide-y divide-line rounded-card border border-line bg-surface" x-data="{ active: null }">
                @foreach ($institutiiFaqs as $i => $faq)
                    <div>
                        <h3>
                            <button type="button" @click="active === {{ $i }} ? active = null : active = {{ $i }}"
                                    :aria-expanded="active === {{ $i }} ? 'true' : 'false'"
                                    class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left">
                                <span class="text-base font-semibold text-ink">{{ $faq['q'] }}</span>
                                <svg class="h-5 w-5 shrink-0 text-accent transition-transform duration-200" :class="active === {{ $i }} && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                            </button>
                        </h3>
                        <div x-show="active === {{ $i }}" x-collapse x-cloak>
                            <p class="px-5 pb-5 text-sm leading-relaxed text-ink-soft">{{ $faq['a'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</x-layouts.storefront>
