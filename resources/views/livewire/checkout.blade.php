<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <x-storefront.breadcrumb :items="[
        ['label' => 'Acasă', 'url' => url('/')],
        ['label' => 'Coș', 'url' => route('cart')],
        ['label' => 'Finalizează comanda'],
    ]" class="mb-6" />

    <h1 class="text-2xl font-bold text-ink sm:text-3xl">Finalizează comanda</h1>
    <p class="mt-1 text-sm text-ink-muted">Prețurile sunt la cerere — trimiți o cerere de ofertă. Revenim cu confirmarea și oferta.</p>

    <form wire:submit="placeOrder" class="mt-8 grid gap-8 lg:grid-cols-[1fr_20rem]">
        @php $inputCls = 'w-full rounded-button border border-line bg-white px-3 py-2.5 text-sm text-ink placeholder:text-ink-muted focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent'; @endphp

        {{-- Date client --}}
        <div class="space-y-5">
            <div class="rounded-card border border-line bg-surface-card p-5">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-ink-muted">Date de contact</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="co-name" class="block text-sm font-medium text-ink">Nume și prenume *</label>
                        <input id="co-name" type="text" wire:model="customer_name" autocomplete="name" class="{{ $inputCls }} mt-1">
                        @error('customer_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="co-company" class="block text-sm font-medium text-ink">Firmă / instituție</label>
                        <input id="co-company" type="text" wire:model="company" autocomplete="organization" class="{{ $inputCls }} mt-1" placeholder="opțional">
                        @error('company') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="co-cui" class="block text-sm font-medium text-ink">CUI</label>
                        <input id="co-cui" type="text" wire:model="cui" class="{{ $inputCls }} mt-1" placeholder="opțional, pentru firme/instituții">
                        @error('cui') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="co-phone" class="block text-sm font-medium text-ink">Telefon *</label>
                        <input id="co-phone" type="tel" wire:model="phone" autocomplete="tel" class="{{ $inputCls }} mt-1">
                        @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="co-email" class="block text-sm font-medium text-ink">Email *</label>
                        <input id="co-email" type="email" wire:model="email" autocomplete="email" class="{{ $inputCls }} mt-1">
                        @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="rounded-card border border-line bg-surface-card p-5">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-ink-muted">Livrare</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="co-county" class="block text-sm font-medium text-ink">Județ *</label>
                        <select id="co-county" wire:model="county" class="{{ $inputCls }} mt-1">
                            <option value="">Alege județul…</option>
                            @foreach ($counties as $c)
                                <option value="{{ $c }}">{{ $c }}</option>
                            @endforeach
                        </select>
                        @error('county') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="co-city" class="block text-sm font-medium text-ink">Localitate *</label>
                        <input id="co-city" type="text" wire:model="city" class="{{ $inputCls }} mt-1">
                        @error('city') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="co-address" class="block text-sm font-medium text-ink">Adresă *</label>
                        <input id="co-address" type="text" wire:model="address" autocomplete="street-address" class="{{ $inputCls }} mt-1" placeholder="stradă, număr, alte detalii">
                        @error('address') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="rounded-card border border-line bg-surface-card p-5">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-ink-muted">Metodă</h2>
                <div class="mt-4 space-y-2">
                    @foreach ($methods as $value => $label)
                        <label class="flex cursor-pointer items-center gap-3 rounded-button border border-line p-3 text-sm has-[:checked]:border-accent has-[:checked]:bg-accent-soft">
                            <input type="radio" wire:model="payment_method" value="{{ $value }}" class="text-accent focus:ring-accent">
                            <span class="font-medium text-ink">{{ $label }}</span>
                        </label>
                    @endforeach
                    @error('payment_method') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="mt-4">
                    <label for="co-notes" class="block text-sm font-medium text-ink">Note / mesaj</label>
                    <textarea id="co-notes" rows="3" wire:model="notes" class="{{ $inputCls }} mt-1" placeholder="dimensiuni custom, culori RAL, termen dorit…"></textarea>
                    @error('notes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Honeypot --}}
                <div class="absolute -left-[9999px]" aria-hidden="true">
                    <label>Website (nu completa)<input type="text" wire:model="website" tabindex="-1" autocomplete="off"></label>
                </div>
            </div>
        </div>

        {{-- Rezumat comandă --}}
        <aside class="lg:sticky lg:top-20 lg:self-start">
            <div class="rounded-card border border-line bg-surface-card p-5">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-ink-muted">Comanda ta ({{ $count }})</h2>
                <ul class="mt-4 divide-y divide-line">
                    @foreach ($lines as $line)
                        <li class="flex items-start justify-between gap-3 py-2.5 text-sm" wire:key="sum-{{ $line['product']->id }}">
                            <span class="min-w-0 text-ink">
                                {{ $line['product']->name }}
                                @if ($line['product']->code)<span class="block text-xs text-ink-muted">Cod {{ ltrim($line['product']->code, '#') }}</span>@endif
                            </span>
                            <span class="shrink-0 font-semibold text-ink-soft">× {{ $line['qty'] }}</span>
                        </li>
                    @endforeach
                </ul>
                <p class="mt-4 rounded-lg bg-tint-sky px-3 py-2 text-xs text-ink-soft">
                    Preț: <strong>la cerere</strong>. Revenim cu oferta după ce primim comanda.
                </p>

                <button type="submit"
                        class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-button bg-accent px-6 py-3 text-sm font-medium text-white transition-colors hover:bg-accent-hover focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-accent disabled:opacity-60"
                        wire:loading.attr="disabled" wire:target="placeOrder">
                    <span wire:loading.remove wire:target="placeOrder">Trimite comanda</span>
                    <span wire:loading wire:target="placeOrder">Se trimite…</span>
                </button>
                <a href="{{ route('cart') }}" class="mt-2 block text-center text-xs text-ink-muted hover:text-accent">← Înapoi la coș</a>
            </div>
        </aside>
    </form>
</div>
