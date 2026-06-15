@props([
    'eyebrow' => null,
    'title' => '',
    'subtitle' => null,
    'align' => 'left', // left | center
])

<div @class([
    'max-w-2xl',
    'mx-auto text-center' => $align === 'center',
])>
    @if ($eyebrow)
        <p class="text-sm font-semibold uppercase tracking-wider text-accent">{{ $eyebrow }}</p>
    @endif
    <h2 class="mt-2 text-3xl sm:text-4xl font-bold text-ink">{{ $title }}</h2>
    @if ($subtitle)
        <p class="mt-3 text-base text-ink-soft leading-relaxed">{{ $subtitle }}</p>
    @endif
</div>
