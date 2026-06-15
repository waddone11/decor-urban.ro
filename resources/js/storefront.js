import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

let ctx;

function initStorefront() {
    // Curăță inițializarea anterioară (navigare Livewire / re-init).
    if (ctx) {
        ctx.revert();
        ctx = null;
    }

    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    ctx = gsap.context(() => {
        // 1. Iconițe categorii: draw-on la intrarea în viewport, cu stagger pe fiecare card.
        gsap.utils.toArray('[data-draw-on]').forEach((card) => {
            const strokes = card.querySelectorAll('.cat-draw');
            if (!strokes.length) return;

            if (prefersReduced) {
                gsap.set(strokes, { strokeDashoffset: 0 });
                return;
            }

            gsap.set(strokes, { strokeDashoffset: 1 });
            gsap.to(strokes, {
                strokeDashoffset: 0,
                duration: 0.7,
                ease: 'power1.inOut',
                stagger: 0.06,
                scrollTrigger: { trigger: card, start: 'top 88%', once: true },
            });
        });

        // 2. Linia procesului „Cum lucrăm" — se trasează (scaleX 0 → 1) la scroll.
        gsap.utils.toArray('[data-process] .process-line').forEach((line) => {
            if (prefersReduced) {
                gsap.set(line, { scaleX: 1 });
                return;
            }
            gsap.set(line, { scaleX: 0 });
            gsap.to(line, {
                scaleX: 1,
                duration: 1,
                ease: 'power2.out',
                scrollTrigger: { trigger: line.closest('[data-process]'), start: 'top 80%', once: true },
            });
        });

        // 3. Contoare social proof — count-up la scroll.
        gsap.utils.toArray('[data-countup]').forEach((el) => {
            const to = parseInt(el.dataset.countTo, 10) || 0;

            if (prefersReduced) {
                el.textContent = to;
                return;
            }

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
    });
}

if (document.readyState !== 'loading') {
    initStorefront();
} else {
    document.addEventListener('DOMContentLoaded', initStorefront);
}
document.addEventListener('livewire:navigated', initStorefront);
