@props(['compact' => false])

@php
    $labels = [
        'facebook' => 'Urmărește Decor Urban pe Facebook',
        'instagram' => 'Urmărește Decor Urban pe Instagram',
        'tiktok' => 'Urmărește Decor Urban pe TikTok',
        'whatsapp' => 'Contactează Decor Urban pe WhatsApp',
        'google_maps' => 'Vezi Decor Urban pe Google Maps',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-2']) }}>
    @foreach (\App\Support\Business::socialLinks() as $network => $url)
        <a href="{{ $url }}"
           target="_blank"
           rel="noopener noreferrer"
           aria-label="{{ $labels[$network] }}"
           {!! \App\Support\Tracking::attrs('outbound_social_click', ['social_network' => $network]) !!}
           class="inline-flex h-10 w-10 items-center justify-center rounded-button border border-line bg-white text-ink-soft transition-colors hover:border-accent hover:text-accent">
            <x-social-icon :name="$network" class="h-5 w-5" />
        </a>
    @endforeach
</div>
