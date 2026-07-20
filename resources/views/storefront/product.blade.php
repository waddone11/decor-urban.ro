<x-layouts.storefront :title="$product->seoTitle()" :description="$product->seoDescription()" :og-image="$product->ogImageUrl()" og-type="product">
    @foreach ($jsonLd as $ld)
        <x-seo.jsonld :data="$ld" />
    @endforeach

    @php
        $images = $product->galleryImages();
        $hasPrice = ! $product->isPriceOnRequest();
        $shareUrl = route('product', $product->slug);
        $productTrackParams = e(json_encode([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_code' => $product->code,
            'product_category' => $primaryCategory?->name,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
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
                                <img src="{{ $image->thumbUrl(800) }}" alt="{{ $image->alt ?: $product->name }}"
                                     width="800" height="800"
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
                                    <img src="{{ $image->thumbUrl(400) }}" alt="" loading="lazy" width="400" height="400" class="aspect-square w-full object-cover">
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
                        @if ($product->hasSalePrice())
                            <p class="flex flex-wrap items-baseline gap-x-2.5 gap-y-1">
                                <s class="text-lg font-medium text-ink-muted">{{ \App\Models\Product::formatLei((float) $product->price) }}</s>
                                <span class="text-2xl font-bold text-accent">{{ \App\Models\Product::formatLei($product->currentPrice()) }}</span>
                                <span class="inline-flex items-center rounded-full bg-signal px-2.5 py-0.5 text-xs font-bold text-white">-{{ $product->discountPercent() }}%</span>
                            </p>
                        @else
                            <p class="text-2xl font-bold text-ink">{{ \App\Models\Product::formatLei($product->currentPrice()) }}</p>
                        @endif
                    @else
                        <span class="inline-flex items-center rounded-full bg-accent-soft px-3.5 py-1.5 text-sm font-semibold text-accent">Preț la cerere</span>
                    @endif
                    <p class="mt-2 text-sm text-ink-muted">Disponibilitate: {{ $product->availability ?: 'la comandă' }}</p>

                    {{-- SEAP/SICAP: semnal pentru achizitori publici + cod CPV (completat manual) --}}
                    @if ($product->available_seap)
                        <p class="mt-2 flex flex-wrap items-center gap-2 text-sm">
                            <span class="inline-flex items-center rounded-full bg-accent-warm px-2.5 py-0.5 text-xs font-bold text-ink">SEAP/SICAP</span>
                            <span class="text-ink-soft">Disponibil pentru achiziții publice</span>
                            @if ($product->cpv_code)
                                <span class="text-ink-muted">· Cod CPV: {{ $product->cpv_code }}</span>
                            @endif
                        </p>
                    @endif
                </div>

                {{-- Adaugă în coș (cerere de ofertă multi-produs) --}}
                <div class="mt-6">
                    <livewire:add-to-cart :product-id="$product->id" />
                </div>

                {{-- CTA rapid 1-produs (WhatsApp) + contact --}}
                <div class="mt-3 flex flex-col gap-3 sm:flex-row">
                    <x-button :href="$whatsappUrl" variant="accent" size="lg" class="flex-1"
                              target="_blank" rel="noopener noreferrer" aria-label="Contactează Decor Urban pe WhatsApp"
                              data-track-event="click_whatsapp" data-track-params="{{ $productTrackParams }}">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91S17.5 2 12.04 2Zm0 18.15c-1.48 0-2.93-.4-4.2-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.2 8.2 0 0 1-1.26-4.38c0-4.54 3.7-8.23 8.24-8.23s8.23 3.69 8.23 8.23-3.69 8.24-8.22 8.24Zm4.52-6.16c-.25-.12-1.47-.72-1.69-.81-.23-.08-.39-.12-.56.12-.16.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.12-1.05-.39-1.99-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.25-.01-.38.11-.51.11-.11.25-.29.37-.43.13-.14.17-.25.25-.41.08-.17.04-.31-.02-.43-.06-.12-.56-1.34-.76-1.84-.2-.48-.41-.42-.56-.43h-.48c-.17 0-.43.06-.66.31-.22.25-.86.85-.86 2.07 0 1.22.89 2.4 1.01 2.56.12.17 1.75 2.67 4.23 3.74.59.26 1.05.41 1.41.52.59.19 1.13.16 1.56.1.48-.07 1.47-.6 1.68-1.18.21-.58.21-1.07.14-1.18-.06-.1-.22-.16-.47-.28Z"/></svg>
                        Cere ofertă pe WhatsApp
                    </x-button>
                    <x-button :href="route('contact')" variant="outline" size="lg">Cere ofertă</x-button>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}" target="_blank" rel="noopener noreferrer"
                       aria-label="Distribuie produsul pe Facebook"
                       class="inline-flex h-10 items-center gap-2 rounded-button border border-line bg-white px-3 text-sm font-medium text-ink-soft hover:border-accent hover:text-accent">
                        <x-social-icon name="facebook" class="h-5 w-5" /> Facebook
                    </a>
                    <a href="https://wa.me/?text={{ rawurlencode($product->name.' '.$shareUrl) }}" target="_blank" rel="noopener noreferrer"
                       aria-label="Distribuie produsul pe WhatsApp"
                       class="inline-flex h-10 items-center gap-2 rounded-button border border-line bg-white px-3 text-sm font-medium text-ink-soft hover:border-accent hover:text-accent">
                        <x-social-icon name="whatsapp" class="h-5 w-5" /> WhatsApp
                    </a>
                    <button type="button"
                            x-data="{ copied: false }"
                            @click="navigator.clipboard.writeText('{{ $shareUrl }}'); copied = true; setTimeout(() => copied = false, 1600)"
                            class="inline-flex h-10 items-center gap-2 rounded-button border border-line bg-white px-3 text-sm font-medium text-ink-soft hover:border-accent hover:text-accent">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v2M10 8h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-8a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2Z"/></svg>
                        <span x-text="copied ? 'Copiat' : 'Copiază link'"></span>
                    </button>
                </div>

                {{-- Trust-row: diferențiatori de producător --}}
                <ul class="mt-6 grid grid-cols-2 gap-3 border-t border-line pt-6 text-sm sm:grid-cols-4">
                    @foreach ([
                        ['t' => 'Producător direct', 'd' => 'M2.25 21h19.5M4.5 21V7l8-4 8 4v14M9 21v-4h6v4'],
                        ['t' => 'Culori RAL la cerere', 'd' => 'M12 21a9 9 0 1 1 0-18 9 9 0 0 1 9 9 3 3 0 0 1-3 3h-1.5a1.5 1.5 0 0 0 0 3H12M7.5 10.5h.008v.008H7.5zM12 7.5h.008v.008H12zm4.5 3h.008v.008H16.5z'],
                        ['t' => 'Dimensiuni custom', 'd' => 'M21.75 6.75 17.25 2.25m0 0L13.5 6m3.75-3.75v13.5m-11.25 0L2.25 12m0 0L6 8.25M2.25 12h13.5'],
                        ['t' => 'Livrare națională', 'd' => 'M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25'],
                    ] as $t)
                        <li class="flex flex-col items-start gap-1.5 text-ink-soft">
                            <svg class="h-5 w-5 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $t['d'] }}" /></svg>
                            <span class="text-xs font-medium leading-tight text-ink">{{ $t['t'] }}</span>
                        </li>
                    @endforeach
                </ul>

                {{-- Notă custom — diferențiatorul de producător --}}
                <div class="mt-6 flex flex-col gap-3 rounded-card border border-line bg-tint-sky p-4 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-ink-soft"><span class="font-semibold text-ink">Solicită dimensiuni sau culoare custom.</span> Producem la comandă — culori RAL și dimensiuni adaptate proiectului tău.</p>
                    <x-button :href="$whatsappUrl" variant="outline" size="sm" target="_blank" rel="noopener noreferrer" class="shrink-0"
                              aria-label="Contactează Decor Urban pe WhatsApp" data-track-event="click_whatsapp" data-track-params="{{ $productTrackParams }}">Solicită custom</x-button>
                </div>

                {{-- Tab-uri: Descriere / Specificații / Livrare (accesibile, reduced-motion safe) --}}
                @php $specs = $product->displaySpecs(); @endphp
                <div class="mt-8 border-t border-line pt-6" x-data="{ tab: 'descriere' }">
                    <div class="flex flex-wrap gap-1 border-b border-line" role="tablist" aria-label="Detalii produs">
                        @foreach (['descriere' => 'Descriere', 'specificatii' => 'Specificații', 'livrare' => 'Livrare & montaj'] as $key => $label)
                            <button type="button" role="tab" :aria-selected="tab === '{{ $key }}' ? 'true' : 'false'"
                                    @click="tab = '{{ $key }}'"
                                    :class="tab === '{{ $key }}' ? 'border-accent text-accent' : 'border-transparent text-ink-soft hover:text-ink'"
                                    class="-mb-px border-b-2 px-4 py-2.5 text-sm font-medium transition-colors">{{ $label }}</button>
                        @endforeach
                    </div>

                    <div role="tabpanel" x-show="tab === 'descriere'"
                         class="pt-4 text-ink-soft leading-relaxed [&_p+p]:mt-3 [&_ul]:mt-3 [&_ul]:list-disc [&_ul]:pl-5 [&_ol]:mt-3 [&_ol]:list-decimal [&_ol]:pl-5">
                        {{ $product->descriptionHtml() ?? 'Descriere disponibilă la cerere.' }}
                    </div>

                    <div role="tabpanel" x-show="tab === 'specificatii'" x-cloak class="pt-4">
                        @if (! empty($specs))
                            <dl class="divide-y divide-line rounded-card border border-line">
                                @foreach ($specs as $label => $value)
                                    <div class="flex gap-4 px-4 py-3 text-sm">
                                        <dt class="w-32 shrink-0 font-medium text-ink-muted">{{ $label }}</dt>
                                        <dd class="text-ink">{{ $value }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        @else
                            <p class="text-sm text-ink-soft">Specificațiile tehnice detaliate sunt disponibile la cerere.</p>
                        @endif
                    </div>

                    <div role="tabpanel" x-show="tab === 'livrare'" x-cloak class="pt-4 text-sm text-ink-soft leading-relaxed">
                        <p>Livrăm în toată țara, cu factură. Termenul de livrare se confirmă în ofertă, în funcție de produs și cantitate.</p>
                        <p class="mt-2">Montajul și instalarea se pot discuta la cerere. Pentru proiecte de amploare (primării, școli, instituții) oferim consultanță tehnică.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recenzii reale de la clienți (doar approved; formular cu moderare) --}}
        <livewire:product-reviews :product-id="$product->id" />

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
