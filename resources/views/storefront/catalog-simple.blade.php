<x-layouts.storefront title="Catalog">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <x-storefront.breadcrumb :items="[
            ['label' => 'Acasă', 'url' => url('/')],
            ['label' => 'Catalog'],
        ]" class="mb-6" />

        <h1 class="text-2xl font-bold text-ink sm:text-3xl">Catalog produse</h1>
        <p class="mt-1 text-ink-soft">Toate produsele noastre de mobilier urban și stradal.</p>

        <div class="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($products as $product)
                <x-product-card :product="$product" :href="route('product', $product->slug)" />
            @endforeach
        </div>

        <div class="mt-8">
            {{ $products->links() }}
        </div>
    </div>
</x-layouts.storefront>
