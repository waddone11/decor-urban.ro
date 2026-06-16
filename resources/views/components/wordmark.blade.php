{{--
    Wordmark „Decor Urban.ro" — un singur loc de adevăr (nav + footer).
    Font: token --font-logo (vezi resources/css/app.css → comutabil de owner).
    Tratament: „Decor" ink · „Urban" teal · „.ro" teal, mai mic.
    Mărimea se dă din afară (ex. class="text-xl").
--}}
<span {{ $attributes->merge(['class' => 'font-logo font-bold tracking-[-0.02em] leading-none']) }}>
    <span class="text-ink">Decor</span> <span class="text-accent">Urban</span><span class="text-accent text-[0.6em] align-top font-bold">.ro</span>
</span>
