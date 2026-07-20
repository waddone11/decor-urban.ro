<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Support\SafeHtml;
use Illuminate\Console\Command;

/**
 * Repară formatul descrierilor:
 *  1. HTML "colapsat" de Filament RichEditor (un singur <p>, paragrafe lipite)
 *     → reconstruit din description_draft dacă textul corespunde (fără edit manual).
 *  2. Text simplu cu paragrafe \n\n → HTML curat cu <p> per paragraf, ca
 *     RichEditor să nu mai piardă paragrafele la următoarea salvare din admin.
 * Descrierile HTML editate manual rămân neatinse.
 */
class NormalizeDescriptions extends Command
{
    protected $signature = 'catalog:normalize-descriptions {--dry-run : Doar raportează, nu scrie}';

    protected $description = 'Normalizează description la HTML curat cu <p> per paragraf (repară colapsarea RichEditor).';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $stats = ['rebuilt' => 0, 'wrapped' => 0, 'skipped' => 0];

        foreach (Product::query()->cursor() as $product) {
            $desc = trim((string) $product->description);
            if ($desc === '') {
                continue;
            }

            $new = $this->normalized($product, $desc);
            if ($new === null) {
                $stats['skipped']++;

                continue;
            }

            $key = SafeHtml::looksLikeHtml($desc) ? 'rebuilt' : 'wrapped';
            $stats[$key]++;
            $this->line(($dry ? '[dry-run] ' : '').$key.': '.$product->slug);

            if (! $dry) {
                $product->description = $new;
                $product->saveQuietly();
            }
        }

        $this->info("reconstruite din draft={$stats['rebuilt']}  ·  text→<p>={$stats['wrapped']}  ·  neatinse={$stats['skipped']}");

        return self::SUCCESS;
    }

    /** Noua valoare pentru description, sau null dacă nu trebuie modificată. */
    private function normalized(Product $product, string $desc): ?string
    {
        if (SafeHtml::looksLikeHtml($desc)) {
            // HTML: reconstruim DOAR dacă textul e identic cu draftul modulo
            // whitespace (adică e colapsarea RichEditor, nu un edit manual).
            $draft = trim((string) $product->description_draft);
            if ($draft === '' || $this->squash(strip_tags($desc)) !== $this->squash($draft)) {
                return null;
            }

            $new = $this->wrapParagraphs($draft);
        } else {
            $new = $this->wrapParagraphs($desc);
        }

        return $new !== $desc ? $new : null;
    }

    /** Text simplu → <p> per paragraf (\n\n), <br> pentru newline simplu. */
    private function wrapParagraphs(string $text): string
    {
        $paragraphs = preg_split('/\R{2,}/u', trim($text));

        return implode('', array_map(
            fn (string $p): string => '<p>'.nl2br(e(trim($p)), false).'</p>',
            array_filter($paragraphs, fn (string $p): bool => trim($p) !== '')
        ));
    }

    /** Elimină tot whitespace-ul, pentru comparație tolerantă la colapsare. */
    private function squash(string $text): string
    {
        return preg_replace('/\s+/u', '', $text);
    }
}
