<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CatalogSummary extends Command
{
    protected $signature = 'catalog:summary';

    protected $description = 'Sumar catalog: produse, imagini, produse per categorie, anomalii sursă';

    public function handle(): int
    {
        $this->info('=== Sumar catalog ===');
        $this->line('Produse total:        '.Product::count());
        $this->line('Imagini total:        '.ProductImage::count());
        $this->line('Categorii:            '.Category::count());
        $this->line('Rânduri pivot:        '.DB::table('category_product')->count());
        $this->newLine();

        $this->info('Produse per categorie nouă:');
        $rows = Category::query()
            ->orderBy('sort_order')
            ->withCount('products')
            ->get()
            ->map(fn (Category $c) => [$c->name, $c->slug, $c->products_count])
            ->all();
        $this->table(['Categorie', 'Slug', 'Produse'], $rows);

        // ---- diverse-custom: ce a aterizat acolo (candidați re-clasare) ----
        $diverse = Category::where('slug', 'diverse-custom')->first();
        $this->newLine();
        $this->info('În „Diverse & custom" ('.($diverse?->products()->count() ?? 0).' produse) — candidați re-clasare:');
        if ($diverse) {
            foreach ($diverse->products()->orderBy('code')->get() as $p) {
                $this->line("  - {$p->code}  {$p->name}");
            }
        }

        // ---- produse fără descriere ----
        $noDesc = Product::whereNull('description')->orWhere('description', '')->get();
        $this->newLine();
        $this->info('Produse fără descriere: '.$noDesc->count());
        foreach ($noDesc as $p) {
            $this->line("  - {$p->code}  {$p->name}");
        }

        // ---- coduri duplicate pe produse distincte ----
        $this->newLine();
        $this->info('Coduri duplicate pe produse distincte (codul NU e cheie unică):');
        $dupCodes = Product::query()
            ->select('code', DB::raw('COUNT(*) as c'))
            ->whereNotNull('code')
            ->groupBy('code')
            ->having('c', '>', 1)
            ->pluck('c', 'code');
        if ($dupCodes->isEmpty()) {
            $this->line('  (niciunul)');
        } else {
            foreach ($dupCodes as $code => $c) {
                $slugs = Product::where('code', $code)->pluck('slug')->implode(', ');
                $this->line("  - {$code} ({$c}): {$slugs}");
            }
        }

        // ---- produse cross-listate (în ≥2 categorii) ----
        $this->newLine();
        $crossListed = Product::query()
            ->has('categories', '>', 1)
            ->orderBy('code')
            ->get();
        $this->info('Produse cross-listate (în ≥2 categorii): '.$crossListed->count());
        foreach ($crossListed as $p) {
            $cats = $p->categories->pluck('slug')->implode(', ');
            $this->line("  - {$p->code}  {$p->name}  →  {$cats}");
        }

        return self::SUCCESS;
    }
}
