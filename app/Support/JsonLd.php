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
    public static function business(): array
    {
        $geo = null;
        if (config('business.latitude') && config('business.longitude')) {
            $geo = [
                '@type' => 'GeoCoordinates',
                'latitude' => config('business.latitude'),
                'longitude' => config('business.longitude'),
            ];
        }

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => ['Organization', 'LocalBusiness'],
            '@id' => url('/#business'),
            'name' => config('business.name'),
            'legalName' => config('business.legal_name'),
            'url' => url('/'),
            'logo' => asset('images/logo.svg'),
            'image' => asset('images/logo.svg'),
            'description' => 'Producător și furnizor direct de mobilier urban și stradal pentru spații publice, instituții, firme și proiecte private.',
            'telephone' => config('business.phone'),
            'email' => config('business.email'),
            'taxID' => config('business.vat_number'),
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => 'Str. Băltați nr. 149',
                'addressLocality' => 'Scornicești',
                'addressRegion' => 'Olt',
                'addressCountry' => 'RO',
            ],
            'geo' => $geo,
            'areaServed' => [
                '@type' => 'Country',
                'name' => 'România',
            ],
            'hasMap' => config('business.google_maps_url'),
            'sameAs' => Business::sameAs(),
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'contactType' => 'sales',
                'telephone' => config('business.phone'),
                'email' => config('business.email'),
                'areaServed' => 'RO',
                'availableLanguage' => 'Romanian',
            ],
            'makesOffer' => [
                '@type' => 'OfferCatalog',
                'name' => 'Mobilier urban și stradal la comandă',
            ],
        ]);
    }

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
            'manufacturer' => [
                '@type' => 'Organization',
                'name' => config('business.name'),
            ],
            'category' => $primaryCategory?->name,
            'url' => route('product', $product->slug),
            'mpn' => $product->mpn ?: ($product->code ? ltrim($product->code, '#') : null),
            'additionalProperty' => $additional ?: null,
        ]);
    }
}
