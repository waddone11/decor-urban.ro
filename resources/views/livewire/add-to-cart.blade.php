<div class="flex flex-col gap-3 sm:flex-row sm:items-stretch">
    {{-- Selector cantitate --}}
    <div class="inline-flex items-center rounded-button border border-line bg-white">
        <button type="button" wire:click="decrement" aria-label="Scade cantitatea"
                class="px-3 py-2.5 text-ink-soft hover:text-ink disabled:opacity-40" @disabled($qty <= 1)>
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" /></svg>
        </button>
        <input type="number" min="1" wire:model.live="qty" aria-label="Cantitate"
               class="w-12 border-0 bg-transparent p-0 text-center text-sm font-semibold text-ink focus:outline-none focus:ring-0 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
        <button type="button" wire:click="increment" aria-label="Crește cantitatea"
                class="px-3 py-2.5 text-ink-soft hover:text-ink">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" /></svg>
        </button>
    </div>

    {{-- Adaugă în coș --}}
    <button type="button" wire:click="add"
            class="inline-flex flex-1 items-center justify-center gap-2 rounded-button bg-primary px-6 py-3 text-sm font-medium text-white transition-colors hover:bg-primary-hover focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-accent">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" /></svg>
        Adaugă în coș
    </button>

    {{-- Confirmare (live region, fără animație agresivă) --}}
    @if ($added)
        <p role="status" class="flex items-center gap-2 text-sm font-medium text-accent sm:self-center" wire:key="added-{{ $qty }}">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
            Adăugat în coș — <a href="{{ route('cart') }}" class="underline hover:text-accent-hover">vezi coșul</a>
        </p>
    @endif
</div>
