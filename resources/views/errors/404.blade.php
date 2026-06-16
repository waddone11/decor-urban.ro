<x-layouts.storefront title="Pagina nu a fost găsită">
    <div class="mx-auto flex max-w-2xl flex-col items-center px-4 py-24 text-center sm:px-6">
        <p class="text-6xl font-bold text-accent">404</p>
        <h1 class="mt-4 text-2xl font-bold text-ink sm:text-3xl">Pagina nu a fost găsită</h1>
        <p class="mt-3 text-ink-soft">
            Linkul accesat nu mai există sau s-a mutat. Poate produsul a fost redenumit
            ori categoria reorganizată.
        </p>
        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
            <x-button :href="url('/')" variant="accent">Acasă</x-button>
            <x-button :href="route('catalog')" variant="outline">Vezi catalogul</x-button>
        </div>
    </div>
</x-layouts.storefront>
