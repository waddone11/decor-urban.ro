@php
    $images = $project->images;
    $primary = $project->primaryImage();
    $whatsapp = config('contact.whatsapp');
    $metaTitle = $project->title;
    $metaDescription = $project->summary
        ?: ('Lucrare de mobilier urban livrată de '.config('contact.brand').($project->location ? ' — '.$project->location : '').'.');
    $ogImage = $primary?->url();
@endphp

<x-layouts.storefront :title="$metaTitle" :description="$metaDescription" :og-image="$ogImage" og-type="article">
    <x-seo.jsonld :data="$jsonLd" />

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <x-storefront.breadcrumb :items="[
            ['label' => 'Acasă', 'url' => url('/')],
            ['label' => 'Proiecte', 'url' => route('proiecte')],
            ['label' => $project->title],
        ]" class="mb-6" />

        <header>
            <h1 class="text-3xl font-bold text-ink sm:text-4xl">{{ $project->title }}</h1>
            <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-ink-muted">
                @if ($project->location)<span>📍 {{ $project->location }}</span>@endif
                @if ($project->clientTypeLabel())<span class="inline-flex items-center rounded-full border border-line bg-tint-sky px-2.5 py-0.5 text-xs font-medium text-ink">{{ $project->clientTypeLabel() }}</span>@endif
                @if ($project->year)<span>{{ $project->year }}</span>@endif
            </div>
        </header>

        @if ($images->isNotEmpty())
            <div class="mt-6" x-data="{ active: 0 }">
                <div class="overflow-hidden rounded-card border border-line bg-tint-stone">
                    @foreach ($images as $i => $image)
                        <img x-show="active === {{ $i }}" src="{{ $image->thumbUrl(800) }}" alt="{{ $image->alt ?: $project->title }}"
                             width="800" height="600"
                             @if($i === 0) fetchpriority="high" @else loading="lazy" @endif
                             class="aspect-[4/3] w-full object-cover">
                    @endforeach
                </div>
                @if ($images->count() > 1)
                    <div class="mt-3 grid grid-cols-5 gap-2 sm:grid-cols-8">
                        @foreach ($images as $i => $image)
                            <button type="button" @click="active = {{ $i }}"
                                    :class="active === {{ $i }} ? 'ring-2 ring-accent' : 'ring-1 ring-line hover:ring-ink-muted'"
                                    class="overflow-hidden rounded-lg motion-safe:transition" aria-label="Imaginea {{ $i + 1 }}">
                                <img src="{{ $image->thumbUrl(400) }}" alt="" loading="lazy" width="400" height="400" class="aspect-square w-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        @if ($project->body)
            <div class="mt-8 max-w-3xl text-ink-soft leading-relaxed [&_h2]:text-xl [&_h2]:font-bold [&_h2]:text-ink [&_h2]:mt-6 [&_p]:mt-3 [&_ul]:mt-3 [&_ul]:list-disc [&_ul]:pl-6">
                {!! $project->body !!}
            </div>
        @endif

        <div class="mt-10 flex flex-wrap items-center gap-3 border-t border-line pt-8">
            <x-button :href="\App\Support\Business::whatsappUrl()" variant="accent" size="lg" target="_blank" rel="noopener noreferrer" aria-label="Contactează Decor Urban pe WhatsApp" data-track-event="click_whatsapp" data-track-params="{}">Cere ofertă similară</x-button>
            <x-button :href="route('proiecte')" variant="outline" size="lg">← Toate proiectele</x-button>
        </div>

        @if ($similar->isNotEmpty())
            <section class="mt-16 border-t border-line pt-10">
                <h2 class="text-xl font-bold text-ink">Alte proiecte</h2>
                <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-3">
                    @foreach ($similar as $item)
                        @php $img = $item->primaryImage(); @endphp
                        <a href="{{ route('project.show', $item->slug) }}" class="group flex flex-col overflow-hidden rounded-card border border-line bg-surface-card shadow-card transition-all hover:shadow-card-hover hover:-translate-y-1">
                            <div class="aspect-[4/3] overflow-hidden bg-tint-stone">
                                @if ($img)<img src="{{ $img->thumbUrl(400) }}" srcset="{{ $img->thumbUrl(400) }} 400w, {{ $img->thumbUrl(800) }} 800w" sizes="(max-width:640px) 100vw, 320px" alt="{{ $item->title }}" loading="lazy" width="400" height="400" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">@endif
                            </div>
                            <div class="p-4">
                                <h3 class="font-semibold text-ink group-hover:text-accent transition-colors">{{ $item->title }}</h3>
                                @if ($item->location)<p class="mt-0.5 text-xs text-ink-muted">{{ $item->location }}</p>@endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-layouts.storefront>
