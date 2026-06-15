<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Database\Seeders\CategorySeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ImportLegacy extends Command
{
    protected $signature = 'import:legacy {--fresh : Golește tabelele catalog + re-seed categorii înainte de import}';

    protected $description = 'Importă catalogul scrape-uit (storage/scrape) în DB, mapat pe categoriile noi';

    public function handle(): int
    {
        $jsonPath = storage_path('scrape/products.json');
        if (! File::exists($jsonPath)) {
            $this->error("Nu găsesc {$jsonPath}. Rulează mai întâi scraper-ul (Faza 1).");

            return self::FAILURE;
        }

        if ($this->option('fresh')) {
            $this->warn('--fresh: golesc tabelele catalog…');
            $this->truncateCatalog();
        }

        // Asigură categoriile noi (idempotent).
        $this->call('db:seed', ['--class' => CategorySeeder::class, '--no-interaction' => true]);

        $map = config('legacy_category_map.source_map');
        $overrides = config('legacy_category_map.product_overrides', []);
        $products = json_decode(File::get($jsonPath), true, 512, JSON_THROW_ON_ERROR);
        $this->info('Importez '.count($products).' produse…');

        $categoriesBySlug = Category::all()->keyBy('slug');
        $bar = $this->output->createProgressBar(count($products));
        $bar->start();

        $stats = ['products' => 0, 'images' => 0, 'pivot' => 0, 'overridden' => 0, 'unmapped' => []];

        foreach ($products as $p) {
            $product = $this->importProduct($p);
            $stats['products']++;

            // ---- Categorii: mapare source → noi, dedup, sync pivot ----
            $newSlugs = [];
            foreach (($p['source_categories'] ?? []) as $sc) {
                $mapped = $map[$sc['name']] ?? [];
                if (empty($mapped)) {
                    $stats['unmapped'][$sc['name']] = true;
                }
                foreach ($mapped as $slug) {
                    if (! in_array($slug, $newSlugs, true)) {
                        $newSlugs[] = $slug;
                    }
                }
            }

            // Override per produs (după slug): înlocuiește complet maparea pe sursă.
            $isOverride = isset($overrides[$p['slug']]);
            if ($isOverride) {
                $newSlugs = array_values($overrides[$p['slug']]);
                $stats['overridden']++;
            }
            if (empty($newSlugs)) {
                $newSlugs = ['diverse-custom'];
            }

            // Categoria principală: la override = prima din listă; altfel prima
            // mapată care NU e diverse-custom, altfel diverse-custom.
            $primarySlug = $isOverride
                ? $newSlugs[0]
                : (collect($newSlugs)->first(fn ($s) => $s !== 'diverse-custom') ?? 'diverse-custom');

            $syncData = [];
            foreach ($newSlugs as $slug) {
                $cat = $categoriesBySlug->get($slug);
                if (! $cat) {
                    $this->warn("  Slug categorie nouă inexistent: {$slug}");

                    continue;
                }
                $syncData[$cat->id] = ['is_primary' => $slug === $primarySlug];
            }
            $product->categories()->sync($syncData);
            $stats['pivot'] += count($syncData);

            // ---- Imagini ----
            $stats['images'] += $this->importImages($product, $p['images'] ?? []);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('Gata.');
        $this->line("  Produse:        {$stats['products']}");
        $this->line("  Imagini:        {$stats['images']}");
        $this->line("  Rânduri pivot:  {$stats['pivot']}");
        $this->line("  Cu override:    {$stats['overridden']}");
        if (! empty($stats['unmapped'])) {
            $this->warn('  Categorii sursă NEMAPATE: '.implode(', ', array_keys($stats['unmapped'])));
        }

        return self::SUCCESS;
    }

    private function importProduct(array $p): Product
    {
        $description = $p['description'] ?? null;
        if (is_string($description) && trim($description) === '') {
            $description = null;
        }

        return Product::updateOrCreate(
            ['slug' => $p['slug']],
            [
                'name' => $p['name'] ?? $p['slug'],
                'code' => $p['code'] ?? null,
                'description' => $description,
                'price' => null,
                'price_on_request' => true,
                'availability' => $p['availability'] ?? null,
                'legacy_urls' => $p['source_urls'] ?? array_filter([$p['source_url'] ?? null]),
                'legacy_categories' => collect($p['source_categories'] ?? [])->pluck('name')->all(),
                'meta_title' => $p['meta']['title'] ?? null,
                'meta_description' => $p['meta']['description'] ?? null,
                'meta_keywords' => $p['meta']['keywords'] ?? null,
            ],
        );
    }

    /**
     * Copiază imaginile din storage/scrape în disk-ul public și creează rândurile
     * product_images (idempotent — nu dublează, nu re-copiază dacă există).
     *
     * @param  array<int, string>  $images  căi relative la storage/scrape (ex. images/<slug>/1.jpg)
     */
    private function importImages(Product $product, array $images): int
    {
        $disk = Storage::disk('public');
        $count = 0;

        foreach (array_values($images) as $i => $relPath) {
            $source = storage_path('scrape/'.$relPath);
            if (! File::exists($source)) {
                $this->warn("  Imagine lipsă pe disk: {$source}");

                continue;
            }

            $ext = pathinfo($relPath, PATHINFO_EXTENSION) ?: 'jpg';
            $destRel = "products/{$product->slug}/".($i + 1).".{$ext}";

            if (! $disk->exists($destRel)) {
                $disk->makeDirectory("products/{$product->slug}");
                $disk->put($destRel, File::get($source));
            }

            ProductImage::updateOrCreate(
                ['product_id' => $product->id, 'path' => $destRel],
                [
                    'alt' => $product->name,
                    'sort_order' => $i,
                    'is_primary' => $i === 0,
                ],
            );
            $count++;
        }

        return $count;
    }

    private function truncateCatalog(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('category_product')->truncate();
        ProductImage::truncate();
        Product::truncate();
        Category::truncate();
        Schema::enableForeignKeyConstraints();
    }
}
