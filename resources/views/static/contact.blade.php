@php
    $brand = config('contact.brand');
    $phone = config('contact.phone');
    $email = config('contact.email');
    $whatsapp = config('contact.whatsapp');
    $city = config('contact.city');
    $address = config('company.address');
    $legalName = config('company.legal_name');
    $isPlaceholder = config('contact.is_placeholder');

    $metaTitle = 'Contact';
    $metaDescription = 'Contactează '.$brand.' — telefon, WhatsApp și email. '.config('company.supplier_label').' de mobilier urban și stradal. Cere o ofertă pentru proiectul tău.';
@endphp

<x-layouts.storefront :title="$metaTitle" :description="$metaDescription">
    <x-seo.jsonld :data="\App\Support\JsonLd::breadcrumb([
        ['name' => 'Acasă', 'url' => url('/')],
        ['name' => 'Contact', 'url' => route('contact')],
    ])" />

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <x-storefront.breadcrumb :items="[
            ['label' => 'Acasă', 'url' => url('/')],
            ['label' => 'Contact'],
        ]" class="mb-6" />

        <h1 class="text-3xl font-bold text-ink sm:text-4xl">Contact</h1>
        <p class="mt-3 max-w-2xl text-lg text-ink-soft">
            Scrie-ne sau sună-ne — pregătim oferta pe specificațiile tale, inclusiv pentru SEAP/SICAP.
        </p>

        <div class="mt-10 grid gap-10 lg:grid-cols-2">
            {{-- Coordonate --}}
            <div>
                <h2 class="text-lg font-semibold text-ink">Date de contact</h2>
                <dl class="mt-4 space-y-4 text-ink-soft">
                    <div class="flex items-start gap-3">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" /></svg>
                        <div>
                            <dt class="text-sm text-ink-muted">Telefon</dt>
                            <dd><a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="font-semibold text-ink hover:text-accent transition-colors">{{ $phone }}</a></dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                        <div>
                            <dt class="text-sm text-ink-muted">Email</dt>
                            <dd><a href="mailto:{{ $email }}" class="font-semibold text-ink hover:text-accent transition-colors">{{ $email }}</a></dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-accent" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163a11.867 11.867 0 0 1-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.82 11.82 0 0 1 8.413 3.488 11.82 11.82 0 0 1 3.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 0 1-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 0 0 1.51 5.26l-.999 3.648 3.488-1.607z"/></svg>
                        <div>
                            <dt class="text-sm text-ink-muted">WhatsApp</dt>
                            <dd><a href="https://wa.me/{{ $whatsapp }}" class="font-semibold text-accent hover:text-accent-hover transition-colors">Scrie-ne pe WhatsApp</a></dd>
                        </div>
                    </div>
                    @if ($address)
                        <div class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                            <div>
                                <dt class="text-sm text-ink-muted">Sediu</dt>
                                <dd class="font-semibold text-ink">{{ $address }}</dd>
                            </div>
                        </div>
                    @endif
                </dl>

                @if ($isPlaceholder)
                    <p class="mt-6 text-xs text-ink-muted">⚠️ Date de contact provizorii — se confirmă în <code>config/contact.php</code>.</p>
                @endif

                @if ($address)
                    <div class="mt-6 overflow-hidden rounded-card border border-line">
                        <iframe
                            title="Hartă {{ $legalName ?: $brand }}"
                            src="https://maps.google.com/maps?q={{ urlencode($address) }}&output=embed"
                            class="aspect-video w-full" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                @endif
            </div>

            {{-- Formular de contact (Livewire — Partea 4). --}}
            <div>
                <h2 class="text-lg font-semibold text-ink">Trimite-ne un mesaj</h2>
                <livewire:contact-form />
            </div>
        </div>
    </div>
</x-layouts.storefront>
