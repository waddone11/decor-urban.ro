import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

let ctx;
let mm;

function prefersReduced() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

function initStorefront() {
    // Curăță inițializarea anterioară (navigare Livewire / re-init).
    if (ctx) { ctx.revert(); ctx = null; }
    if (mm) { mm.revert(); mm = null; }

    const reduced = prefersReduced();

    ctx = gsap.context(() => {
        initCategories(reduced);
        initQuality(reduced);
        initCounters(reduced);
    });

    // „Cum lucrăm" — timeline responsiv (orizontal desktop / vertical mobil).
    // Reduced-motion: CSS pune stările finale, nu construim timeline.
    if (!reduced) {
        mm = gsap.matchMedia();
        mm.add('(min-width: 1024px)', () => buildProces('h'));
        mm.add('(max-width: 1023.98px)', () => buildProces('v'));
    }
}

/* ------------------------------------------------------------------ *
 * Iconițe categorii: scroll-in în „val" + idle subtil + hover micro.
 * ------------------------------------------------------------------ */
function initCategories(reduced) {
    const grid = document.querySelector('[data-cat-grid]');
    if (!grid) return;

    const cards = gsap.utils.toArray('[data-cat-card]', grid);

    if (reduced) return; // strokes deja desenate via CSS; fără idle/hover-motion.

    // Scroll-in în val: cardurile se desenează pe rând (stagger pe grilă).
    const tl = gsap.timeline({
        scrollTrigger: { trigger: grid, start: 'top 85%', once: true },
    });
    cards.forEach((card, i) => {
        const strokes = card.querySelectorAll('.cat-draw');
        tl.to(strokes, { strokeDashoffset: 0, duration: 0.6, ease: 'power1.inOut', stagger: 0.04 }, i * 0.08);
    });

    // Idle subtil, staggered (NU în unison — altfel pare „AI”/zgomotos).
    cards.forEach((card, i) => {
        const icon = card.querySelector('.cat-icon');
        if (!icon) return;
        gsap.to(icon, {
            y: -3,
            duration: 2.4 + (i % 4) * 0.35,
            ease: 'sine.inOut',
            yoyo: true,
            repeat: -1,
            delay: (i % 5) * 0.4,
        });
    });

    // Hover: re-joacă draw-on + un mic „hop” pe iconiță (micro-interacțiune).
    cards.forEach((card) => {
        const strokes = card.querySelectorAll('.cat-draw');
        const icon = card.querySelector('.cat-icon');
        card.addEventListener('mouseenter', () => {
            gsap.fromTo(strokes, { strokeDashoffset: 1 }, { strokeDashoffset: 0, duration: 0.5, ease: 'power1.inOut', stagger: 0.03 });
            if (icon) gsap.fromTo(icon, { scale: 0.9 }, { scale: 1, duration: 0.5, ease: 'back.out(3)', transformOrigin: '50% 50%' });
        });
    });
}

/* ------------------------------------------------------------------ *
 * „Cum lucrăm": linia se trasează + punct luminos călător; pașii „pop”
 * pe rând, sincron cu punctul. dir = 'h' (desktop) | 'v' (mobil).
 * ------------------------------------------------------------------ */
function buildProces(dir) {
    const section = document.querySelector('[data-proces]');
    if (!section) return;

    const steps = gsap.utils.toArray('[data-step]', section);
    if (!steps.length) return;

    const line = section.querySelector(dir === 'h' ? '[data-line-h]' : '[data-line-v]');
    const point = section.querySelector(dir === 'h' ? '[data-point-h]' : '[data-point-v]');
    const travelProp = dir === 'h' ? { left: '100%' } : { top: '100%' };

    // Stări inițiale ale pașilor (cercuri „dim”, label-uri ascunse).
    steps.forEach((s) => {
        gsap.set(s.querySelector('[data-step-circle]'), { scale: 0.6, opacity: 0.5 });
        gsap.set(s.querySelector('[data-step-label]'), { opacity: 0, y: 8 });
    });

    const T = 1.6; // durata călătoriei punctului
    const tl = gsap.timeline({ scrollTrigger: { trigger: section, start: 'top 65%', once: true } });

    if (point) gsap.set(point, { opacity: 1 });
    if (line) tl.to(line, { scaleX: dir === 'h' ? 1 : undefined, scaleY: dir === 'v' ? 1 : undefined, duration: T, ease: 'none' }, 0);
    if (point) tl.to(point, { ...travelProp, duration: T, ease: 'none' }, 0);

    steps.forEach((s, i) => {
        const t = (i / (steps.length - 1)) * T;
        tl.to(s.querySelector('[data-step-circle]'), { scale: 1, opacity: 1, duration: 0.4, ease: 'back.out(2)' }, t)
            .to(s.querySelectorAll('.step-draw'), { strokeDashoffset: 0, duration: 0.5, ease: 'power1.inOut' }, t)
            .to(s.querySelector('[data-step-label]'), { opacity: 1, y: 0, duration: 0.4 }, t + 0.05)
            .fromTo(s.querySelector('[data-step-pulse]'),
                { scale: 0.85, opacity: 0.6 },
                { scale: 1.5, opacity: 0, duration: 0.7, ease: 'power2.out' }, t);
    });

    // Punctul ajunge la final → se stinge discret.
    if (point) tl.to(point, { opacity: 0, duration: 0.3 }, T);
}

/* ------------------------------------------------------------------ *
 * „Calitate & materiale": iconițe material draw-on + underline + sweep.
 * ------------------------------------------------------------------ */
function initQuality(reduced) {
    const section = document.querySelector('[data-quality]');
    if (!section) return;

    if (reduced) return; // iconițe/underline deja la final via CSS; fără sweep.

    const tl = gsap.timeline({ scrollTrigger: { trigger: section, start: 'top 75%', once: true } });
    tl.to(section.querySelectorAll('.material-draw'), { strokeDashoffset: 0, duration: 0.7, ease: 'power1.inOut', stagger: 0.05 }, 0)
        .to(section.querySelector('[data-underline]'), { scaleX: 1, duration: 0.5, ease: 'power2.out' }, 0.3);

    // Sweep de finisaj — o singură trecere peste rândul de materiale.
    const sweep = section.querySelector('[data-quality-sweep]');
    const row = section.querySelector('[data-quality-row]');
    if (sweep && row) {
        ScrollTrigger.create({
            trigger: row,
            start: 'top 75%',
            once: true,
            onEnter: () => {
                gsap.set(sweep, { opacity: 1, xPercent: 0 });
                gsap.to(sweep, {
                    xPercent: 480,
                    duration: 1.1,
                    ease: 'power1.inOut',
                    onComplete: () => gsap.set(sweep, { opacity: 0 }),
                });
            },
        });
    }
}

/* ------------------------------------------------------------------ *
 * Contoare social proof — count-up la scroll.
 * ------------------------------------------------------------------ */
function initCounters(reduced) {
    gsap.utils.toArray('[data-countup]').forEach((el) => {
        const to = parseInt(el.dataset.countTo, 10) || 0;
        if (reduced) { el.textContent = to; return; }

        const obj = { val: 0 };
        ScrollTrigger.create({
            trigger: el,
            start: 'top 90%',
            once: true,
            onEnter: () => {
                gsap.to(obj, {
                    val: to,
                    duration: 1.4,
                    ease: 'power1.out',
                    onUpdate: () => { el.textContent = Math.round(obj.val); },
                });
            },
        });
    });
}

if (document.readyState !== 'loading') {
    initStorefront();
} else {
    document.addEventListener('DOMContentLoaded', initStorefront);
}
document.addEventListener('livewire:navigated', initStorefront);
