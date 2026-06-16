<x-layouts.storefront :title="$product->seoTitle()" :description="$product->seoDescription()" :og-image="$product->ogImageUrl()" og-type="product">
    @foreach ($jsonLd as $ld)
        <x-seo.jsonld :data="$ld" />
    @endforeach

    @php
        $images = $product->galleryImages();
        $hasPrice = ! $product->price_on_request && $product->price;
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <x-storefront.breadcrumb :items="array_values(array_filter([
            ['label' => 'Acasă', 'url' => url('/')],
            ['label' => 'Catalog', 'url' => route('catalog')],
            $primaryCategory ? ['label' => $primaryCategory->name, 'url' => route('category', $primaryCategory->slug)] : null,
            ['label' => $product->name],
        ]))" class="mb-6" />

        <div class="grid gap-8 lg:grid-cols-2 lg:gap-12">
            {{-- Galerie --}}
            <div x-data="{
                    active: 0,
                    count: {{ $images->count() }},
                    lightbox: false,
                    next() { this.active = (this.active + 1) % this.count },
                    prev() { this.active = (this.active - 1 + this.count) % this.count },
                 }"
                 @keydown.window.escape="lightbox = false"
                 @keydown.window.arrow-right="next()"
                 @keydown.window.arrow-left="prev()">
                @if ($images->isEmpty())
                    <div class="flex aspect-square items-center justify-center rounded-card border border-line bg-tint-stone text-ink-muted">
                        <svg class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 19.5h18a1.5 1.5 0 0 0 1.5-1.5V6A1.5 1.5 0 0 0 21 4.5H3A1.5 1.5 0 0 0 1.5 6v12A1.5 1.5 0 0 0 3 19.5Z" />
                        </svg>
                    </div>
                @else
                    {{-- Imagine mare --}}
                    <div class="relative overflow-hidden rounded-card border border-line bg-tint-stone">
                        @foreach ($images as $i => $image)
                            <button type="button" x-show="active === {{ $i }}" @click="lightbox = true"
                                    class="block w-full cursor-zoom-in" aria-label="Mărește imaginea">
                                <img src="{{ $image->url() }}" alt="{{ $image->alt ?: $product->name }}"
                                     @if($i === 0) fetchpriority="high" @else loading="lazy" @endif
                                     class="aspect-square w-full object-cover">
                            </button>
                        @endforeach
                    </div>

                    {{-- Thumbnails --}}
                    @if ($images->count() > 1)
                        <div class="mt-3 grid grid-cols-5 gap-2 sm:grid-cols-6">
                            @foreach ($images as $i => $image)
                                <button type="button" @click="active = {{ $i }}"
                                        :class="active === {{ $i }} ? 'ring-2 ring-accent' : 'ring-1 ring-line hover:ring-ink-muted'"
                                        class="overflow-hidden rounded-lg motion-safe:transition" aria-label="Imaginea {{ $i + 1 }}">
                                    <img src="{{ $image->url() }}" alt="" loading="lazy" class="aspect-square w-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif

                    {{-- Lightbox --}}
                    <div x-show="lightbox" x-cloak @click="lightbox = false"
                         x-transition.opacity
                         class="fixed inset-0 z-50 flex items-center justify-center bg-ink/90 p-4"
                         role="dialog" aria-modal="true" aria-label="Imagine produs mărită">
                        <button type="button" @click="lightbox = false" class="absolute right-4 top-4 rounded-full bg-white/10 p-2 text-white hover:bg-white/20" aria-label="Închide">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                        </button>
                        @foreach ($images as $i => $image)
                            <img x-show="active === {{ $i }}" @click.stop src="{{ $image->url() }}" alt="{{ $image->alt ?: $product->name }}"
                                 class="max-h-[90vh] max-w-full rounded-lg object-contain">
                        @endforeach
                        <template x-if="count > 1">
                            <div>
                                <button type="button" @click.stop="prev()" class="absolute left-4 top-1/2 -translate-y-1/2 rounded-full bg-white/10 p-2 text-white hover:bg-white/20" aria-label="Anterioara">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
                                </button>
                                <button type="button" @click.stop="next()" class="absolute right-4 top-1/2 -translate-y-1/2 rounded-full bg-white/10 p-2 text-white hover:bg-white/20" aria-label="Următoarea">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                @endif
            </div>

            {{-- Info --}}
            <div>
                @if ($product->code)
                    <p class="text-sm font-medium text-ink-muted">Cod {{ ltrim($product->code, '#') }}</p>
                @endif
                <h1 class="mt-1 text-2xl font-bold text-ink sm:text-3xl">{{ $product->name }}</h1>

                {{-- Categorii (badge-uri linkate) --}}
                @if ($product->categories->isNotEmpty())
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($product->categories as $cat)
                            @if ($cat->is_active)
                                <a href="{{ route('category', $cat->slug) }}"
                                   class="inline-flex items-center rounded-full border border-line bg-white px-3 py-1 text-xs font-medium text-ink-soft hover:border-accent hover:text-accent transition-colors">
                                    {{ $cat->name }}
                                </a>
                            @endif
                        @endforeach
                    </div>
                @endif

                {{-- Preț --}}
                <div class="mt-5">
                    @if ($hasPrice)
                        <p class="text-2xl font-bold text-ink">{{ number_format((float) $product->price, 2, ',', '.') }} lei</p>
                    @else
                        <span class="inline-flex items-center rounded-full bg-accent-soft px-3.5 py-1.5 text-sm font-semibold text-accent">Preț la cerere</span>
                    @endif
                    <p class="mt-2 text-sm text-ink-muted">Disponibilitate: {{ $product->availability ?: 'la comandă' }}</p>
                </div>

                {{-- CTA --}}
                <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                    <x-button :href="$whatsappUrl" variant="accent" size="lg" class="flex-1"
                              target="_blank" rel="noopener">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91S17.5 2 12.04 2Zm0 18.15c-1.48 0-2.93-.4-4.2-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.2 8.2 0 0 1-1.26-4.38c0-4.54 3.7-8.23 8.24-8.23s8.23 3.69 8.23 8.23-3.69 8.24-8.22 8.24Zm4.52-6.16c-.25-.12-1.47-.72-1.69-.81-.23-.08-.39-.12-.56.12-.16.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.12-1.05-.39-1.99-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.25-.01-.38.11-.51.11-.11.25-.29.37-.43.13-.14.17-.25.25-.41.08-.17.04-.31-.02-.43-.06-.12-.56-1.34-.76-1.84-.2-.48-.41-.42-.56-.43h-.48c-.17 0-.43.06-.66.31-.22.25-.86.85-.86 2.07 0 1.22.89 2.4 1.01 2.56.12.17 1.75 2.67 4.23 3.74.59.26 1.05.41 1.41.52.59.19 1.13.16 1.56.1.48-.07 1.47-.6 1.68-1.18.21-.58.21-1.07.14-1.18-.06-.1-.22-.16-.47-.28Z"/></svg>
                        Cere ofertă pe WhatsApp
                    </x-button>
                    <x-button :href="url('/').'#contact'" variant="outline" size="lg">Cere ofertă</x-button>
                </div>

                {{-- Descriere --}}
                <div class="mt-8 border-t border-line pt-6">
                    <h2 class="text-lg font-semibold text-ink">Descriere</h2>
                    <div class="prose-sm mt-2 max-w-none text-ink-soft">
                        {!! $product->description ? nl2br(e($product->description)) : '—' !!}
                    </div>
                </div>
            </div>
        </div>

        {{-- Produse similare --}}
        @if ($similar->isNotEmpty())
            <section class="mt-16 border-t border-line pt-10">
                <h2 class="text-xl font-bold text-ink">Produse similare</h2>
                <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach ($similar as $item)
                        <x-product-card :product="$item" :href="route('product', $item->slug)" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-layouts.storefront>
