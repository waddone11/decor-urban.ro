<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class RevertDescriptions extends Command
{
    protected $signature = 'catalog:revert-descriptions {--only= : Doar un slug}';

    protected $description = 'Plasă de siguranță: readuce legacy_description → description, description_source=legacy.';

    public function handle(): int
    {
        $query = Product::query()->where('description_source', 'ai')->whereNotNull('legacy_description');
        if ($only = $this->option('only')) {
            $query->where('slug', $only);
        }

        $reverted = 0;
        foreach ($query->cursor() as $product) {
            $product->description = $product->legacy_description;
            $product->description_source = 'legacy';
            $product->saveQuietly();
            $reverted++;
        }

        $this->info("Revenite la legacy: {$reverted}  (description_draft păstrat pentru re-promovare).");

        return self::SUCCESS;
    }
}
