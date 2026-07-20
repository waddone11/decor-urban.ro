<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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
            'offers' => self::offer($product),
            'aggregateRating' => self::aggregateRating($product),
            'review' => self::reviews($product),
        ]);
    }

    /**
     * AggregateRating DOAR din recenzii reale aprobate, afișate pe pagină.
     * 0 recenzii → null (fără rating inventat). Nu se pune niciodată pe
     * Organization/LocalBusiness (self-serving) — doar aici, pe Product.
     */
    private static function aggregateRating(Product $product): ?array
    {
        $stats = $product->reviews()->approved()
            ->selectRaw('COUNT(*) as cnt, AVG(rating) as avg_rating')
            ->toBase()
            ->first();

        if (! $stats || (int) $stats->cnt === 0) {
            return null;
        }

        return [
            '@type' => 'AggregateRating',
            'ratingValue' => round((float) $stats->avg_rating, 1),
            'reviewCount' => (int) $stats->cnt,
            'bestRating' => 5,
            'worstRating' => 1,
        ];
    }

    /** Recenziile reale aprobate (cele afișate pe pagină), max 10 în schema. */
    private static function reviews(Product $product): ?array
    {
        $reviews = $product->approvedReviews()->take(10)->get();
        if ($reviews->isEmpty()) {
            return null;
        }

        return $reviews->map(fn ($r) => array_filter([
            '@type' => 'Review',
            'author' => ['@type' => 'Person', 'name' => $r->author_name],
            'datePublished' => $r->created_at->toDateString(),
            'name' => $r->title,
            'reviewBody' => $r->body,
            'reviewRating' => [
                '@type' => 'Rating',
                'ratingValue' => $r->rating,
                'bestRating' => 5,
                'worstRating' => 1,
            ],
        ]))->values()->all();
    }

    /** Offer DOAR pentru produse cu preț real (vezi nota „fără preț fals" de sus). */
    private static function offer(Product $product): ?array
    {
        if ($product->isPriceOnRequest()) {
            return null;
        }

        return array_filter([
            '@type' => 'Offer',
            'url' => route('product', $product->slug),
            'price' => number_format((float) $product->currentPrice(), 2, '.', ''),
            'priceCurrency' => $product->currency ?: 'RON',
            'availability' => self::schemaAvailability($product->availability),
        ]);
    }

    /** Disponibilitatea liberă din DB → valoare schema.org; null dacă nu putem mapa. */
    private static function schemaAvailability(?string $availability): ?string
    {
        $a = Str::lower(Str::ascii((string) $availability));

        return match (true) {
            str_contains($a, 'out of stock') || str_contains($a, 'fara stoc') => 'https://schema.org/OutOfStock',
            str_contains($a, 'preorder') || str_contains($a, 'precomanda') => 'https://schema.org/PreOrder',
            str_contains($a, 'la comanda') || str_contains($a, 'backorder') => 'https://schema.org/BackOrder',
            str_contains($a, 'stoc') || str_contains($a, 'in stock') => 'https://schema.org/InStock',
            default => null,
        };
    }
}
