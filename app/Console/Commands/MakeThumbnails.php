<?php

namespace App\Console\Commands;

use App\Models\ProductImage;
use App\Models\ProjectImage;
use App\Support\Thumbnails;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\ImageManager;

/**
 * Generează variante mici WebP (400×400, 800×800) lângă fiecare imagine sursă.
 * GENERARE LOCALĂ — fișierele se urcă pe prod (shared poate n-are GD/Imagick/timp).
 * Idempotent (skip dacă există, dacă nu --force), resumabil. Nu atinge originalele.
 *
 * Calitate: 400 → 80, 800 → 82. Toate aplatizate „contain" pe alb (pătrate exacte),
 * deci PNG-urile transparente nu rămân cu fundal negru la export WebP.
 */
class MakeThumbnails extends Command
{
    protected $signature = 'images:thumbnails
        {--force : Regenerează chiar dacă varianta există}
        {--only= : Doar imaginile produsului/proiectului cu acest slug}';

    protected $description = 'Generează variante WebP 400/800 pentru produse + proiecte (local; se urcă pe prod)';

    /** Calitate WebP per dimensiune. */
    private const QUALITY = [400 => 80, 800 => 82];

    public function handle(): int
    {
        $only = $this->option('only');
        $force = (bool) $this->option('force');
        $disk = Storage::disk('public');

        $manager = new ImageManager(
            extension_loaded('imagick') ? new ImagickDriver : new GdDriver
        );

        // Adună căile sursă (produse + proiecte), filtrate opțional pe slug.
        $paths = ProductImage::query()
            ->when($only, fn ($q) => $q->whereHas('product', fn ($p) => $p->where('slug', $only)))
            ->pluck('path')
            ->merge(
                ProjectImage::query()
                    ->when($only, fn ($q) => $q->whereHas('project', fn ($p) => $p->where('slug', $only)))
                    ->pluck('path')
            )
            ->filter(fn ($p) => $p && ! Thumbnails::isVariant($p))
            ->unique()
            ->values();

        if ($paths->isEmpty()) {
            $this->warn($only ? "Nicio imagine pentru slug „{$only}”." : 'Nicio imagine de procesat.');

            return self::SUCCESS;
        }

        $stats = ['generated' => 0, 'skipped' => 0, 'missing_src' => 0, 'errors' => 0];
        $sizeSum = ['src' => 0, 'thumb' => 0, 'thumbCount' => 0];

        $bar = $this->output->createProgressBar($paths->count());
        $bar->start();

        foreach ($paths as $path) {
            $srcAbs = $disk->path($path);
            if (! is_file($srcAbs)) {
                $stats['missing_src']++;
                $bar->advance();

                continue;
            }
            $sizeSum['src'] += filesize($srcAbs);

            foreach (Thumbnails::SIZES as $size) {
                $variant = Thumbnails::variantPath($path, $size);
                $destAbs = $disk->path($variant);

                if (! $force && is_file($destAbs)) {
                    $stats['skipped']++;
                    $sizeSum['thumb'] += filesize($destAbs);
                    $sizeSum['thumbCount']++;

                    continue;
                }

                try {
                    $manager->read($srcAbs)
                        ->contain($size, $size, 'ffffff')
                        ->toWebp(self::QUALITY[$size])
                        ->save($destAbs);

                    $stats['generated']++;
                    $sizeSum['thumb'] += filesize($destAbs);
                    $sizeSum['thumbCount']++;
                } catch (\Throwable $e) {
                    $stats['errors']++;
                    $this->newLine();
                    $this->warn("  Eroare la {$variant}: {$e->getMessage()}");
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Thumbnails gata.');
        $this->line("  Surse procesate:   {$paths->count()}");
        $this->line("  Variante generate: {$stats['generated']}");
        $this->line("  Sărite (existau):  {$stats['skipped']}");
        if ($stats['missing_src']) {
            $this->warn("  Surse lipsă pe disk: {$stats['missing_src']}");
        }
        if ($stats['errors']) {
            $this->warn("  Erori:             {$stats['errors']}");
        }

        // Câștigul (medie thumb vs medie original) — orientativ.
        if ($sizeSum['thumbCount'] > 0 && $paths->count() > 0) {
            $avgSrc = $sizeSum['src'] / $paths->count();
            $avgThumb = $sizeSum['thumb'] / $sizeSum['thumbCount'];
            $this->line(sprintf(
                '  Medie original: %s · medie thumb: %s (~%d%% din original)',
                $this->human((int) $avgSrc),
                $this->human((int) $avgThumb),
                $avgSrc > 0 ? round($avgThumb / $avgSrc * 100) : 0
            ));
        }

        return $stats['errors'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function human(int $bytes): string
    {
        return $bytes > 1048576
            ? round($bytes / 1048576, 1).' MB'
            : round($bytes / 1024, 1).' KB';
    }
}
