<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Product;
use App\Models\Project;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Sitemap
{
    public static function xml(): string
    {
        return self::index();
    }

    public static function forgetCache(): void
    {
        foreach (['index', 'pages', 'categories', 'products', 'images'] as $name) {
            Cache::forget('sitemap.'.$name);
        }
    }

    public static function index(): string
    {
        return Cache::remember('sitemap.index', now()->addHours(6), fn () => self::sitemapIndex([
            url('/sitemaps/pages.xml'),
            url('/sitemaps/categories.xml'),
            url('/sitemaps/products.xml'),
            url('/sitemaps/images.xml'),
        ]));
    }

    public static function pages(): string
    {
        return Cache::remember('sitemap.pages', now()->addHours(6), function (): string {
            $urls = [];

            foreach (['home', 'catalog', 'despre', 'institutii', 'contact', 'proiecte', 'confidentialitate', 'termeni', 'politica-cookies'] as $route) {
                $urls[] = ['loc' => route($route)];
            }

            Project::query()->where('is_published', true)->orderBy('id')->get()
                ->each(function (Project $p) use (&$urls) {
                    $urls[] = [
                        'loc' => route('project.show', $p->slug),
                        'lastmod' => $p->updated_at?->toAtomString(),
                    ];
                });

            return self::urlset($urls);
        });
    }

    public static function categories(): string
    {
        return Cache::remember('sitemap.categories', now()->addHours(6), function (): string {
            $urls = [];

            Category::query()->where('is_active', true)->orderBy('sort_order')->get()
                ->each(function (Category $c) use (&$urls) {
                    $urls[] = [
                        'loc' => route('category', $c->slug),
                        'lastmod' => $c->updated_at?->toAtomString(),
                    ];
                });

            return self::urlset($urls);
        });
    }

    public static function products(): string
    {
        return Cache::remember('sitemap.products', now()->addHours(6), function (): string {
            $urls = [];

            Product::query()->where('is_active', true)->with('images')->orderBy('id')->get()
                ->each(function (Product $p) use (&$urls) {
                    $urls[] = [
                        'loc' => route('product', $p->slug),
                        'lastmod' => $p->updated_at?->toAtomString(),
                        'images' => $p->galleryImages()->map(fn ($image) => [
                            'loc' => $image->url(),
                            'title' => $image->alt ?: $p->name,
                        ])->all(),
                    ];
                });

            return self::urlset($urls, true);
        });
    }

    public static function images(): string
    {
        return Cache::remember('sitemap.images', now()->addHours(6), function (): string {
            $urls = [];

            Product::query()->where('is_active', true)->with('images')->orderBy('id')->get()
                ->each(function (Product $p) use (&$urls) {
                    foreach ($p->galleryImages() as $image) {
                        $urls[] = [
                            'loc' => $image->url(),
                            'lastmod' => $image->updated_at?->toAtomString() ?? $p->updated_at?->toAtomString(),
                        ];
                    }
                });

            return self::urlset($urls);
        });
    }

    private static function sitemapIndex(array $locs): string
    {
        $lines = ['<?xml version="1.0" encoding="UTF-8"?>'];
        $lines[] = '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($locs as $loc) {
            $lines[] = '  <sitemap>';
            $lines[] = '    <loc>'.e($loc).'</loc>';
            $lines[] = '  </sitemap>';
        }
        $lines[] = '</sitemapindex>';

        return implode("\n", $lines)."\n";
    }

    private static function urlset(array|Collection $urls, bool $withImages = false): string
    {
        $lines = ['<?xml version="1.0" encoding="UTF-8"?>'];
        $lines[] = $withImages
            ? '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'
            : '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($urls as $url) {
            $lines[] = '  <url>';
            $lines[] = '    <loc>'.e($url['loc']).'</loc>';
            if (! empty($url['lastmod'])) {
                $lines[] = '    <lastmod>'.e($url['lastmod']).'</lastmod>';
            }
            foreach (($url['images'] ?? []) as $image) {
                $lines[] = '    <image:image>';
                $lines[] = '      <image:loc>'.e($image['loc']).'</image:loc>';
                if (! empty($image['title'])) {
                    $lines[] = '      <image:title>'.e($image['title']).'</image:title>';
                }
                $lines[] = '    </image:image>';
            }
            $lines[] = '  </url>';
        }
        $lines[] = '</urlset>';

        return implode("\n", $lines)."\n";
    }
}
