<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class ExportCatalogSnapshot extends Command
{
    protected $signature = 'catalog:export-snapshot {--path= : Cale de ieșire (implicit config catalog.snapshot_path)}';

    protected $description = 'Exportă catalogul (categorii + produse + pivot + imagini) în JSON pentru seeding pe prod';

    public function handle(): int
    {
        $hasSource = Schema::hasColumn('product_images', 'source');

        $categories = Category::query()->orderBy('sort_order')->orderBy('id')->get()
            ->map(fn (Category $c) => [
                'slug' => $c->slug,
                'name' => $c->name,
                'description' => $c->description,
                'intro' => $c->intro,
                'parent_slug' => $c->parent?->slug,
                'sort_order' => $c->sort_order,
                'is_active' => (bool) $c->is_active,
            ])->values();

        $products = Product::query()->with(['categories', 'images'])->orderBy('id')->get()
            ->map(function (Product $p) use ($hasSource) {
                return [
                    'slug' => $p->slug,
                    'name' => $p->name,
                    'code' => $p->code,
                    'description' => $p->description,
                    'specs' => $p->specs,
                    'legacy_description' => $p->legacy_description,
                    'description_draft' => $p->description_draft,
                    'description_source' => $p->description_source,
                    'price' => $p->price,
                    'price_on_request' => (bool) $p->price_on_request,
                    'availability' => $p->availability,
                    'is_active' => (bool) $p->is_active,
                    'sort_order' => $p->sort_order,
                    'legacy_urls' => $p->legacy_urls,
                    'legacy_categories' => $p->legacy_categories,
                    'meta_title' => $p->meta_title,
                    'meta_description' => $p->meta_description,
                    'meta_keywords' => $p->meta_keywords,
                    'categories' => $p->categories->map(fn ($c) => [
                        'slug' => $c->slug,
                        'is_primary' => (bool) $c->pivot->is_primary,
                    ])->values(),
                    'images' => $p->images->map(function ($img) use ($hasSource) {
                        $row = [
                            'path' => $img->path,
                            'alt' => $img->alt,
                            'sort_order' => $img->sort_order,
                            'is_primary' => (bool) $img->is_primary,
                        ];
                        if ($hasSource) {
                            $row['source'] = $img->source;
                        }

                        return $row;
                    })->values(),
                ];
            })->values();

        $data = [
            'exported_at' => now()->toAtomString(),
            'counts' => [
                'categories' => $categories->count(),
                'products' => $products->count(),
                'images' => $products->sum(fn ($p) => count($p['images'])),
            ],
            'categories' => $categories,
            'products' => $products,
        ];

        $path = $this->option('path') ?: config('catalog.snapshot_path');
        @mkdir(dirname($path), 0775, true);
        file_put_contents(
            $path,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n",
        );

        $this->info("Snapshot scris: {$path}");
        $this->line("  categorii: {$data['counts']['categories']}  produse: {$data['counts']['products']}  imagini: {$data['counts']['images']}");

        return self::SUCCESS;
    }
}
