@props([
    'product',
    'href' => '#', // ruta către pagina de produs se cablează în 4b
])

@php
    $path = $product->primary_image_path;
    $img = $path ? \Illuminate\Support\Facades\Storage::disk('public')->url($path) : null;
    $code = $product->code ? ltrim($product->code, '#') : null;
@endphp

<a href="{{ $href }}"
   {{ $attributes->merge(['class' => 'group flex flex-col overflow-hidden rounded-card bg-surface-card border border-line shadow-card transition-all duration-300 hover:shadow-card-hover hover:-translate-y-1']) }}>
    <div class="relative aspect-square overflow-hidden bg-tint-stone">
        @if ($img)
            <img src="{{ $img }}" alt="{{ $product->name }}" loading="lazy"
                 class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
        @else
            <div class="flex h-full w-full items-center justify-center text-ink-muted">
                <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 19.5h18a1.5 1.5 0 0 0 1.5-1.5V6A1.5 1.5 0 0 0 21 4.5H3A1.5 1.5 0 0 0 1.5 6v12A1.5 1.5 0 0 0 3 19.5Z" />
                </svg>
            </div>
        @endif
    </div>

    <div class="flex flex-1 flex-col p-4">
        @if ($code)
            <span class="text-xs font-medium text-ink-muted">Cod {{ $code }}</span>
        @endif
        <h3 class="mt-1 text-base font-semibold leading-snug text-ink line-clamp-2 group-hover:text-accent transition-colors">
            {{ $product->name }}
        </h3>
        <div class="mt-3 flex items-center justify-between">
            <span class="inline-flex items-center rounded-full bg-accent-soft px-3 py-1 text-sm font-semibold text-accent">
                La cerere
            </span>
            <span class="text-sm font-medium text-ink-soft transition-colors group-hover:text-ink">Detalii →</span>
        </div>
    </div>
</a>
