@props([
    'product',
    'href' => '#',
])

@php
    $image = $product->primaryImage();
    $thumb400 = $image?->thumbUrl(400);
    $thumb800 = $image?->thumbUrl(800);
    $code = $product->code ? ltrim($product->code, '#') : null;
    $material = $product->materialLabel();
@endphp

{{-- Container (group, relative): linkul acoperă imaginea+textul; butonul quick-add e
     SIBLING (în afara <a>) — HTML valid (fără interactive în interactive). --}}
<div {{ $attributes->merge(['class' => 'group relative flex flex-col overflow-hidden rounded-card bg-surface-card border border-line shadow-card transition-all duration-300 hover:shadow-card-hover hover:-translate-y-1']) }}>
    <a href="{{ $href }}" class="flex flex-1 flex-col">
        <div class="relative aspect-square overflow-hidden bg-tint-stone">
            @if ($image)
                <img src="{{ $thumb400 }}" srcset="{{ $thumb400 }} 400w, {{ $thumb800 }} 800w"
                     sizes="(max-width:640px) 50vw, 280px" alt="{{ $product->name }}"
                     loading="lazy" width="400" height="400"
                     class="h-full w-full object-cover transition-transform duration-500 motion-safe:group-hover:scale-105">
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
            @if ($material)
                <span class="mt-1.5 inline-flex w-fit items-center rounded-md bg-tint-sky px-2 py-0.5 text-xs font-medium text-ink-soft">{{ $material }}</span>
            @endif
            <div class="mt-3 flex items-center justify-between gap-2">
                @if ($product->isPriceOnRequest())
                    <span class="inline-flex items-center rounded-full bg-accent-soft px-3 py-1 text-sm font-semibold text-accent">La cerere</span>
                @elseif ($product->hasSalePrice())
                    <span class="flex flex-wrap items-baseline gap-x-1.5">
                        <s class="text-xs text-ink-muted">{{ \App\Models\Product::formatLei((float) $product->price) }}</s>
                        <span class="text-sm font-bold text-accent">{{ \App\Models\Product::formatLei($product->currentPrice()) }}</span>
                    </span>
                @else
                    <span class="text-sm font-bold text-ink">{{ \App\Models\Product::formatLei($product->currentPrice()) }}</span>
                @endif
                <span class="shrink-0 text-sm font-medium text-ink-soft transition-colors group-hover:text-ink">Detalii →</span>
            </div>
        </div>
    </a>

    {{-- Quick-add (peste imagine): vizibil pe mobil, la hover pe desktop (motion-safe). --}}
    <div class="absolute right-2 top-2 z-10 lg:opacity-0 lg:transition-opacity lg:duration-200 lg:group-hover:opacity-100 motion-reduce:transition-none lg:focus-within:opacity-100">
        <livewire:quick-add :product-id="$product->id" :key="'qa-'.$product->id" />
    </div>
</div>
