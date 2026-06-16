<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * Constructori JSON-LD (schema.org) pentru storefront.
 * Fără preț fals: la `price_on_request` nu emitem `price` în Offer.
 */
class JsonLd
{
    /**
     * @param  array<int, array{name: string, url?: string}>  $items  pașii breadcrumb, în ordine
     */
    public static function breadcrumb(array $items): array
    {
        $elements = [];
        foreach (array_values($items) as $i => $item) {
            $element = [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $item['name'],
            ];
            if (! empty($item['url'])) {
                $element['item'] = $item['url'];
            }
            $elements[] = $element;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $elements,
        ];
    }

    /**
     * @param  Collection<int, Product>  $products
     */
    public static function itemList(Collection $products, ?string $name = null): array
    {
        $elements = $products->values()->map(fn (Product $p, int $i) => [
            '@type' => 'ListItem',
            'position' => $i + 1,
            'url' => route('product', $p->slug),
            'name' => $p->name,
        ])->all();

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => $name,
            'numberOfItems' => count($elements),
            'itemListElement' => $elements,
        ]);
    }

    public static function product(Product $product): array
    {
        $images = $product->galleryImages()->map(fn ($img) => $img->url())->values()->all();
        $primaryCategory = $product->primaryCategory() ?? $product->categories->first();

        $hasRealPrice = ! $product->price_on_request && $product->price;

        $offer = array_filter([
            '@type' => 'Offer',
            'url' => route('product', $product->slug),
            'priceCurrency' => 'RON',
            'availability' => $hasRealPrice
                ? 'https://schema.org/InStock'
                : 'https://schema.org/PreOrder',
            // Preț DOAR dacă e real și nu „la cerere" — niciun preț fals.
            'price' => $hasRealPrice ? number_format((float) $product->price, 2, '.', '') : null,
        ]);

        // additionalProperty din specs (doar câmpurile prezente) + material.
        $specs = $product->displaySpecs();
        $additional = [];
        foreach ($specs as $label => $value) {
            $additional[] = ['@type' => 'PropertyValue', 'name' => $label, 'value' => $value];
        }
        $material = $product->materialLabel();

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'image' => $images ?: null,
            'sku' => $product->code ? ltrim($product->code, '#') : null,
            'description' => $product->seoDescription(),
            'material' => $material,
            'brand' => [
                '@type' => 'Brand',
                'name' => config('contact.brand'),
            ],
            'category' => $primaryCategory?->name,
            'additionalProperty' => $additional ?: null,
            'offers' => $offer,
        ]);
    }
}
