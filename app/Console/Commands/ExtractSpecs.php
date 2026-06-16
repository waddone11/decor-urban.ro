<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Support\SpecsExtractor;
use Illuminate\Console\Command;

class ExtractSpecs extends Command
{
    protected $signature = 'catalog:extract-specs {--dry-run : Doar raport, fără scriere}';

    protected $description = 'Extrage specs structurate (determinist) din descriere+meta. Nu inventează.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $coverage = ['dimensiuni' => 0, 'material' => 0, 'montaj' => 0, 'finisaj' => 0];
        $empty = 0;
        $total = 0;

        foreach (Product::query()->orderBy('id')->cursor() as $product) {
            $total++;
            $specs = SpecsExtractor::fromProduct($product);

            foreach (array_keys($coverage) as $field) {
                if (! empty($specs[$field])) {
                    $coverage[$field]++;
                }
            }
            if ($specs === []) {
                $empty++;
            }

            if (! $dryRun) {
                $product->specs = $specs ?: null;
                $product->saveQuietly();
            }
        }

        $this->info(($dryRun ? '[dry-run] ' : '').'Specs extrase din '.$total.' produse.');
        $this->table(
            ['Câmp', 'Produse cu valoare', '%'],
            collect($coverage)->map(fn ($n, $f) => [$f, $n, $total ? round($n / $total * 100).'%' : '0%'])->values()->all()
        );
        $this->line('Produse fără niciun spec (sursă subțire): '.$empty);

        return self::SUCCESS;
    }
}
