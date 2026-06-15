<?php

namespace App\Console\Commands;

use App\Models\ProductImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class RevertAiImages extends Command
{
    protected $signature = 'images:revert-ai
        {--only= : Revert doar acest slug}
        {--dry-run : Arată ce s-ar reverti, fără să scrie nimic}';

    protected $description = 'Revert: re-copiază originalele pristine (storage/scrape/images) peste public + marchează source=legacy';

    public function handle(): int
    {
        $only = $this->option('only');
        $dryRun = (bool) $this->option('dry-run');
        $disk = Storage::disk('public');

        $rows = ProductImage::query()
            ->where('source', 'ai')
            ->when($only, fn ($q) => $q->whereHas('product', fn ($p) => $p->where('slug', $only)))
            ->with('product')
            ->get();

        if ($rows->isEmpty()) {
            $this->warn($only ? "Niciun rând AI pentru slug „{$only}”." : 'Niciun rând cu source=ai de revertit.');

            return self::SUCCESS;
        }

        $stats = ['reverted' => 0, 'missing_source' => []];

        foreach ($rows as $row) {
            // path = products/<slug>/<file>
            if (! preg_match('#^products/([^/]+)/(.+)$#', $row->path, $m)) {
                continue;
            }
            [, $slug, $file] = $m;
            $source = storage_path("scrape/images/{$slug}/{$file}");

            if (! File::exists($source)) {
                $stats['missing_source'][] = $row->path;
                $this->warn("  Lipsă original pristin: {$source} — sar peste (rândul rămâne neschimbat).");

                continue;
            }

            if ($dryRun) {
                $this->line("  [dry] revert {$row->path}  ←  scrape/images/{$slug}/{$file}");

                continue;
            }

            $disk->put($row->path, File::get($source));
            $row->update(['source' => 'legacy', 'enhanced_at' => null]);
            $stats['reverted']++;
        }

        $this->newLine();
        if ($dryRun) {
            $this->info("Dry-run: {$rows->count()} rânduri AI ar fi revertite.");
        } else {
            $this->info("Revert gata: {$stats['reverted']} imagini restaurate la legacy.");
        }
        if (! empty($stats['missing_source'])) {
            $this->warn('  Fără original pristin (nereverite): '.count($stats['missing_source']));
        }

        return self::SUCCESS;
    }
}
