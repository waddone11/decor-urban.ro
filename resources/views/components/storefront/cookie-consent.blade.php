{{--
    Banner consimțământ cookie-uri (GDPR). Discret, jos. Reține alegerea într-un cookie
    (cookie_consent=accepted, 1 an). Nu încărcăm tracking până la accept — dacă adăugăm
    vreodată analytics, condiționează-l pe `window.cookieConsentGranted`.
    Accesibil (role/aria, buton + link reale) și reduced-motion safe (fade gated motion-safe).
--}}
<div x-data="{
        show: false,
        init() {
            this.show = ! document.cookie.split('; ').some(c => c.startsWith('cookie_consent='));
            window.cookieConsentGranted = ! this.show;
        },
        accept() {
            document.cookie = 'cookie_consent=accepted; path=/; max-age=' + (60 * 60 * 24 * 365) + '; SameSite=Lax';
            window.cookieConsentGranted = true;
            this.show = false;
        }
     }"
     x-show="show"
     x-cloak
     x-transition:enter="motion-safe:transition motion-safe:duration-300"
     x-transition:enter-start="opacity-0 translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="motion-safe:transition motion-safe:duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     role="region"
     aria-label="Consimțământ cookie-uri"
     class="fixed inset-x-0 bottom-0 z-50 p-3 sm:p-4">
    <div class="mx-auto flex max-w-3xl flex-col gap-3 rounded-card border border-line bg-surface-card p-4 shadow-card-hover sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-ink-soft">
            Folosim cookie-uri strict necesare pentru funcționarea site-ului. Detalii în
            <a href="{{ route('politica-cookies') }}" class="font-medium text-accent underline hover:text-accent-hover">Politica de cookie-uri</a>.
        </p>
        <div class="flex shrink-0 items-center gap-2">
            <a href="{{ route('politica-cookies') }}"
               class="rounded-button px-4 py-2 text-sm font-medium text-ink-soft hover:bg-tint-stone hover:text-ink transition-colors">Detalii</a>
            <button type="button" @click="accept()"
                    class="rounded-button bg-accent px-5 py-2 text-sm font-medium text-white transition-colors hover:bg-accent-hover focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-accent">
                Accept
            </button>
        </div>
    </div>
</div>
