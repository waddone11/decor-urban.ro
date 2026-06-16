<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;

class EnrichReview extends Command
{
    protected $signature = 'catalog:enrich-review {--samples=4 : Eșantion per categorie}';

    protected $description = 'Generează storage/enrich/review.html: înainte→după + specs + flags (poartă de review).';

    public function handle(): int
    {
        $manifestPath = config('catalog.enrich_manifest_path');
        $manifest = is_file($manifestPath)
            ? (json_decode((string) file_get_contents($manifestPath), true)['items'] ?? [])
            : [];

        $isFlagged = fn (Product $p): bool => (bool) ($manifest[$p->slug]['thin'] ?? false);

        // Toate produsele cu flag (sursă subțire) — afișate integral.
        $flagged = Product::query()->with('categories')->orderBy('id')->get()
            ->filter($isFlagged)->values();

        // Eșantion per categorie.
        $samplesPer = (int) $this->option('samples');
        $byCategory = Category::query()->ordered()->get()->map(function (Category $c) use ($samplesPer) {
            return [
                'category' => $c,
                'products' => $c->products()->where('is_active', true)->ordered()->limit($samplesPer)->get(),
            ];
        });

        $summary = [
            'total' => Product::count(),
            'cu_draft' => Product::whereNotNull('description_draft')->count(),
            'flagged' => $flagged->count(),
            'cu_specs' => Product::whereNotNull('specs')->count(),
            'model' => json_decode((string) @file_get_contents($manifestPath), true)['model'] ?? '—',
        ];

        $html = view('enrich.review', compact('flagged', 'byCategory', 'isFlagged', 'summary'))->render();

        $out = storage_path('enrich/review.html');
        @mkdir(dirname($out), 0775, true);
        file_put_contents($out, $html);

        $this->info('Review scris: '.$out);
        $this->table(['Metric', 'Valoare'], collect($summary)->map(fn ($v, $k) => [$k, $v])->values()->all());

        return self::SUCCESS;
    }
}
