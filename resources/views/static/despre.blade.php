@php
    $supplierLabel = config('company.supplier_label');
    $brand = config('contact.brand');
    $legalName = config('company.legal_name');
    $cui = config('company.cui');
    $regCom = config('company.reg_com');
    $address = config('company.address');
    $companyPlaceholder = config('company.is_placeholder');
    $whatsapp = config('contact.whatsapp');

    $metaTitle = 'Despre noi';
    $metaDescription = $brand.' — '.$supplierLabel.' de mobilier urban și stradal pentru primării, școli și firme. Producție proprie, materiale de exterior, dimensiuni la cerere, livrare în toată țara.';
@endphp

<x-layouts.storefront :title="$metaTitle" :description="$metaDescription">
    <x-seo.jsonld :data="\App\Support\JsonLd::breadcrumb([
        ['name' => 'Acasă', 'url' => url('/')],
        ['name' => 'Despre noi', 'url' => route('despre')],
    ])" />

    <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
        <x-storefront.breadcrumb :items="[
            ['label' => 'Acasă', 'url' => url('/')],
            ['label' => 'Despre noi'],
        ]" class="mb-6" />

        <h1 class="text-3xl font-bold text-ink sm:text-4xl">Despre noi</h1>
        <p class="mt-4 text-lg leading-relaxed text-ink-soft">
            {{ ucfirst($brand) }} este {{ $supplierLabel }} de mobilier urban și stradal. Proiectăm și
            fabricăm bănci, coșuri de gunoi, jardiniere, stații de autobuz, locuri de joacă și soluții
            custom pentru spații publice și private.
        </p>

        <div class="mt-10 space-y-8 text-ink-soft leading-relaxed">
            <section>
                <h2 class="text-xl font-bold text-ink">Pentru cine lucrăm</h2>
                <p class="mt-3">
                    Livrăm pentru primării și instituții publice, școli și grădinițe, firme și dezvoltatori,
                    precum și pentru clienți privați. Pregătim oferte pentru achiziții publice (SEAP/SICAP),
                    cu specificații tehnice și documentație pentru caietul de sarcini.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-bold text-ink">Producție proprie</h2>
                <p class="mt-3">
                    Fabricăm ce vindem, în atelier propriu — fără intermediari. Folosim materiale gândite
                    pentru exterior: lemn tratat, metal vopsit electrostatic și inox, rezistente la
                    intemperii, uz intens și vandalism.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-bold text-ink">Dimensiuni și finisaje la cerere</h2>
                <p class="mt-3">
                    Producem la comandă: dimensiuni custom, culori RAL la alegere și personalizare cu stema
                    localității sau logo. Confirmăm totul în scris — preț, finisaje și termen — înainte de
                    producție. Livrăm în toată țara, cu factură.
                </p>
            </section>
        </div>

        {{-- Identitate legală (din config; avertisment cât timp e provizorie). --}}
        @if ($legalName || $cui || $regCom || $address)
            <div class="mt-10 rounded-card border border-line bg-tint-stone/60 p-5">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-ink-muted">Date firmă</h2>
                <dl class="mt-3 space-y-1 text-sm text-ink-soft">
                    @if ($legalName)<div class="font-semibold text-ink">{{ $legalName }}</div>@endif
                    @if ($cui)<div>CUI {{ $cui }}@if ($regCom) · Reg. Com. {{ $regCom }}@endif</div>@endif
                    @if ($address)<div>{{ $address }}</div>@endif
                </dl>
            </div>
        @endif

        @if ($companyPlaceholder)
            <p class="mt-4 text-xs text-ink-muted">⚠️ Unele date despre firmă sunt provizorii — se completează în <code>config/company.php</code>.</p>
        @endif

        <div class="mt-10 flex flex-wrap gap-3">
            <x-button :href="route('contact')" variant="primary" size="lg">Contactează-ne</x-button>
            <x-button :href="'https://wa.me/'.$whatsapp" variant="accent" size="lg">Cere ofertă pe WhatsApp</x-button>
        </div>
    </div>
</x-layouts.storefront>
