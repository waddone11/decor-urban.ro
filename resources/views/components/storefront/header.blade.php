@php
    $categories = $navCategories ?? \App\Models\Category::query()->active()->ordered()->withCount('products')->get();
    $whatsappUrl = \App\Support\Business::whatsappUrl();
@endphp

<header x-data="{ mobileOpen: false }" class="sticky top-0 z-40 border-b border-shell-line bg-shell/95 backdrop-blur">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        {{-- Logo: marca (image.svg) + wordmark cu font dedicat (token --font-logo). --}}
        <a href="{{ url('/') }}" class="flex items-center gap-2.5 shrink-0">
            <img src="{{ asset('images/logo.svg') }}" alt="Decor Urban" class="h-9 w-9 text-ink" width="36" height="36">
            <x-wordmark class="text-xl" />
        </a>

        {{-- Nav desktop --}}
        <nav class="hidden lg:flex items-center gap-1">
            <a href="{{ url('/') }}" class="px-3 py-2 text-sm font-medium text-ink-soft hover:text-ink transition-colors">Acasă</a>

            {{-- Mega-menu Categorii (hover + click, accesibil) --}}
            <div x-data="{ open: false }"
                 class="relative"
                 @mouseenter="open = true"
                 @mouseleave="open = false"
                 @keydown.escape.window="open = false">
                <button type="button"
                        @click="open = !open"
                        @click.outside="open = false"
                        :aria-expanded="open ? 'true' : 'false'"
                        aria-haspopup="true"
                        aria-controls="mega-categorii"
                        class="flex items-center gap-1 px-3 py-2 text-sm font-medium text-ink-soft hover:text-ink transition-colors">
                    Categorii
                    <svg class="h-4 w-4 transition-transform duration-200" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>

                <div id="mega-categorii"
                     x-show="open"
                     x-cloak
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-1"
                     class="absolute left-1/2 top-full w-[46rem] max-w-[calc(100vw-2rem)] -translate-x-1/2 pt-3">
                    <div class="rounded-card border border-line bg-surface-card p-4 shadow-card-hover">
                        <div class="grid grid-cols-2 gap-1 sm:grid-cols-3">
                            @foreach ($categories as $category)
                                <a href="{{ route('category', $category->slug) }}"
                                   class="group flex items-center gap-3 rounded-lg px-3 py-2.5 transition-colors hover:bg-accent-soft">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-tint-sky text-accent transition-colors group-hover:bg-white">
                                        <x-category-icon :slug="$category->slug" class="h-5 w-5" />
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block truncate text-sm font-semibold text-ink">{{ $category->name }}</span>
                                        <span class="block text-xs text-ink-muted">{{ $category->products_count }} {{ $category->products_count === 1 ? 'produs' : 'produse' }}</span>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                        <div class="mt-3 flex items-center justify-between border-t border-line pt-3">
                            <p class="text-xs text-ink-muted">{{ ucfirst(config('company.supplier_label')) }} · {{ $categories->count() }} categorii de mobilier urban</p>
                            <a href="{{ route('catalog') }}" class="text-sm font-semibold text-accent hover:text-accent-hover transition-colors">Vezi toate categoriile →</a>
                        </div>
                    </div>
                </div>
            </div>

            <a href="{{ route('despre') }}" class="px-3 py-2 text-sm font-medium text-ink-soft hover:text-ink transition-colors">Despre</a>
            <a href="{{ route('institutii') }}" class="px-3 py-2 text-sm font-medium text-ink-soft hover:text-ink transition-colors">Instituții</a>
            <a href="{{ route('contact') }}" class="px-3 py-2 text-sm font-medium text-ink-soft hover:text-ink transition-colors">Contact</a>
        </nav>

        {{-- Acțiuni dreapta --}}
        <div class="flex items-center gap-1">
            {{-- Coș (count live, Livewire) --}}
            <livewire:cart-counter />

            {{-- CTA doar pe desktop; pe mobil rămâne în meniul hamburger. --}}
            <div class="hidden lg:block ml-1">
                <x-button :href="$whatsappUrl" variant="accent" size="sm" target="_blank" rel="noopener noreferrer"
                          aria-label="Contactează Decor Urban pe WhatsApp" data-track-event="click_whatsapp" data-track-params="{}">
                    Cere ofertă
                </x-button>
            </div>

            {{-- Hamburger mobil --}}
            <button @click="mobileOpen = !mobileOpen" :aria-expanded="mobileOpen ? 'true' : 'false'" aria-controls="mobile-menu" class="lg:hidden rounded-lg p-2 text-ink hover:bg-tint-stone transition-colors" aria-label="Meniu">
                <svg x-show="!mobileOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
                <svg x-show="mobileOpen" x-cloak class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    {{-- Meniu mobil — grilă categorii + scroll + CTA sticky jos --}}
    <div id="mobile-menu"
         x-show="mobileOpen"
         x-cloak
         x-transition.opacity
         @keydown.escape.window="mobileOpen = false"
         x-effect="mobileOpen && $nextTick(() => $el.querySelector('a[href], button')?.focus())"
         @keydown.tab="
            let f = [...$el.querySelectorAll('a[href], button:not([disabled])')];
            if (! f.length) return;
            let i = f.indexOf(document.activeElement);
            if ($event.shiftKey && i <= 0) { $event.preventDefault(); f[f.length - 1].focus(); }
            else if (! $event.shiftKey && i === f.length - 1) { $event.preventDefault(); f[0].focus(); }
         "
         class="lg:hidden flex flex-col border-t border-shell-line bg-shell max-h-[calc(100dvh-4rem)]">
        {{-- Conținut scrollabil --}}
        <div class="flex-1 overflow-y-auto overscroll-contain px-4 py-4" style="-webkit-overflow-scrolling: touch;">
            <nav class="space-y-1" aria-label="Meniu mobil">
                <a href="{{ url('/') }}" class="block rounded-lg px-3 py-2.5 text-base font-medium text-ink hover:bg-tint-stone">Acasă</a>
                <a href="{{ route('despre') }}" class="block rounded-lg px-3 py-2.5 text-base font-medium text-ink hover:bg-tint-stone">Despre</a>
                <a href="{{ route('institutii') }}" class="block rounded-lg px-3 py-2.5 text-base font-medium text-ink hover:bg-tint-stone">Instituții</a>
                <a href="{{ route('contact') }}" class="block rounded-lg px-3 py-2.5 text-base font-medium text-ink hover:bg-tint-stone">Contact</a>
            </nav>

            {{-- Cele 11 categorii — grilă 2 coloane (descoperibile dintr-o privire) --}}
            <p class="px-1 pt-4 pb-2 text-xs font-semibold uppercase tracking-wider text-ink-muted">Categorii</p>
            <div class="grid grid-cols-2 gap-2">
                @foreach ($categories as $category)
                    <a href="{{ route('category', $category->slug) }}"
                       class="flex min-h-[44px] items-center gap-2.5 rounded-lg border border-shell-line bg-surface-card p-2.5 text-ink transition-colors hover:bg-tint-stone">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-tint-sky text-accent">
                            <x-category-icon :slug="$category->slug" class="h-5 w-5" />
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-medium leading-tight">{{ $category->name }}</span>
                            <span class="block text-xs text-ink-muted">{{ $category->products_count }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
            <x-storefront.social-links class="mt-5" />
        </div>

        {{-- CTA sticky jos — mereu accesibil, conținutul scrollează deasupra --}}
        <div class="shrink-0 border-t border-shell-line bg-shell px-4 py-3">
            <x-button :href="$whatsappUrl" variant="accent" size="md" class="w-full" target="_blank" rel="noopener noreferrer"
                      aria-label="Contactează Decor Urban pe WhatsApp" data-track-event="click_whatsapp" data-track-params="{}">Cere ofertă pe WhatsApp</x-button>
        </div>
    </div>
</header>
