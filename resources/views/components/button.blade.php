@props([
    'href' => null,
    'variant' => 'primary', // primary | accent | outline | ghost
    'size' => 'md',         // sm | md | lg
])

@php
    $base = 'inline-flex items-center justify-center gap-2 font-medium rounded-button transition-colors duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-accent';

    $variants = [
        'primary' => 'bg-primary text-white hover:bg-primary-hover',
        'accent'  => 'bg-accent text-white hover:bg-accent-hover',
        'outline' => 'border border-line bg-white text-ink hover:bg-tint-stone',
        'ghost'   => 'text-ink hover:bg-tint-stone',
    ];

    $sizes = [
        'sm' => 'text-sm px-4 py-2',
        'md' => 'text-sm px-5 py-2.5',
        'lg' => 'text-base px-7 py-3.5',
    ];

    $classes = $base.' '.($variants[$variant] ?? $variants['primary']).' '.($sizes[$size] ?? $sizes['md']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['class' => $classes, 'type' => 'button']) }}>{{ $slot }}</button>
@endif
