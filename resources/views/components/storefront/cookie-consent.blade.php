{{--
    Banner consimțământ cookie-uri (GDPR/UE): necesare, analytics, marketing.
--}}
<div x-data="{
        show: false,
        panel: false,
        analytics: false,
        marketing: false,
        init() {
            const found = document.cookie.split('; ').find(c => c.startsWith('cookie_consent='));
            this.show = ! found;
            if (found) {
                try {
                    const consent = JSON.parse(decodeURIComponent(found.split('=')[1]));
                    this.analytics = !! consent.analytics;
                    this.marketing = !! consent.marketing;
                    window.decorTracking?.applyConsent(consent);
                } catch (e) {}
            }
        },
        save(analytics = this.analytics, marketing = this.marketing) {
            const consent = { necessary: true, analytics, marketing, date: new Date().toISOString() };
            document.cookie = 'cookie_consent=' + encodeURIComponent(JSON.stringify(consent)) + '; path=/; max-age=' + (60 * 60 * 24 * 365) + '; SameSite=Lax';
            window.decorTracking?.applyConsent(consent);
            this.show = false;
            this.panel = false;
        },
        acceptAll() {
            this.analytics = true;
            this.marketing = true;
            this.save(true, true);
        },
        rejectOptional() {
            this.analytics = false;
            this.marketing = false;
            this.save(false, false);
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
        <div class="text-sm text-ink-soft">
            <p>Folosim cookie-uri necesare, iar analytics/marketing doar cu acordul tău. Detalii în
                <a href="{{ route('politica-cookies') }}" class="font-medium text-accent underline hover:text-accent-hover">Politica de cookie-uri</a>.
            </p>
            <div x-show="panel" x-cloak class="mt-3 grid gap-2 text-sm">
                <label class="flex items-center justify-between gap-4 rounded-button border border-line px-3 py-2">
                    <span><strong class="text-ink">Necesare</strong> <span class="text-ink-muted">mereu active</span></span>
                    <input type="checkbox" checked disabled>
                </label>
                <label class="flex items-center justify-between gap-4 rounded-button border border-line px-3 py-2">
                    <span><strong class="text-ink">Analytics</strong> <span class="text-ink-muted">GA4/GTM</span></span>
                    <input type="checkbox" x-model="analytics" class="text-accent focus:ring-accent">
                </label>
                <label class="flex items-center justify-between gap-4 rounded-button border border-line px-3 py-2">
                    <span><strong class="text-ink">Marketing</strong> <span class="text-ink-muted">Meta Pixel, TikTok Pixel</span></span>
                    <input type="checkbox" x-model="marketing" class="text-accent focus:ring-accent">
                </label>
            </div>
        </div>
        <div class="flex shrink-0 flex-wrap items-center gap-2">
            <a href="{{ route('politica-cookies') }}"
               class="rounded-button px-4 py-2 text-sm font-medium text-ink-soft hover:bg-tint-stone hover:text-ink transition-colors">Detalii</a>
            <button type="button" @click="panel = ! panel"
                    class="rounded-button px-4 py-2 text-sm font-medium text-ink-soft hover:bg-tint-stone hover:text-ink transition-colors">
                Preferințe
            </button>
            <button type="button" @click="rejectOptional()"
                    class="rounded-button border border-line px-4 py-2 text-sm font-medium text-ink-soft transition-colors hover:bg-tint-stone">
                Respinge
            </button>
            <button type="button" x-show="panel" @click="save()"
                    class="rounded-button border border-line px-4 py-2 text-sm font-medium text-ink transition-colors hover:bg-tint-stone">
                Salvează
            </button>
            <button type="button" @click="acceptAll()"
                    class="rounded-button bg-accent px-5 py-2 text-sm font-medium text-white transition-colors hover:bg-accent-hover focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-accent">
                Acceptă tot
            </button>
        </div>
    </div>
</div>
