import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

let ctx;

function initHero() {
    const hero = document.querySelector('#hero');
    if (!hero) return;

    // Curăță o eventuală inițializare anterioară (navigare Livewire / re-init).
    if (ctx) {
        ctx.revert();
        ctx = null;
    }

    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    ctx = gsap.context(() => {
        const paths = gsap.utils.toArray('.hero-draw');

        // Reduced motion: direct pe starea finală, fără mișcare.
        if (prefersReduced) {
            gsap.set(paths, { strokeDashoffset: 0 });
            gsap.set('.hero-reveal', { opacity: 1, y: 0 });
            revealSections(true);
            return;
        }

        // Stări inițiale.
        gsap.set(paths, { strokeDashoffset: 1 });
        gsap.set('.hero-reveal', { opacity: 0, y: 18 });

        const tl = gsap.timeline({ defaults: { ease: 'power2.out' } });

        // Linia 1 a titlului („Mobilier urban") e vizibilă din load (fără hero-reveal).
        tl.to('[data-eyebrow]', { opacity: 1, y: 0, duration: 0.4 })
            // Banca se desenează, element cu element (montanți → spătar → brațe → șezut → picioare).
            .to(paths, { strokeDashoffset: 0, duration: 0.9, stagger: 0.12, ease: 'power1.inOut' }, '-=0.1')
            .to('[data-lead]', { opacity: 1, y: 0, duration: 0.45 }, '-=0.4')
            .to('[data-cta]', { opacity: 1, y: 0, duration: 0.4, stagger: 0.1 }, '-=0.2')
            .to('[data-stat]', { opacity: 1, y: 0, duration: 0.4 }, '-=0.2')
            .add(startAmbient);

        // Linia 2 („care durează." + underline) se revelează separat: ScrollTrigger
        // care se joacă și pe load dacă hero-ul e deja în viewport (cazul normal).
        revealHeroLine2(hero);

        revealSections(false);
    }, hero);
}

// Linia 2 a titlului: fade-up + underline trasat, declanșat la intrarea în viewport.
function revealHeroLine2(hero) {
    const line2 = hero.querySelector('[data-hero-line2]');
    if (!line2) return;

    ScrollTrigger.create({
        trigger: line2,
        start: 'top 92%',
        once: true,
        onEnter: () => {
            const utl = gsap.timeline({ defaults: { ease: 'power2.out' } });
            utl.to(line2, { opacity: 1, y: 0, duration: 0.55 });
            const underline = line2.querySelector('[data-hero-underline]');
            if (underline) utl.to(underline, { scaleX: 1, duration: 0.45 }, '-=0.15');
        },
    });
}

// Mișcare ambientală în buclă: banca plutește, inelul se rotește, particulele driftează.
function startAmbient() {
    gsap.to('[data-float]', {
        y: -10,
        duration: 3.2,
        ease: 'sine.inOut',
        yoyo: true,
        repeat: -1,
    });
    gsap.to('[data-ring]', {
        rotation: 360,
        transformOrigin: '50% 50%',
        duration: 70,
        ease: 'none',
        repeat: -1,
    });
    gsap.utils.toArray('[data-particle]').forEach((p, i) => {
        gsap.to(p, {
            y: '-=12',
            opacity: 0.35,
            duration: 3 + (i % 3),
            ease: 'sine.inOut',
            yoyo: true,
            repeat: -1,
            delay: i * 0.4,
        });
    });
}

// Secțiunile de sub hero apar cu fade-up la intrarea în viewport.
function revealSections(instant) {
    gsap.utils.toArray('[data-scroll-reveal]').forEach((el) => {
        if (instant) {
            gsap.set(el, { opacity: 1, y: 0 });
            return;
        }
        gsap.from(el, {
            opacity: 0,
            y: 32,
            duration: 0.7,
            ease: 'power2.out',
            scrollTrigger: {
                trigger: el,
                start: 'top 85%',
                toggleActions: 'play none none none',
            },
        });
    });
}

// Init la prima încărcare + la navigare Livewire. Dedup: `livewire:navigated` se
// emite și la boot (după DOMContentLoaded) → ignorăm prima emisie ca să nu re-rulăm
// (și re-anima) hero-ul de două ori. Navigările SPA ulterioare re-inițializează.
let heroBooted = false;
function bootHero() {
    heroBooted = true;
    initHero();
}
if (document.readyState !== 'loading') {
    bootHero();
} else {
    document.addEventListener('DOMContentLoaded', bootHero);
}
document.addEventListener('livewire:navigated', () => {
    if (heroBooted) { heroBooted = false; return; }
    initHero();
});
