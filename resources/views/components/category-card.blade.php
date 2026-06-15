@props([
    'category',
    'href' => null, // ruta /categorie/{slug} se cablează în 4b
])

@php
    $path = $category->representativeImagePath();
    $img = $path ? \Illuminate\Support\Facades\Storage::disk('public')->url($path) : null;
    $link = $href ?? '#';
    $count = $category->products_count ?? $category->products()->count();
@endphp

<a href="{{ $link }}"
   {{ $attributes->merge(['class' => 'group relative flex aspect-[4/5] flex-col justify-end overflow-hidden rounded-card border border-line bg-tint-sky shadow-card transition-all duration-300 hover:shadow-card-hover']) }}>
    @if ($img)
        <img src="{{ $img }}" alt="{{ $category->name }}" loading="lazy"
             class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
        <div class="absolute inset-0 bg-gradient-to-t from-ink/70 via-ink/10 to-transparent"></div>
    @endif

    <div class="relative p-5">
        <h3 @class([
            'text-lg font-bold leading-tight',
            'text-white' => $img,
            'text-ink' => ! $img,
        ])>{{ $category->name }}</h3>
        <p @class([
            'mt-1 text-sm',
            'text-white/80' => $img,
            'text-ink-soft' => ! $img,
        ])>{{ $count }} {{ $count === 1 ? 'produs' : 'produse' }}</p>
    </div>
</a>
