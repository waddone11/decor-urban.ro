<div class="mx-auto max-w-2xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="text-center">
        <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-accent-soft text-accent">
            <svg class="h-9 w-9" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
        </span>
        <h1 class="mt-5 text-2xl font-bold text-ink sm:text-3xl">Am primit comanda ta!</h1>
        <p class="mt-2 text-ink-soft">
            Comandă <strong class="text-ink">{{ $order->number }}</strong>. Revenim curând cu confirmarea și oferta
            (prețurile sunt la cerere).
        </p>
        <p class="mt-1 text-sm text-ink-muted">Ți-am trimis un email de confirmare la <strong>{{ $order->email }}</strong>.</p>
    </div>

    {{-- Rezumat --}}
    <div class="mt-8 rounded-card border border-line bg-surface-card p-5">
        <h2 class="text-sm font-semibold uppercase tracking-wider text-ink-muted">Rezumat comandă</h2>
        <ul class="mt-4 divide-y divide-line">
            @foreach ($order->items as $item)
                <li class="flex items-start justify-between gap-3 py-2.5 text-sm">
                    <span class="min-w-0 text-ink">
                        {{ $item->product_name }}
                        @if ($item->product_code)<span class="block text-xs text-ink-muted">Cod {{ ltrim($item->product_code, '#') }}</span>@endif
                    </span>
                    <span class="shrink-0 font-semibold text-ink-soft">× {{ $item->quantity }}</span>
                </li>
            @endforeach
        </ul>
        <dl class="mt-4 space-y-1 border-t border-line pt-4 text-sm text-ink-soft">
            <div class="flex justify-between"><dt class="text-ink-muted">Metodă</dt><dd class="font-medium text-ink">{{ $order->paymentMethodLabel() }}</dd></div>
            <div class="flex justify-between"><dt class="text-ink-muted">Livrare</dt><dd class="text-right font-medium text-ink">{{ $order->city }}, jud. {{ $order->county }}</dd></div>
        </dl>
    </div>

    <div class="mt-8 flex flex-col items-center gap-3">
        {{-- Butonul WhatsApp se adaugă în Partea 3b. --}}
        <x-button :href="route('catalog')" variant="outline" size="lg">← Înapoi la catalog</x-button>
    </div>
</div>
