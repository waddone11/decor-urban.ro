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

        tl.to('[data-eyebrow]', { opacity: 1, y: 0, duration: 0.4 })
            // Banca se desenează, element cu element (montanți → spătar → brațe → șezut → picioare).
            .to(paths, { strokeDashoffset: 0, duration: 0.9, stagger: 0.12, ease: 'power1.inOut' }, '-=0.1')
            .to('[data-word]', { opacity: 1, y: 0, duration: 0.5, stagger: 0.08 }, '-=0.5')
            .to('[data-lead]', { opacity: 1, y: 0, duration: 0.45 }, '-=0.2')
            .to('[data-cta]', { opacity: 1, y: 0, duration: 0.4, stagger: 0.1 }, '-=0.2')
            .to('[data-stat]', { opacity: 1, y: 0, duration: 0.4 }, '-=0.2')
            .add(startAmbient);

        revealSections(false);
    }, hero);
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

// Init la prima încărcare + la navigare Livewire (cu curățare ca să nu se dubleze).
if (document.readyState !== 'loading') {
    initHero();
} else {
    document.addEventListener('DOMContentLoaded', initHero);
}
document.addEventListener('livewire:navigated', initHero);
