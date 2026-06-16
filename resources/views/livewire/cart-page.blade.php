<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <x-storefront.breadcrumb :items="[
        ['label' => 'Acasă', 'url' => url('/')],
        ['label' => 'Coș'],
    ]" class="mb-6" />

    <h1 class="text-2xl font-bold text-ink sm:text-3xl">Coșul tău</h1>

    @if ($lines->isEmpty())
        {{-- Stare goală prietenoasă --}}
        <div class="mt-8 rounded-card border border-dashed border-line py-16 text-center">
            <svg class="mx-auto h-12 w-12 text-ink-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.3"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" /></svg>
            <p class="mt-4 text-ink-soft">Coșul tău e gol.</p>
            <p class="mt-1 text-sm text-ink-muted">Adaugă produse din catalog și trimite-ne o cerere de ofertă.</p>
            <x-button :href="route('catalog')" variant="accent" size="lg" class="mt-6">Vezi catalogul</x-button>
        </div>
    @else
        <p class="mt-1 text-sm text-ink-muted">{{ $count }} {{ $count === 1 ? 'produs' : 'produse' }} · prețuri la cerere — trimiți o cerere de ofertă.</p>

        <div class="mt-6 divide-y divide-line rounded-card border border-line bg-surface-card">
            @foreach ($lines as $line)
                @php $product = $line['product']; $img = $product->primaryImage(); @endphp
                <div class="flex items-center gap-4 p-4" wire:key="line-{{ $product->id }}">
                    <a href="{{ route('product', $product->slug) }}" class="block h-20 w-20 shrink-0 overflow-hidden rounded-lg border border-line bg-tint-stone">
                        @if ($img)
                            <img src="{{ $img->url() }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                        @endif
                    </a>

                    <div class="min-w-0 flex-1">
                        <a href="{{ route('product', $product->slug) }}" class="font-semibold text-ink hover:text-accent transition-colors line-clamp-2">{{ $product->name }}</a>
                        @if ($product->code)
                            <p class="text-xs text-ink-muted">Cod {{ ltrim($product->code, '#') }}</p>
                        @endif
                        <p class="mt-0.5 text-sm font-medium text-accent">La cerere</p>
                    </div>

                    {{-- Cantitate editabilă --}}
                    <div class="inline-flex items-center rounded-button border border-line bg-white">
                        <button type="button" wire:click="decrement({{ $product->id }})" aria-label="Scade" class="px-2.5 py-2 text-ink-soft hover:text-ink disabled:opacity-40" @disabled($line['qty'] <= 1)>
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" /></svg>
                        </button>
                        <input type="number" min="1" value="{{ $line['qty'] }}"
                               wire:change="updateQty({{ $product->id }}, $event.target.value)"
                               aria-label="Cantitate {{ $product->name }}"
                               class="w-12 border-0 bg-transparent p-0 text-center text-sm font-semibold text-ink focus:outline-none focus:ring-0 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                        <button type="button" wire:click="increment({{ $product->id }})" aria-label="Crește" class="px-2.5 py-2 text-ink-soft hover:text-ink">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" /></svg>
                        </button>
                    </div>

                    <button type="button" wire:click="remove({{ $product->id }})" aria-label="Elimină {{ $product->name }}" class="rounded-lg p-2 text-ink-muted hover:bg-red-50 hover:text-red-600 transition-colors">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                    </button>
                </div>
            @endforeach
        </div>

        <div class="mt-6 flex flex-col items-stretch justify-between gap-3 sm:flex-row sm:items-center">
            <x-button :href="route('catalog')" variant="outline">← Continuă cumpărăturile</x-button>
            <x-button :href="route('checkout')" variant="accent" size="lg">Finalizează comanda</x-button>
        </div>
    @endif
</div>
