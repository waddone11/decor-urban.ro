<div class="mt-4">
    @if ($sent)
        <div class="rounded-card border border-accent/30 bg-accent-soft p-5" role="status">
            <p class="font-semibold text-accent">Mulțumim! Am primit mesajul.</p>
            <p class="mt-1 text-sm text-ink-soft">Revenim curând cu un răspuns. Pentru ceva urgent, scrie-ne pe WhatsApp.</p>
            <button type="button" wire:click="$set('sent', false)" class="mt-3 text-sm font-semibold text-accent hover:underline">Trimite alt mesaj</button>
        </div>
    @else
        <form wire:submit="submit" class="space-y-4">
            @php $inputCls = 'w-full rounded-button border border-line bg-white px-3 py-2.5 text-sm text-ink placeholder:text-ink-muted focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent'; @endphp

            <div>
                <label for="cf-name" class="block text-sm font-medium text-ink">Nume</label>
                <input id="cf-name" type="text" wire:model="name" autocomplete="name" class="{{ $inputCls }} mt-1">
                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="cf-phone" class="block text-sm font-medium text-ink">Telefon</label>
                    <input id="cf-phone" type="tel" wire:model="phone" autocomplete="tel" class="{{ $inputCls }} mt-1">
                    @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="cf-email" class="block text-sm font-medium text-ink">Email</label>
                    <input id="cf-email" type="email" wire:model="email" autocomplete="email" class="{{ $inputCls }} mt-1">
                    @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="cf-message" class="block text-sm font-medium text-ink">Mesaj</label>
                <textarea id="cf-message" rows="5" wire:model="message" class="{{ $inputCls }} mt-1" placeholder="Spune-ne ce ai nevoie: produs, cantitate, dimensiuni, termen…"></textarea>
                @error('message') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Honeypot anti-spam: ascuns pentru oameni, completat doar de boți. --}}
            <div class="absolute -left-[9999px]" aria-hidden="true">
                <label>Website (nu completa)<input type="text" wire:model="website" tabindex="-1" autocomplete="off"></label>
            </div>

            <button type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-button bg-accent px-6 py-3 text-sm font-medium text-white transition-colors hover:bg-accent-hover focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-accent disabled:opacity-60"
                    wire:loading.attr="disabled" wire:target="submit">
                <span wire:loading.remove wire:target="submit">Trimite mesajul</span>
                <span wire:loading wire:target="submit">Se trimite…</span>
            </button>

            <p class="text-xs text-ink-muted">Trimițând formularul, ești de acord cu <a href="{{ route('confidentialitate') }}" class="underline hover:text-accent">politica de confidențialitate</a>.</p>
        </form>
    @endif
</div>
