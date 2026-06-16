<?php

namespace App\Console\Commands;

use App\Models\ProductImage;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class PromoteAiImages extends Command
{
    protected $signature = 'images:promote-ai
        {--only= : Promovează doar acest slug}
        {--dry-run : Arată ce s-ar promova, fără să scrie nimic}';

    protected $description = 'Promovează pozele AI din staging (storage/scrape/images-ai) în catalogul public + marchează source=ai';

    public function handle(): int
    {
        $stagingBase = storage_path('scrape/images-ai');
        if (! File::isDirectory($stagingBase)) {
            $this->error("Nu găsesc staging-ul: {$stagingBase}. Rulează întâi scripts/ai-images/generate.mjs.");

            return self::FAILURE;
        }

        $only = $this->option('only');
        $dryRun = (bool) $this->option('dry-run');
        $disk = Storage::disk('public');
        $backup = Storage::disk('local'); // storage/app/private

        $slugs = collect(File::directories($stagingBase))
            ->map(fn (string $d) => basename($d))
            ->when($only, fn ($c) => $c->filter(fn ($s) => $s === $only))
            ->sort()
            ->values();

        if ($slugs->isEmpty()) {
            $this->warn($only ? "Niciun slug „{$only}” în staging." : 'Nimic de promovat în staging.');

            return self::SUCCESS;
        }

        $stats = ['copied' => 0, 'rows' => 0, 'no_row' => [], 'slugs' => 0, 'backed_up' => 0];
        $now = Carbon::now();

        foreach ($slugs as $slug) {
            $files = collect(File::files($stagingBase.'/'.$slug))
                ->filter(fn ($f) => in_array(strtolower($f->getExtension()), ['jpg', 'jpeg', 'png', 'webp'], true));

            if ($files->isEmpty()) {
                continue;
            }
            $stats['slugs']++;

            foreach ($files as $file) {
                $name = $file->getFilename();
                // Pe disk-ul public păstrăm exact path-ul existent: products/<slug>/<file>
                // (același nume ca sursa scrape) -> rândurile product_images rămân valide.
                $destRel = "products/{$slug}/{$name}";

                if ($dryRun) {
                    $row = ProductImage::where('path', $destRel)->exists();
                    $this->line(sprintf('  [dry] %s %s', $destRel, $row ? '' : '(fără rând product_images!)'));
                    if (! $row) {
                        $stats['no_row'][] = $destRel;
                    }

                    continue;
                }

                // Backup (dublă plasă): salvează poza publică CURENTĂ înainte de overwrite,
                // o singură dată (nu suprascrie backup-ul existent → păstrăm versiunea legacy inițială).
                $backupRel = "products-legacy-backup/{$slug}/{$name}";
                if ($disk->exists($destRel) && ! $backup->exists($backupRel)) {
                    $backup->put($backupRel, $disk->get($destRel));
                    $stats['backed_up']++;
                }

                $disk->put($destRel, File::get($file->getPathname()));
                $stats['copied']++;

                $affected = ProductImage::where('path', $destRel)
                    ->update(['source' => 'ai', 'enhanced_at' => $now]);

                if ($affected === 0) {
                    $stats['no_row'][] = $destRel;
                } else {
                    $stats['rows'] += $affected;
                }
            }
        }

        $this->newLine();
        if ($dryRun) {
            $this->info("Dry-run: {$slugs->count()} slug-uri în staging.");
        } else {
            $this->info('Promovare gata.');
            $this->line("  Slug-uri:           {$stats['slugs']}");
            $this->line("  Fișiere copiate:    {$stats['copied']}");
            $this->line("  Backup legacy:      {$stats['backed_up']}");
            $this->line("  Rânduri → source=ai:{$stats['rows']}");
        }
        if (! empty($stats['no_row'])) {
            $this->warn('  Fișiere fără rând product_images (path neschimbat?): '.count($stats['no_row']));
            foreach ($stats['no_row'] as $p) {
                $this->line("    - {$p}");
            }
        }
        $this->line('  Originalele rămân backup în storage/scrape/images/.');

        // Pozele noi capătă automat variante 400/800 (idempotent — le sare pe cele existente).
        if (! $dryRun && $stats['copied'] > 0) {
            $this->newLine();
            $this->info('Generez thumbnails pentru pozele noi…');
            $this->call('images:thumbnails', $only ? ['--only' => $only] : []);
        }

        return self::SUCCESS;
    }
}
