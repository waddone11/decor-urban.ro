<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Product;

class Sitemap
{
    /**
     * Construiește XML-ul sitemap din paginile statice + categorii + produse active.
     */
    public static function xml(): string
    {
        $urls = [];

        // Pagini statice.
        $urls[] = ['loc' => url('/'), 'changefreq' => 'weekly', 'priority' => '1.0'];
        $urls[] = ['loc' => route('catalog'), 'changefreq' => 'weekly', 'priority' => '0.9'];
        $urls[] = ['loc' => route('proiecte'), 'changefreq' => 'monthly', 'priority' => '0.4'];

        // Categorii active.
        Category::query()->where('is_active', true)->orderBy('sort_order')->get()
            ->each(function (Category $c) use (&$urls) {
                $urls[] = [
                    'loc' => route('category', $c->slug),
                    'lastmod' => $c->updated_at?->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.8',
                ];
            });

        // Produse active.
        Product::query()->where('is_active', true)->orderBy('id')->get()
            ->each(function (Product $p) use (&$urls) {
                $urls[] = [
                    'loc' => route('product', $p->slug),
                    'lastmod' => $p->updated_at?->toAtomString(),
                    'changefreq' => 'monthly',
                    'priority' => '0.7',
                ];
            });

        $lines = ['<?xml version="1.0" encoding="UTF-8"?>'];
        $lines[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($urls as $url) {
            $lines[] = '  <url>';
            $lines[] = '    <loc>'.e($url['loc']).'</loc>';
            if (! empty($url['lastmod'])) {
                $lines[] = '    <lastmod>'.e($url['lastmod']).'</lastmod>';
            }
            $lines[] = '    <changefreq>'.$url['changefreq'].'</changefreq>';
            $lines[] = '    <priority>'.$url['priority'].'</priority>';
            $lines[] = '  </url>';
        }
        $lines[] = '</urlset>';

        return implode("\n", $lines)."\n";
    }
}
