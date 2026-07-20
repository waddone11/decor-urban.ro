@php
    $gtm = config('business.tracking.gtm_container_id');
    $ga4 = config('business.tracking.ga4_measurement_id');
    $meta = config('business.tracking.meta_pixel_id');
    $tiktok = config('business.tracking.tiktok_pixel_id');
    $trackingConfig = json_encode(compact('gtm', 'ga4', 'meta', 'tiktok'), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
@endphp

<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('consent', 'default', {
    ad_storage: 'denied',
    ad_user_data: 'denied',
    ad_personalization: 'denied',
    analytics_storage: 'denied',
    functionality_storage: 'granted',
    security_storage: 'granted',
    wait_for_update: 500
});
window.decorTracking = {
    config: {!! $trackingConfig !!},
    loaded: { analytics: false, marketing: false },
    event(name, params = {}) {
        window.dataLayer.push({ event: name, ...params });
        if (typeof gtag === 'function') gtag('event', name, params);
        if (typeof fbq === 'function' && params.marketing !== false) fbq('trackCustom', name, params);
        if (window.ttq && typeof window.ttq.track === 'function' && params.marketing !== false) window.ttq.track(name, params);
    },
    applyConsent(consent) {
        const analytics = !!consent.analytics;
        const marketing = !!consent.marketing;
        gtag('consent', 'update', {
            analytics_storage: analytics ? 'granted' : 'denied',
            ad_storage: marketing ? 'granted' : 'denied',
            ad_user_data: marketing ? 'granted' : 'denied',
            ad_personalization: marketing ? 'granted' : 'denied'
        });
        if (analytics) this.loadAnalytics();
        if (marketing) this.loadMarketing();
    },
    loadScript(src) {
        if ([...document.scripts].some(script => script.src === src)) return;
        const script = document.createElement('script');
        script.async = true;
        script.src = src;
        document.head.appendChild(script);
    },
    loadAnalytics() {
        if (this.loaded.analytics) return;
        this.loaded.analytics = true;
        if (this.config.gtm) this.loadScript('https://www.googletagmanager.com/gtm.js?id=' + encodeURIComponent(this.config.gtm));
        if (this.config.ga4) {
            this.loadScript('https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(this.config.ga4));
            gtag('js', new Date());
            gtag('config', this.config.ga4);
        }
    },
    loadMarketing() {
        if (this.loaded.marketing) return;
        this.loaded.marketing = true;
        if (this.config.meta) {
            !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window, document,'script','https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', this.config.meta);
            fbq('track', 'PageView');
        }
        if (this.config.tiktok) {
            this.loadScript('https://analytics.tiktok.com/i18n/pixel/events.js?sdkid=' + encodeURIComponent(this.config.tiktok) + '&lib=ttq');
        }
    }
};
document.addEventListener('click', (event) => {
    const target = event.target.closest('[data-track-event]');
    if (! target) return;
    let params = {};
    try { params = JSON.parse(target.dataset.trackParams || '{}'); } catch (e) {}
    window.decorTracking.event(target.dataset.trackEvent, params);
});
document.addEventListener('livewire:init', () => {
    Livewire.on('decor-track', ({ name, params }) => window.decorTracking.event(name, params || {}));
});
</script>
