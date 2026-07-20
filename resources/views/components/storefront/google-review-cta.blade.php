@props(['compact' => false])

@if (config('business.google_review_url'))
    <a href="{{ config('business.google_review_url') }}"
       target="_blank"
       rel="noopener noreferrer"
       aria-label="Lasă o recenzie pentru Decor Urban pe Google"
       data-track-event="outbound_review_click"
       data-track-params="{}"
       {{ $attributes->merge(['class' => $compact
            ? 'inline-flex items-center gap-2 text-sm font-semibold text-accent hover:text-accent-hover'
            : 'inline-flex items-center justify-center gap-2 rounded-button border border-line bg-white px-4 py-2.5 text-sm font-semibold text-ink transition-colors hover:border-accent hover:text-accent']) }}>
        <x-social-icon name="google_maps" class="h-5 w-5" />
        Lasă-ne o recenzie pe Google
    </a>
@endif
