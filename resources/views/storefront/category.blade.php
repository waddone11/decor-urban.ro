<x-layouts.storefront :title="$category->seoTitle()" :description="$category->seoDescription()" :og-image="$ogImage">
    @foreach ($jsonLd as $ld)
        <x-seo.jsonld :data="$ld" />
    @endforeach

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <x-storefront.breadcrumb :items="[
            ['label' => 'Acasă', 'url' => url('/')],
            ['label' => 'Categorii', 'url' => route('catalog')],
            ['label' => $category->name],
        ]" class="mb-6" />

        <header class="flex items-start gap-4 border-b border-line pb-6">
            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-card bg-tint-sky text-accent">
                <x-category-icon :slug="$category->slug" class="h-8 w-8" />
            </span>
            <div>
                <h1 class="text-2xl font-bold text-ink sm:text-3xl">{{ $category->name }}</h1>
                @if ($category->description)
                    <p class="mt-1 max-w-2xl text-ink-soft">{{ $category->description }}</p>
                @endif
                <p class="mt-1 text-sm text-ink-muted">{{ $count }} {{ $count === 1 ? 'produs' : 'produse' }}</p>
            </div>
        </header>

        @if ($category->intro)
            {{-- Intro SEO (conținut îmbogățit, crawlabil) --}}
            <p class="mt-6 max-w-7xl text-sm text-ink-soft leading-relaxed">{{ $category->intro }}</p>
        @endif

        @if ($products->isEmpty())
            <p class="py-16 text-center text-ink-muted">Nu există produse în această categorie momentan.</p>
        @else
            {{-- Sortare (GET, query-string linkabil) --}}
            <form method="get" class="mt-6 flex items-center justify-end gap-2">
                <label for="sort" class="text-sm text-ink-muted">Sortează:</label>
                <select id="sort" name="sort" onchange="this.form.submit()"
                        class="rounded-button border border-line bg-white py-1.5 pl-3 pr-8 text-sm text-ink focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent">
                    <option value="recomandate" @selected($sort === 'recomandate')>Recomandate</option>
                    <option value="nume" @selected($sort === 'nume')>Nume A–Z</option>
                    <option value="cod" @selected($sort === 'cod')>Cod</option>
                </select>
            </form>

            <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($products as $product)
                    <x-product-card :product="$product" :href="route('product', $product->slug)" />
                @endforeach
            </div>

            <div class="mt-8">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</x-layouts.storefront>
