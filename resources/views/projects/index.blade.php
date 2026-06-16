@php
    $whatsapp = config('contact.whatsapp');
    $metaTitle = 'Proiecte — lucrări de mobilier urban livrate';
    $metaDescription = 'Lucrări de mobilier urban și stradal livrate de '.config('contact.brand').' pentru primării, școli și instituții.';
@endphp

<x-layouts.storefront :title="$metaTitle" :description="$metaDescription">
    <x-seo.jsonld :data="\App\Support\JsonLd::breadcrumb([
        ['name' => 'Acasă', 'url' => url('/')],
        ['name' => 'Proiecte', 'url' => route('proiecte')],
    ])" />

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <x-storefront.breadcrumb :items="[
            ['label' => 'Acasă', 'url' => url('/')],
            ['label' => 'Proiecte'],
        ]" class="mb-6" />

        <header class="max-w-2xl">
            <h1 class="text-3xl font-bold text-ink sm:text-4xl">Proiectele noastre</h1>
            <p class="mt-3 text-lg text-ink-soft">Lucrări de mobilier urban livrate pentru primării, școli și instituții.</p>
        </header>

        @if ($projects->isEmpty())
            <div class="mt-10 rounded-card border border-dashed border-line py-16 text-center">
                <p class="text-lg font-semibold text-ink">Adăugăm în curând lucrările livrate</p>
                <p class="mx-auto mt-2 max-w-xl text-sm text-ink-soft">
                    Pregătim o galerie cu proiecte reale — fotografii și detalii de la primării, școli
                    și instituții. Până atunci, cere-ne referințe direct.
                </p>
                <div class="mt-6 flex flex-wrap justify-center gap-3">
                    <x-button :href="'https://wa.me/'.$whatsapp" variant="accent" size="lg" target="_blank" rel="noopener">Cere ofertă pe WhatsApp</x-button>
                    <x-button :href="route('catalog')" variant="outline" size="lg">Vezi catalogul</x-button>
                </div>
            </div>
        @else
            <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($projects as $project)
                    @php $img = $project->primaryImage(); @endphp
                    <a href="{{ route('project.show', $project->slug) }}"
                       class="group flex flex-col overflow-hidden rounded-card border border-line bg-surface-card shadow-card transition-all duration-300 hover:shadow-card-hover hover:-translate-y-1">
                        <div class="relative aspect-[4/3] overflow-hidden bg-tint-stone">
                            @if ($img)
                                <img src="{{ $img->url() }}" alt="{{ $img->alt ?: $project->title }}" loading="lazy"
                                     class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                            @endif
                        </div>
                        <div class="flex flex-1 flex-col p-5">
                            <h2 class="text-lg font-bold leading-snug text-ink group-hover:text-accent transition-colors">{{ $project->title }}</h2>
                            @if ($project->location)
                                <p class="mt-1 text-sm text-ink-muted">{{ $project->location }}</p>
                            @endif
                            @if ($project->summary)
                                <p class="mt-2 text-sm text-ink-soft line-clamp-2">{{ $project->summary }}</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.storefront>
