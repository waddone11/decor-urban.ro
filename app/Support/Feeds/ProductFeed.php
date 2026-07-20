<?php

namespace App\Support\Feeds;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProductFeed
{
    public static function candidates(): Collection
    {
        return Product::query()
            ->active()
            ->with(['images', 'categories'])
            ->ordered()
            ->get();
    }

    public static function eligibleProducts(): Collection
    {
        return self::candidates()
            ->filter(fn (Product $product): bool => self::exclusionReasons($product) === [])
            ->values();
    }

    public static function forgetCache(): void
    {
        Cache::forget('feeds.google-merchant');
        Cache::forget('feeds.meta-catalog');
        Cache::forget('feeds.google-business-products');
        Cache::forget('feeds.exclusion-report');
    }

    public static function exclusionReport(): array
    {
        return self::candidates()
            ->mapWithKeys(fn (Product $product): array => [
                $product->id => [
                    'name' => $product->name,
                    'code' => $product->code,
                    'reasons' => self::exclusionReasons($product),
                ],
            ])
            ->filter(fn (array $row): bool => $row['reasons'] !== [])
            ->all();
    }

    public static function summary(): array
    {
        $included = self::eligibleProducts()->count();
        $excluded = self::exclusionReport();

        return [
            'included' => $included,
            'excluded' => count($excluded),
            'reasons' => $excluded,
        ];
    }

    public static function summaryText(string $label): string
    {
        $summary = self::summary();
        $lines = [
            $label,
            'Produse incluse: '.$summary['included'],
            'Produse excluse: '.$summary['excluded'],
        ];

        foreach ($summary['reasons'] as $id => $row) {
            $lines[] = '- #'.$id.' '.$row['name'].' ('.$row['code'].'): '.implode('; ', $row['reasons']);
        }

        return implode("\n", $lines)."\n";
    }

    public static function exclusionReasons(Product $product): array
    {
        $reasons = [];

        if (! $product->is_active) {
            $reasons[] = 'produs inactiv/draft';
        }
        if (! $product->feed_enabled) {
            $reasons[] = 'feed_enabled este dezactivat';
        }
        if ($product->quote_only || $product->price_on_request) {
            $reasons[] = 'produs la cerere';
        }
        if ($product->price === null || (float) $product->price <= 0) {
            $reasons[] = 'preț lipsă sau invalid';
        }
        if (! $product->availability) {
            $reasons[] = 'disponibilitate lipsă';
        }
        if (! $product->primaryImage()) {
            $reasons[] = 'imagine principală lipsă';
        }
        if (! $product->name) {
            $reasons[] = 'titlu lipsă';
        }
        if (! $product->description) {
            $reasons[] = 'descriere lipsă';
        }

        return $reasons;
    }

    public static function googleXml(): string
    {
        return Cache::remember('feeds.google-merchant', now()->addHour(), function (): string {
            $items = self::eligibleProducts();
            Log::info('Google Merchant feed generated', ['included' => $items->count(), 'excluded' => count(self::exclusionReport())]);

            $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rss version="2.0" xmlns:g="http://base.google.com/ns/1.0"><channel></channel></rss>');
            $channel = $xml->channel;
            $channel->addChild('title', config('business.name'));
            $channel->addChild('link', url('/'));
            $channel->addChild('description', 'Produse Decor Urban eligibile pentru Google Merchant Center');

            foreach ($items as $product) {
                $item = $channel->addChild('item');
                self::addGoogle($item, 'id', (string) $product->feedId());
                self::addGoogle($item, 'title', $product->name);
                self::addGoogle($item, 'description', strip_tags($product->description));
                self::addGoogle($item, 'link', route('product', $product->slug));
                self::addGoogle($item, 'image_link', $product->primaryImage()->url());

                foreach ($product->galleryImages()->skip(1)->take(10) as $image) {
                    self::addGoogle($item, 'additional_image_link', $image->url());
                }

                self::addGoogle($item, 'availability', $product->availability);
                self::addGoogle($item, 'price', number_format((float) $product->price, 2, '.', '').' '.$product->currency);
                if ($product->sale_price && (float) $product->sale_price < (float) $product->price) {
                    self::addGoogle($item, 'sale_price', number_format((float) $product->sale_price, 2, '.', '').' '.$product->currency);
                }
                self::addGoogle($item, 'condition', $product->condition ?: 'new');
                self::addGoogle($item, 'brand', $product->brand ?: config('business.name'));
                $mpn = $product->mpn ?: ($product->code ? ltrim($product->code, '#') : null);
                if ($mpn) {
                    self::addGoogle($item, 'mpn', $mpn);
                }
                if ($product->gtin) {
                    self::addGoogle($item, 'gtin', $product->gtin);
                }
                self::addGoogle($item, 'identifier_exists', $product->gtin || $mpn ? 'yes' : 'no');
                if ($product->google_product_category) {
                    self::addGoogle($item, 'google_product_category', $product->google_product_category);
                }
                self::addGoogle($item, 'product_type', $product->primaryCategory()?->name ?? $product->categories->first()?->name ?? 'Mobilier urban');
                if ($product->shipping_weight) {
                    self::addGoogle($item, 'shipping_weight', $product->shipping_weight);
                }
                foreach (range(0, 4) as $i) {
                    $field = 'custom_label_'.$i;
                    if ($product->{$field}) {
                        self::addGoogle($item, $field, $product->{$field});
                    }
                }
            }

            return $xml->asXML();
        });
    }

