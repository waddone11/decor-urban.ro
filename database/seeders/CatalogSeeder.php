<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Support\LegacyRedirects;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Populează catalogul din snapshot-ul JSON (database/data/catalog.json).
 * Idempotent (updateOrCreate după slug / (product_id, path)). Astfel
 * `migrate:fresh --seed` reconstruiește tot catalogul fără terminal/scrape pe prod.
 *
 * ⚠️ Fișierele imagine NU sunt în git — se urcă separat în
 *    storage/app/public/products/<slug>/. Seeder-ul populează doar rândurile.
 */
class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $path = config('catalog.snapshot_path');

        if (! $path || ! is_file($path)) {
            $this->command?->warn("CatalogSeeder: snapshot lipsă ({$path}) — sar peste.");

            return;
        }

        $data = json_decode((string) file_get_contents($path), true);
        if (! is_array($data) || empty($data['products'])) {
            $this->command?->warn('CatalogSeeder: snapshot invalid sau gol — sar peste.');

            return;
        }

        $hasSource = Schema::hasColumn('product_images', 'source');
        $hasThumbs = Schema::hasColumn('product_images', 'thumb_sm_path');

        // 1) Categorii (fără parent întâi, ca să existe înainte de legare).
        foreach ($data['categories'] ?? [] as $c) {
            Category::updateOrCreate(
                ['slug' => $c['slug']],
                [
                    'name' => $c['name'],
                    'description' => $c['description'] ?? null,
                    'intro' => $c['intro'] ?? null,
                    'sort_order' => $c['sort_order'] ?? 0,
                    'is_active' => $c['is_active'] ?? true,
                ],
            );
        }

        // 2) Parent-i (după ce toate categoriile există).
        foreach ($data['categories'] ?? [] as $c) {
            if (! empty($c['parent_slug'])) {
                $parent = Category::where('slug', $c['parent_slug'])->first();
                Category::where('slug', $c['slug'])->update(['parent_id' => $parent?->id]);
            }
        }

        // 3) Produse + pivot + imagini.
        foreach ($data['products'] as $p) {
            $product = Product::updateOrCreate(
                ['slug' => $p['slug']],
                [
                    'name' => $p['name'],
                    'code' => $p['code'] ?? null,
                    'description' => $p['description'] ?? null,
                    'specs' => $p['specs'] ?? null,
                    'legacy_description' => $p['legacy_description'] ?? null,
                    'description_draft' => $p['description_draft'] ?? null,
                    'description_source' => $p['description_source'] ?? 'legacy',
                    'price' => $p['price'] ?? null,
                    'sale_price' => $p['sale_price'] ?? null,
                    'price_on_request' => $p['price_on_request'] ?? true,
                    'quote_only' => $p['quote_only'] ?? true,
                    'feed_enabled' => $p['feed_enabled'] ?? false,
                    'available_seap' => $p['available_seap'] ?? false,
                    'cpv_code' => $p['cpv_code'] ?? null,
                    'availability' => $p['availability'] ?? null,
                    'is_active' => $p['is_active'] ?? true,
                    'sort_order' => $p['sort_order'] ?? 0,
                    'legacy_urls' => $p['legacy_urls'] ?? null,
                    'legacy_categories' => $p['legacy_categories'] ?? null,
                    'meta_title' => $p['meta_title'] ?? null,
                    'meta_description' => $p['meta_description'] ?? null,
                    'meta_keywords' => $p['meta_keywords'] ?? null,
                ],
            );

            // Pivot categorii (sync = idempotent).
            $sync = [];
            foreach ($p['categories'] ?? [] as $pc) {
                $cat = Category::where('slug', $pc['slug'])->first();
                if ($cat) {
                    $sync[$cat->id] = ['is_primary' => $pc['is_primary'] ?? false];
                }
            }
            $product->categories()->sync($sync);

            // Imagini (updateOrCreate pe (product_id, path)).
            foreach ($p['images'] ?? [] as $img) {
                $attrs = [
                    'alt' => $img['alt'] ?? null,
                    'sort_order' => $img['sort_order'] ?? 0,
                    'is_primary' => $img['is_primary'] ?? false,
                ];
                if ($hasSource && array_key_exists('source', $img)) {
                    $attrs['source'] = $img['source'];
                }
                if ($hasThumbs) {
                    $attrs['thumb_sm_path'] = $img['thumb_sm_path'] ?? null;
                    $attrs['thumb_md_path'] = $img['thumb_md_path'] ?? null;
                }

                ProductImage::updateOrCreate(
                    ['product_id' => $product->id, 'path' => $img['path']],
                    $attrs,
                );
            }
        }

        // Harta 301 depinde de legacy_urls — bustează cache-ul după reseed.
        LegacyRedirects::flush();

        $this->command?->info('CatalogSeeder: '.Category::count().' categorii, '.Product::count().' produse, '.ProductImage::count().' imagini.');
    }
}
