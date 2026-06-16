<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Support\GeminiText;
use Illuminate\Console\Command;
use Throwable;

class EnrichCategories extends Command
{
    protected $signature = 'catalog:enrich-categories
        {--only= : Doar un slug}
        {--force : Re-generează chiar dacă există deja intro}
        {--model= : Override model Gemini}';

    protected $description = 'Generează intro per categorie (AI Gemini, grounded pe nume + tipul produselor).';

    public function handle(): int
    {
        $template = file_get_contents(base_path('scripts/enrich/category-prompt.txt'));
        $gemini = new GeminiText(model: $this->option('model') ?: null);
        $force = (bool) $this->option('force');

        $query = Category::query()->orderBy('sort_order');
        if ($only = $this->option('only')) {
            $query->where('slug', $only);
        }
        $categories = $query->get();

        $this->info('Model: '.$gemini->model().'  ·  categorii: '.$categories->count());
        $ok = 0;
        $skip = 0;
        $fail = 0;

        foreach ($categories as $category) {
            if (! $force && $category->intro) {
                $skip++;

                continue;
            }

            $prompt = str_replace('{{FACTS}}', $this->facts($category), $template);

            try {
                $text = $this->withRetry(fn () => $gemini->generate($prompt, 0.5, 1024));
                $category->intro = trim($text);
                $category->saveQuietly();
                $this->line('  ✓ '.$category->name);
                $ok++;
            } catch (Throwable $e) {
                $this->error('  ✗ '.$category->name.': '.mb_substr($e->getMessage(), 0, 120));
                $fail++;
            }
        }

        $this->newLine();
        $this->info("ok={$ok}  skip={$skip}  fail={$fail}");

        return self::SUCCESS;
    }

    private function facts(Category $category): string
    {
        $count = $category->products()->where('is_active', true)->count();
        $samples = $category->products()->where('is_active', true)->ordered()->limit(6)->pluck('name')->all();

        return implode("\n", array_filter([
            'Nume categorie: '.$category->name,
            'Număr produse: '.$count,
            'Exemple de produse: '.($samples ? implode('; ', $samples) : '—'),
        ]));
    }

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
}