    public static function metaCsv(): string
    {
        return Cache::remember('feeds.meta-catalog', now()->addHour(), function (): string {
            $headers = ['id', 'title', 'description', 'availability', 'condition', 'price', 'link', 'image_link', 'additional_image_link', 'brand', 'google_product_category', 'fb_product_category', 'sale_price', 'custom_label_0', 'custom_label_1', 'custom_label_2', 'custom_label_3', 'custom_label_4'];
            $handle = fopen('php://temp', 'r+');
            fputcsv($handle, $headers);

            foreach (self::eligibleProducts() as $product) {
                fputcsv($handle, [
                    $product->feedId(),
                    $product->name,
                    strip_tags($product->description),
                    $product->availability,
                    $product->condition ?: 'new',
                    number_format((float) $product->price, 2, '.', '').' '.$product->currency,
                    route('product', $product->slug),
                    $product->primaryImage()->url(),
                    $product->galleryImages()->skip(1)->pluck('path')->isNotEmpty()
                        ? $product->galleryImages()->skip(1)->map->url()->implode(',')
                        : '',
                    $product->brand ?: config('business.name'),
                    $product->google_product_category,
                    $product->facebook_product_category,
                    $product->sale_price && (float) $product->sale_price < (float) $product->price
                        ? number_format((float) $product->sale_price, 2, '.', '').' '.$product->currency
                        : '',
                    $product->custom_label_0,
                    $product->custom_label_1,
                    $product->custom_label_2,
                    $product->custom_label_3,
                    $product->custom_label_4,
                ]);
            }

            rewind($handle);

            return stream_get_contents($handle);
        });
    }

    public static function googleBusinessProductsCsv(): string
    {
        return Cache::remember('feeds.google-business-products', now()->addHour(), function (): string {
            $handle = fopen('php://temp', 'r+');
            fputcsv($handle, ['name', 'category', 'short_description', 'product_url', 'image_url', 'display_price', 'product_code']);

            Product::query()->active()->where('show_in_google_business', true)->with(['images', 'categories'])->ordered()->get()
                ->each(function (Product $product) use ($handle): void {
                    fputcsv($handle, [
                        $product->name,
                        $product->primaryCategory()?->name ?? $product->categories->first()?->name,
                        (string) str($product->description ?: $product->seoDescription())->stripTags()->squish()->limit(300),
                        route('product', $product->slug),
                        $product->primaryImage()?->url(),
                        $product->price_on_request || ! $product->price ? '' : number_format((float) $product->price, 2, '.', '').' '.$product->currency,
                        $product->code,
                    ]);
                });

            rewind($handle);

            return stream_get_contents($handle);
        });
    }

    public static function exclusionReportCsv(): string
    {
        return Cache::remember('feeds.exclusion-report', now()->addHour(), function (): string {
            $handle = fopen('php://temp', 'r+');
            fputcsv($handle, ['product_id', 'name', 'code', 'reasons']);

            foreach (self::exclusionReport() as $id => $row) {
                fputcsv($handle, [$id, $row['name'], $row['code'], implode('; ', $row['reasons'])]);
            }

            rewind($handle);

            return stream_get_contents($handle);
        });
    }

    private static function addGoogle(\SimpleXMLElement $item, string $name, string $value): void
    {
        $item->addChild('g:'.$name, htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8'), 'http://base.google.com/ns/1.0');
    }
}
