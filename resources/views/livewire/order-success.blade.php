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
                    <span class="shrink-0 text-right">
                        <span class="block font-semibold text-ink-soft">× {{ $item->quantity }}</span>
                        <span class="block text-xs {{ $item->unit_price === null ? 'text-accent' : 'font-medium text-ink-soft' }}">{{ $item->priceLabel() }}</span>
                    </span>
                </li>
            @endforeach
        </ul>
        <dl class="mt-4 space-y-1 border-t border-line pt-4 text-sm text-ink-soft">
            <div class="flex justify-between"><dt class="text-ink-muted">Metodă</dt><dd class="font-medium text-ink">{{ $order->paymentMethodLabel() }}</dd></div>
            <div class="flex justify-between"><dt class="text-ink-muted">Livrare</dt><dd class="text-right font-medium text-ink">{{ $order->city }}, jud. {{ $order->county }}</dd></div>
        </dl>
    </div>

    <div class="mt-8 flex flex-col items-center gap-3">
        <p class="text-sm text-ink-soft">Vrei să trimiți comanda și pe WhatsApp? Apasă mai jos — mesajul e deja pregătit.</p>
        <x-button :href="$order->whatsappUrl()" variant="accent" size="lg" target="_blank" rel="noopener noreferrer" class="w-full sm:w-auto"
                  aria-label="Contactează Decor Urban pe WhatsApp" data-track-event="click_whatsapp" data-track-params="{}">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91S17.5 2 12.04 2Zm0 18.15c-1.48 0-2.93-.4-4.2-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.2 8.2 0 0 1-1.26-4.38c0-4.54 3.7-8.23 8.24-8.23s8.23 3.69 8.23 8.23-3.69 8.24-8.22 8.24Zm4.52-6.16c-.25-.12-1.47-.72-1.69-.81-.23-.08-.39-.12-.56.12-.16.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.12-1.05-.39-1.99-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.25-.01-.38.11-.51.11-.11.25-.29.37-.43.13-.14.17-.25.25-.41.08-.17.04-.31-.02-.43-.06-.12-.56-1.34-.76-1.84-.2-.48-.41-.42-.56-.43h-.48c-.17 0-.43.06-.66.31-.22.25-.86.85-.86 2.07 0 1.22.89 2.4 1.01 2.56.12.17 1.75 2.67 4.23 3.74.59.26 1.05.41 1.41.52.59.19 1.13.16 1.56.1.48-.07 1.47-.6 1.68-1.18.21-.58.21-1.07.14-1.18-.06-.1-.22-.16-.47-.28Z"/></svg>
            Trimite comanda pe WhatsApp
        </x-button>
        @if (config('business.google_review_url'))
            <p class="mt-3 text-sm text-ink-soft">Mulțumim! O recenzie pe Google ne ajută enorm.</p>
            <x-storefront.google-review-cta />
        @endif
        <x-button :href="route('catalog')" variant="outline" size="lg">← Înapoi la catalog</x-button>
    </div>
</div>
