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
 * Generează variante mici WebP (400×400, 800×800) lângă fiecare imagine sursă
 * ȘI salvează căile în DB (thumb_sm_path / thumb_md_path).
 *
 * PROCESARE LOCALĂ DE IMAGINI (Intervention Image) — FĂRĂ niciun apel AI/extern.
 * Generare locală; fișierele se urcă pe prod (shared poate n-are GD/Imagick/timp).
 * Idempotent (skip dacă există + path setat, dacă nu --force), resumabil.
 * Nu atinge originalele.
 *
 * Calitate: 400 → 80, 800 → 82. Toate aplatizate „contain" pe alb (pătrate exacte),
 * deci PNG-urile transparente nu rămân cu fundal negru la export WebP.
 */
class MakeThumbnails extends Command
{
    protected $signature = 'images:thumbnails
        {--force : Regenerează chiar dacă varianta există}
        {--only= : Doar imaginile produsului/proiectului cu acest slug}';

    protected $description = 'Generează variante WebP 400/800 (produse + proiecte) + salvează căile în DB (local; fără AI)';

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

        // Rânduri (produse + proiecte), filtrate opțional pe slug — iterăm pe rânduri
        // ca să putem seta coloanele thumb_*_path în DB.
        $rows = ProductImage::query()
            ->when($only, fn ($q) => $q->whereHas('product', fn ($p) => $p->where('slug', $only)))
            ->get()
            ->concat(
                ProjectImage::query()
                    ->when($only, fn ($q) => $q->whereHas('project', fn ($p) => $p->where('slug', $only)))
                    ->get()
            )
            ->filter(fn ($r) => $r->path && ! Thumbnails::isVariant($r->path))
            ->values();

        if ($rows->isEmpty()) {
            $this->warn($only ? "Nicio imagine pentru slug „{$only}”." : 'Nicio imagine de procesat.');

            return self::SUCCESS;
        }

        $stats = ['generated' => 0, 'skipped' => 0, 'db_updated' => 0, 'missing_src' => 0, 'errors' => 0];
        $sizeSum = ['src' => 0, 'thumb' => 0, 'thumbCount' => 0];

        $bar = $this->output->createProgressBar($rows->count());
        $bar->start();

        foreach ($rows as $row) {
            $srcAbs = $disk->path($row->path);
            if (! is_file($srcAbs)) {
                $stats['missing_src']++;
                $bar->advance();

                continue;
            }
            $sizeSum['src'] += filesize($srcAbs);

            $dirty = false;
            foreach (Thumbnails::SIZES as $size) {
                $variant = Thumbnails::variantPath($row->path, $size);
                $destAbs = $disk->path($variant);
                $column = Thumbnails::COLUMNS[$size];

                if (! $force && is_file($destAbs)) {
                    $stats['skipped']++;
                    $sizeSum['thumb'] += filesize($destAbs);
                    $sizeSum['thumbCount']++;
                } else {
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

                        continue;
                    }
                }

                // Setează calea în DB (idempotent — doar dacă diferă).
                if (is_file($destAbs) && $row->getAttribute($column) !== $variant) {
                    $row->setAttribute($column, $variant);
                    $dirty = true;
                }
            }

            if ($dirty) {
                $row->save();
                $stats['db_updated']++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Thumbnails gata.');
        $this->line("  Surse procesate:   {$rows->count()}");
        $this->line("  Variante generate: {$stats['generated']}");
        $this->line("  Sărite (existau):  {$stats['skipped']}");
        $this->line("  Rânduri DB setate: {$stats['db_updated']}");
        if ($stats['missing_src']) {
            $this->warn("  Surse lipsă pe disk: {$stats['missing_src']}");
        }
        if ($stats['errors']) {
            $this->warn("  Erori:             {$stats['errors']}");
        }

        // Câștigul (medie thumb vs medie original) — orientativ.
        if ($sizeSum['thumbCount'] > 0 && $rows->count() > 0) {
            $avgSrc = $sizeSum['src'] / $rows->count();
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
