{{--
    Ilustrație hero: bancă „blueprint" desenată cu GSAP.
    Fiecare element are pathLength="1" + clasa .hero-draw (draw-on fără plugin).
    Decorativă → aria-hidden. Strokes pe accentul teal (currentColor via text-accent).
--}}
<svg viewBox="0 0 520 440" fill="none" aria-hidden="true"
     {{ $attributes->merge(['class' => 'h-full w-full text-accent']) }}>

    {{-- Inel „compas" decorativ (se rotește lent) --}}
    <g data-ring opacity="0.9">
        <circle cx="260" cy="215" r="192" stroke="currentColor" stroke-width="1" opacity="0.20" stroke-dasharray="2 9" />
        <circle cx="260" cy="215" r="150" stroke="currentColor" stroke-width="1" opacity="0.14" stroke-dasharray="1 7" />
        <path d="M260 18 L260 36 M260 394 L260 412 M63 215 L81 215 M439 215 L457 215"
              stroke="currentColor" stroke-width="1" opacity="0.20" />
    </g>

    {{-- Particule teal care driftează --}}
    <g fill="currentColor">
        <circle data-particle cx="92" cy="120" r="4" opacity="0.6" />
        <circle data-particle cx="430" cy="150" r="3" opacity="0.5" />
        <circle data-particle cx="78" cy="300" r="3" opacity="0.45" />
        <circle data-particle cx="450" cy="320" r="4" opacity="0.5" />
        <circle data-particle cx="260" cy="60" r="3" opacity="0.4" />
        <circle data-particle cx="160" cy="395" r="3" opacity="0.4" />
    </g>

    {{-- Banca (plutește în ansamblu) --}}
    <g data-float stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        {{-- montanți (back posts) --}}
        <path class="hero-draw" pathLength="1" d="M150 110 L150 250" />
        <path class="hero-draw" pathLength="1" d="M370 110 L370 250" />
        {{-- top rail spătar --}}
        <path class="hero-draw" pathLength="1" d="M138 108 Q260 86 382 108" />
        {{-- 3 slats spătar (sus → jos) --}}
        <path class="hero-draw" pathLength="1" d="M158 135 L362 135" />
        <path class="hero-draw" pathLength="1" d="M158 162 L362 162" />
        <path class="hero-draw" pathLength="1" d="M158 189 L362 189" />
        {{-- brațe --}}
        <path class="hero-draw" pathLength="1" d="M108 250 L108 205 Q108 196 120 196 L150 196" />
        <path class="hero-draw" pathLength="1" d="M412 250 L412 205 Q412 196 400 196 L370 196" />
        {{-- 3 slats șezut --}}
        <path class="hero-draw" pathLength="1" d="M108 250 L412 250" />
        <path class="hero-draw" pathLength="1" d="M108 265 L412 265" />
        <path class="hero-draw" pathLength="1" d="M108 280 L412 280" />
        {{-- picioare + traversă --}}
        <path class="hero-draw" pathLength="1" d="M150 280 L150 360" />
        <path class="hero-draw" pathLength="1" d="M370 280 L370 360" />
        <path class="hero-draw" pathLength="1" d="M120 250 L104 360" />
        <path class="hero-draw" pathLength="1" d="M400 250 L416 360" />
        <path class="hero-draw" pathLength="1" d="M150 330 L370 330" />
    </g>
</svg>
