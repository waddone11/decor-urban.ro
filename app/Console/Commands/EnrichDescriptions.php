<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Support\GeminiText;
use Illuminate\Console\Command;
use Throwable;

class EnrichDescriptions extends Command
{
    protected $signature = 'catalog:enrich-descriptions
        {--only= : Doar un slug}
        {--limit= : Limitează numărul de produse (test)}
        {--force : Re-generează chiar dacă există deja draft (ignoră manifest)}
        {--model= : Override model Gemini}';

    protected $description = 'Generează description_draft (AI Gemini, grounded) — staging, NU live. Idempotent + resumabil.';

    private string $manifestPath;

    /** @var array<string, mixed> */
    private array $manifest;

    public function handle(): int
    {
        $this->manifestPath = config('catalog.enrich_manifest_path');
        @mkdir(dirname($this->manifestPath), 0775, true);
        $this->manifest = is_file($this->manifestPath)
            ? (json_decode((string) file_get_contents($this->manifestPath), true) ?: [])
            : [];

        $template = file_get_contents(base_path('scripts/enrich/prompt.txt'));
        $gemini = new GeminiText(model: $this->option('model') ?: null);
        $force = (bool) $this->option('force');

        $query = Product::query()->with('categories')->orderBy('id');
        if ($only = $this->option('only')) {
            $query->where('slug', $only);
        }
        if ($limit = $this->option('limit')) {
            $query->limit((int) $limit);
        }
        $products = $query->get();

        $this->info('Model: '.$gemini->model().'  ·  produse: '.$products->count());
        $bar = $this->output->createProgressBar($products->count());
        $bar->start();

        $stats = ['ok' => 0, 'skip' => 0, 'thin' => 0, 'fail' => 0];

        foreach ($products as $product) {
            $entry = $this->manifest['items'][$product->slug] ?? null;
            if (! $force && ($entry['status'] ?? null) === 'ok' && $product->description_draft) {
                $stats['skip']++;
                $bar->advance();

                continue;
            }

            $thinSource = $this->isThin($product);
            $prompt = str_replace('{{FACTS}}', $this->facts($product), $template);

            try {
                $text = $this->withRetry(fn () => $gemini->generate($prompt));

                $generic = str_starts_with(trim($text), '[GENERIC]');
                $text = trim(preg_replace('/^\s*\[GENERIC\]\s*/', '', $text));

                $product->description_draft = $text;
                $product->saveQuietly();

                $flagged = $thinSource || $generic;
                $this->manifest['items'][$product->slug] = [
                    'status' => 'ok', 'model' => $gemini->model(), 'at' => date('c'),
                    'thin' => $flagged, 'generic' => $generic,
                ];
                $stats['ok']++;
                if ($flagged) {
                    $stats['thin']++;
                }
            } catch (Throwable $e) {
                $this->manifest['items'][$product->slug] = [
                    'status' => 'fail', 'model' => $gemini->model(), 'at' => date('c'),
                    'error' => mb_substr($e->getMessage(), 0, 200),
                ];
                $stats['fail']++;
            }

            $this->persistManifest($gemini->model());
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("ok={$stats['ok']}  skip={$stats['skip']}  flag-sursă-subțire={$stats['thin']}  fail={$stats['fail']}");
        $this->line('Manifest: '.$this->manifestPath);
        $this->warn('Drafturile sunt în description_draft — NU live. Vezi review-ul, apoi „promovează".');

        return self::SUCCESS;
    }

    private function isThin(Product $product): bool
    {
        $desc = trim(strip_tags((string) $product->description));

        return mb_strlen($desc) < 25 && empty($product->specs);
    }

    private function facts(Product $product): string
    {
        $cat = $product->primaryCategory() ?? $product->categories->first();
        $desc = trim(strip_tags((string) $product->description));

        $specsLines = [];
        foreach ((array) $product->specs as $key => $val) {
            $specsLines[] = '  - '.$key.': '.(is_array($val) ? implode(', ', $val) : $val);
        }

        return implode("\n", array_filter([
            'Nume: '.$product->name,
            'Cod: '.($product->code ? ltrim($product->code, '#') : '—'),
            'Categorie: '.($cat?->name ?? '—'),
            'Descriere sursă: '.($desc !== '' ? $desc : '(lipsă)'),
            'Specs extrase:'.($specsLines ? "\n".implode("\n", $specsLines) : ' (niciunul)'),
        ]));
    }

    /** Retry cu backoff exponențial (1s, 2s, 4s). */
    private function withRetry(callable $fn, int $attempts = 4): string
    {
        for ($i = 1; ; $i++) {
            try {
                return $fn();
            } catch (Throwable $e) {
                if ($i >= $attempts) {
                    throw $e;
                }
                sleep(2 ** ($i - 1));
            }
        }
    }

    private function persistManifest(string $model): void
    {
        $this->manifest['model'] = $model;
        $this->manifest['updated_at'] = date('c');
        file_put_contents($this->manifestPath, json_encode($this->manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
