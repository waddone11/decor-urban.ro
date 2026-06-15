@php
    $categories = $navCategories ?? \App\Models\Category::query()->active()->ordered()->withCount('products')->get();
    $whatsapp = config('contact.whatsapp');
@endphp

<header x-data="{ mobileOpen: false }" class="sticky top-0 z-40 border-b border-line bg-surface/90 backdrop-blur">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        {{-- Wordmark (logo SVG vine ulterior — acum text) --}}
        <a href="{{ url('/') }}" class="flex items-center gap-2 shrink-0">
            <span class="text-xl font-extrabold tracking-tight text-ink">Decor<span class="text-accent">Urban</span></span>
        </a>

        {{-- Nav desktop --}}
        <nav class="hidden lg:flex items-center gap-1">
            <a href="{{ url('/') }}" class="px-3 py-2 text-sm font-medium text-ink-soft hover:text-ink transition-colors">Acasă</a>

            <div x-data="{ open: false }" class="relative" @mouseenter="open = true" @mouseleave="open = false">
                <button class="flex items-center gap-1 px-3 py-2 text-sm font-medium text-ink-soft hover:text-ink transition-colors">
                    Categorii
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>
                <div x-show="open" x-cloak x-transition.opacity
                     class="absolute left-0 top-full w-[34rem] pt-2">
                    <div class="grid grid-cols-2 gap-1 rounded-card border border-line bg-surface-card p-3 shadow-card-hover">
                        @foreach ($categories as $category)
                            <a href="#" class="flex items-center justify-between rounded-lg px-3 py-2.5 text-sm text-ink hover:bg-tint-stone transition-colors">
                                <span class="font-medium">{{ $category->name }}</span>
                                <span class="text-xs text-ink-muted">{{ $category->products_count }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <a href="#" class="px-3 py-2 text-sm font-medium text-ink-soft hover:text-ink transition-colors">Despre</a>
            <a href="#" class="px-3 py-2 text-sm font-medium text-ink-soft hover:text-ink transition-colors">Contact</a>
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
            <button @click="mobileOpen = !mobileOpen" class="lg:hidden rounded-lg p-2 text-ink hover:bg-tint-stone transition-colors" aria-label="Meniu">
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
    <div x-show="mobileOpen" x-cloak x-transition class="lg:hidden border-t border-line bg-surface-card">
        <div class="space-y-1 px-4 py-4">
            <a href="{{ url('/') }}" class="block rounded-lg px-3 py-2.5 text-base font-medium text-ink hover:bg-tint-stone">Acasă</a>
            <p class="px-3 pt-3 pb-1 text-xs font-semibold uppercase tracking-wider text-ink-muted">Categorii</p>
            @foreach ($categories as $category)
                <a href="#" class="flex items-center justify-between rounded-lg px-3 py-2.5 text-base text-ink hover:bg-tint-stone">
                    <span>{{ $category->name }}</span>
                    <span class="text-xs text-ink-muted">{{ $category->products_count }}</span>
                </a>
            @endforeach
            <a href="#" class="block rounded-lg px-3 py-2.5 text-base font-medium text-ink hover:bg-tint-stone mt-2">Despre</a>
            <a href="#" class="block rounded-lg px-3 py-2.5 text-base font-medium text-ink hover:bg-tint-stone">Contact</a>
            <x-button :href="'https://wa.me/'.$whatsapp" variant="accent" size="md" class="mt-3 w-full">Cere ofertă pe WhatsApp</x-button>
        </div>
    </div>
</header>
