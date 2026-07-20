<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <x-seo.jsonld :data="$itemListLd" />

    <x-storefront.breadcrumb :items="[
        ['label' => 'Acasă', 'url' => url('/')],
        ['label' => 'Catalog'],
    ]" class="mb-6" />

    <header class="border-b border-line pb-6">
        <h1 class="text-2xl font-bold text-ink sm:text-3xl">
            {{ $activeCategory?->name ?? 'Catalog produse' }}
        </h1>
        <p class="mt-1 text-ink-soft">Mobilier urban și stradal — producător direct.</p>
    </header>

    <div class="mt-6 grid gap-8 lg:grid-cols-[16rem_1fr]">
        {{-- Sidebar filtre --}}
        <aside class="lg:sticky lg:top-20 lg:self-start" x-data="{ filtersOpen: false }">
            {{-- Search --}}
            <div class="relative">
                <input type="search" wire:model.live.debounce.300ms="q"
                       placeholder="Caută după nume sau cod…"
                       class="w-full rounded-button border border-line bg-white py-2 pl-9 pr-3 text-sm text-ink placeholder:text-ink-muted focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </div>

            {{-- Mobil: filtrele stau într-un accordion ca să nu ocupe tot ecranul --}}
            <button type="button" @click="filtersOpen = !filtersOpen" :aria-expanded="filtersOpen"
                    class="mt-4 flex w-full items-center justify-between rounded-button border border-line bg-white px-4 py-2.5 text-sm font-medium text-ink lg:hidden">
                Filtre
                <svg class="h-4 w-4 transition-transform motion-reduce:transition-none" :class="filtersOpen && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
            </button>

            <div :class="filtersOpen ? 'block' : 'hidden'" class="lg:block">
            {{-- Categorii --}}
            <nav class="mt-6">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-ink-muted">Categorii</p>
                <ul class="space-y-0.5">
                    <li>
                        <button type="button" wire:click="$set('cat', null)"
                                @class([
                                    'flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2 text-left text-sm transition-colors',
                                    'bg-accent-soft font-semibold text-accent' => ! $cat,
                                    'text-ink-soft hover:bg-tint-stone hover:text-ink' => (bool) $cat,
                                ])>
                            <span>Toate</span>
                            <span class="text-xs text-ink-muted">{{ $totalCount }}</span>
                        </button>
                    </li>
                    @foreach ($categories as $category)
                        <li>
                            <button type="button" wire:click="$set('cat', '{{ $category->slug }}')"
                                    @class([
                                        'flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-left text-sm transition-colors',
                                        'bg-accent-soft font-semibold text-accent' => $cat === $category->slug,
                                        'text-ink-soft hover:bg-tint-stone hover:text-ink' => $cat !== $category->slug,
                                    ])>
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-tint-sky text-accent">
                                    <x-category-icon :slug="$category->slug" class="h-4 w-4" />
                                </span>
                                <span class="min-w-0 flex-1 truncate">{{ $category->name }}</span>
                                <span class="text-xs text-ink-muted">{{ $category->products_count }}</span>
                            </button>
                        </li>
                    @endforeach
                </ul>
            </nav>

            {{-- Material (facete derivate din specs) --}}
            @if (! empty($materialFacets))
                <fieldset class="mt-6">
                    <legend class="mb-2 text-xs font-semibold uppercase tracking-wider text-ink-muted">Material</legend>
                    <ul class="space-y-0.5">
                        @foreach ($materialFacets as $mat => $cnt)
                            <li>
                                <label class="flex cursor-pointer items-center gap-2.5 rounded-lg px-3 py-1.5 text-sm text-ink-soft transition-colors hover:bg-tint-stone hover:text-ink has-[:checked]:font-semibold has-[:checked]:text-accent">
                                    <input type="checkbox" wire:model.live="materials" value="{{ $mat }}"
                                           class="rounded border-line text-accent focus:ring-accent">
                                    <span class="flex-1">{{ ucfirst($mat) }}</span>
                                    <span class="text-xs text-ink-muted">{{ $cnt }}</span>
                                </label>
                            </li>
                        @endforeach
                    </ul>
                </fieldset>
            @endif

            {{-- Oferte & achiziții publice --}}
            @if ($promoCount > 0 || $seapCount > 0)
                <fieldset class="mt-6">
                    <legend class="mb-2 text-xs font-semibold uppercase tracking-wider text-ink-muted">Oferte</legend>
                    <ul class="space-y-0.5">
                        @if ($promoCount > 0)
                            <li>
                                <label class="flex cursor-pointer items-center gap-2.5 rounded-lg px-3 py-1.5 text-sm text-ink-soft transition-colors hover:bg-tint-stone hover:text-ink has-[:checked]:font-semibold has-[:checked]:text-accent">
                                    <input type="checkbox" wire:model.live="promo"
                                           class="rounded border-line text-accent focus:ring-accent">
                                    <span class="flex-1">Doar reduceri</span>
                                    <span class="text-xs text-ink-muted">{{ $promoCount }}</span>
                                </label>
                            </li>
                        @endif
                        @if ($seapCount > 0)
                            <li>
                                <label class="flex cursor-pointer items-center gap-2.5 rounded-lg px-3 py-1.5 text-sm text-ink-soft transition-colors hover:bg-tint-stone hover:text-ink has-[:checked]:font-semibold has-[:checked]:text-accent">
                                    <input type="checkbox" wire:model.live="seap"
                                           class="rounded border-line text-accent focus:ring-accent">
                                    <span class="flex-1">SEAP / SICAP</span>
                                    <span class="text-xs text-ink-muted">{{ $seapCount }}</span>
                                </label>
                            </li>
                        @endif
                    </ul>
                </fieldset>
            @endif
            {{-- TODO(filtre): interval de preț — amânat până când 30-40+ produse au preț
                 real (azi ~10/127, doar bănci — vezi docs/AUDIT-FILTRE.md). --}}
            </div>
        </aside>

        {{-- Rezultate --}}
        <div>
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm text-ink-muted">
                    {{ $products->total() }} {{ $products->total() === 1 ? 'produs' : 'produse' }}
                    @if ($cat || trim($q) !== '' || ! empty($materials) || $promo || $seap)
                        · <button type="button" wire:click="clearFilters" class="text-accent hover:underline">resetează filtrele</button>
                    @endif
                </p>
                <label class="flex items-center gap-2 text-sm">
                    <span class="text-ink-muted">Sortează:</span>
                    <select wire:model.live="sort"
                            class="rounded-button border border-line bg-white py-1.5 pl-3 pr-8 text-sm text-ink focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent">
                        <option value="recomandate">Recomandate</option>
                        <option value="nume">Nume A–Z</option>
                        <option value="cod">Cod</option>
                    </select>
                </label>
            </div>

            @if ($products->isEmpty())
                <div class="rounded-card border border-dashed border-line py-16 text-center">
                    <p class="text-ink-muted">Niciun produs nu se potrivește filtrelor.</p>
                    <button type="button" wire:click="clearFilters" class="mt-2 text-sm font-semibold text-accent hover:underline">Resetează filtrele</button>
                </div>
            @else
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                    @foreach ($products as $product)
                        <x-product-card :product="$product" :href="route('product', $product->slug)" wire:key="prod-{{ $product->id }}" />
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
