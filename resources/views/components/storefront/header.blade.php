@php
    $categories = $navCategories ?? \App\Models\Category::query()->active()->ordered()->withCount('products')->get();
    $whatsapp = config('contact.whatsapp');
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
            <a href="{{ url('/') }}#institutii" class="px-3 py-2 text-sm font-medium text-ink-soft hover:text-ink transition-colors">Instituții</a>
            <a href="{{ route('contact') }}" class="px-3 py-2 text-sm font-medium text-ink-soft hover:text-ink transition-colors">Contact</a>
        </nav>

        {{-- Acțiuni dreapta --}}
        <div class="flex items-center gap-1">
            {{-- Search placeholder (nefuncțional încă) --}}
            <button class="rounded-lg p-2 text-ink-soft hover:bg-tint-stone hover:text-ink transition-colors" aria-label="Căutare">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </button>
            {{-- Coș placeholder (se cablează în 4c) --}}
            <button class="rounded-lg p-2 text-ink-soft hover:bg-tint-stone hover:text-ink transition-colors" aria-label="Coș">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                </svg>
            </button>

            <x-button :href="'https://wa.me/'.$whatsapp" variant="accent" size="sm" class="hidden sm:inline-flex ml-1">
                Cere ofertă
            </x-button>

            {{-- Hamburger mobil --}}
            <button @click="mobileOpen = !mobileOpen" :aria-expanded="mobileOpen ? 'true' : 'false'" class="lg:hidden rounded-lg p-2 text-ink hover:bg-tint-stone transition-colors" aria-label="Meniu">
                <svg x-show="!mobileOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
                <svg x-show="mobileOpen" x-cloak class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    {{-- Meniu mobil --}}
    <div x-show="mobileOpen" x-cloak x-transition class="lg:hidden border-t border-shell-line bg-shell">
        <div class="space-y-1 px-4 py-4">
            <a href="{{ url('/') }}" class="block rounded-lg px-3 py-2.5 text-base font-medium text-ink hover:bg-tint-stone">Acasă</a>
            <p class="px-3 pt-3 pb-1 text-xs font-semibold uppercase tracking-wider text-ink-muted">Categorii</p>
            @foreach ($categories as $category)
                <a href="{{ route('category', $category->slug) }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-base text-ink hover:bg-tint-stone">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-tint-sky text-accent">
                        <x-category-icon :slug="$category->slug" class="h-5 w-5" />
                    </span>
                    <span class="flex-1">{{ $category->name }}</span>
                    <span class="text-xs text-ink-muted">{{ $category->products_count }}</span>
                </a>
            @endforeach
            <a href="{{ route('despre') }}" class="block rounded-lg px-3 py-2.5 text-base font-medium text-ink hover:bg-tint-stone mt-2">Despre</a>
            <a href="{{ url('/') }}#institutii" class="block rounded-lg px-3 py-2.5 text-base font-medium text-ink hover:bg-tint-stone">Instituții</a>
            <a href="{{ route('contact') }}" class="block rounded-lg px-3 py-2.5 text-base font-medium text-ink hover:bg-tint-stone">Contact</a>
            <x-button :href="'https://wa.me/'.$whatsapp" variant="accent" size="md" class="mt-3 w-full">Cere ofertă pe WhatsApp</x-button>
        </div>
    </div>
</header>
