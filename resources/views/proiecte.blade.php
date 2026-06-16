@php
    $whatsapp = config('contact.whatsapp');
    $waOferta = 'https://wa.me/'.$whatsapp;
    $supplierLabel = config('company.supplier_label');

    $metaTitle = 'Proiectele noastre — mobilier urban livrat în toată țara';
    $metaDescription = 'Lucrări de mobilier urban livrate de Decor Urban pentru primării, școli și instituții — bănci, coșuri, jardiniere, locuri de joacă. Galerie în curând.';
@endphp

<x-layouts.storefront :title="$metaTitle" :description="$metaDescription">
    <section class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 py-20 text-center">
        <x-section-heading
            align="center"
            eyebrow="Proiectele noastre"
            title="Proiectele noastre — mobilier urban livrat în toată țara"
            subtitle="Lucrări realizate pentru primării, școli și instituții — bănci, coșuri, jardiniere, stații și locuri de joacă, montate în spații publice din toată țara." />

        @if (! empty($projects))
            {{-- Listă reală de proiecte (din config/company.php), dacă a fost completată. --}}
            <ul class="mt-10 grid gap-3 text-left sm:grid-cols-2">
                @foreach ($projects as $project)
                    <li class="rounded-card border border-line bg-surface-card p-5 text-sm font-medium text-ink shadow-card">{{ $project }}</li>
                @endforeach
            </ul>
        @else
            {{-- Placeholder onest: nu inventăm proiecte/referințe până avem conținut real. --}}
            <div class="mt-10 rounded-card border border-line bg-tint-sky px-6 py-12">
                <p class="text-lg font-semibold text-ink">Galerie în curând</p>
                <p class="mx-auto mt-3 max-w-xl text-base leading-relaxed text-ink-soft">
                    Pregătim o galerie cu lucrări livrate — fotografii și detalii reale de la primării,
                    școli și instituții. Până atunci, cereți-ne referințe și exemple direct, pe WhatsApp.
                </p>
                <p class="mt-4 text-xs text-ink-muted">⚠️ Pagină în lucru — proiectele reale se adaugă pe măsură ce le confirmăm. Nu afișăm referințe fabricate.</p>
            </div>
        @endif

        <div class="mt-10 flex flex-wrap justify-center gap-3">
            <x-button :href="$waOferta" variant="accent" size="lg">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163a11.867 11.867 0 0 1-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.82 11.82 0 0 1 8.413 3.488 11.82 11.82 0 0 1 3.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 0 1-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 0 0 1.51 5.26l-.999 3.648 3.488-1.607z"/></svg>
                Cere ofertă pe WhatsApp
            </x-button>
            <x-button href="{{ url('/') }}#categorii" variant="outline" size="lg">Răsfoiește catalogul</x-button>
        </div>
    </section>
</x-layouts.storefront>
