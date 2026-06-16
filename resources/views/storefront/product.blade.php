<x-layouts.storefront :title="$product->name">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <x-storefront.breadcrumb :items="array_values(array_filter([
            ['label' => 'Acasă', 'url' => url('/')],
            $primaryCategory ? ['label' => $primaryCategory->name, 'url' => route('category', $primaryCategory->slug)] : null,
            ['label' => $product->name],
        ]))" class="mb-6" />

        <div class="grid gap-8 lg:grid-cols-2">
            <div>
                @php $primary = $product->primaryImage(); @endphp
                @if ($primary)
                    <img src="{{ $primary->url() }}" alt="{{ $product->name }}"
                         class="w-full rounded-card border border-line object-cover">
                @endif
            </div>

            <div>
                <h1 class="text-2xl font-bold text-ink sm:text-3xl">{{ $product->name }}</h1>
                @if ($product->code)
                    <p class="mt-1 text-sm text-ink-muted">Cod {{ ltrim($product->code, '#') }}</p>
                @endif

                <p class="mt-4 inline-flex items-center rounded-full bg-accent-soft px-3 py-1 text-sm font-semibold text-accent">
                    La cerere
                </p>

                <div class="mt-4 text-ink-soft">
                    {{ $product->description ?: '—' }}
                </div>
            </div>
        </div>
    </div>
</x-layouts.storefront>
