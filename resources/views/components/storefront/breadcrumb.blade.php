@props([
    // Listă de pași: [['label' => 'Acasă', 'url' => '/'], ['label' => 'Categorie']]
    // Ultimul element (fără url) e pagina curentă.
    'items' => [],
])

<nav aria-label="Breadcrumb" {{ $attributes->merge(['class' => 'text-sm']) }}>
    <ol class="flex flex-wrap items-center gap-1.5 text-ink-muted">
        @foreach ($items as $i => $item)
            <li class="flex items-center gap-1.5">
                @if (! empty($item['url']) && ! $loop->last)
                    <a href="{{ $item['url'] }}" class="hover:text-accent transition-colors">{{ $item['label'] }}</a>
                    <svg class="h-3.5 w-3.5 text-line" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                @else
                    <span class="font-medium text-ink" aria-current="page">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
