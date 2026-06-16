<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class PromoteDescriptions extends Command
{
    protected $signature = 'catalog:promote-descriptions {--only= : Doar un slug} {--force : Re-promovează chiar dacă e deja ai}';

    protected $description = 'Promovează description_draft → description (live), salvează vechea în legacy_description.';

    public function handle(): int
    {
        $force = (bool) $this->option('force');

        $query = Product::query()->whereNotNull('description_draft')->where('description_draft', '!=', '');
        if ($only = $this->option('only')) {
            $query->where('slug', $only);
        }

        $promoted = 0;
        $skipped = 0;

        foreach ($query->cursor() as $product) {
            // Idempotent: deja promovat → skip (nu suprascrie backup-ul legacy).
            if ($product->description_source === 'ai' && ! $force) {
                $skipped++;

                continue;
            }

            // Păstrează originalul legacy o singură dată (plasă de revert).
            if ($product->legacy_description === null) {
                $product->legacy_description = $product->description;
            }

            $product->description = $product->description_draft;
            $product->description_source = 'ai';
            $product->saveQuietly();
            $promoted++;
        }

        $this->info("Promovate: {$promoted}  ·  sărite (deja ai): {$skipped}");
        $this->warn('Re-rulează `catalog:export-snapshot` ca descrierile noi să intre în seed.');

        return self::SUCCESS;
    }
}
