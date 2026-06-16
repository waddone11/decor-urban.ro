<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;

/**
 * Hartă 301 din URL-urile vechi → canonicalul nou.
 * Critic pentru păstrarea poziționării Google la lansare.
 *
 * Două straturi de potrivire:
 *  - `exact`    — pe calea normalizată completă: legacy_urls produse, page_redirects,
 *                 redirect_url_map (categorii pe cale exactă).
 *  - `category` — pe ULTIMUL segment al căii (prinde formele nested + flat ale
 *                 categoriilor vechi), din config `category_redirects`.
 *
 * Harta e cache-uită (nu full-scan per request) — bustează cu `cache:clear` după reimport.
 */
class LegacyRedirects
{
    public const CACHE_KEY = 'legacy_redirects_map';

    /**
     * Caută o cale veche. Întoarce calea canonică relativă (ex. "/produs/cos-c120")
     * sau null. Precedență: potrivire exactă pe cale, apoi pe ultimul segment.
     */
    public static function lookup(string $pathOrUrl): ?string
    {
        $map = static::map();
        $full = static::normalize($pathOrUrl);

        if (isset($map['exact'][$full])) {
            return $map['exact'][$full];
        }

        $last = static::lastSegment($full);
        if ($last !== '' && isset($map['category'][$last])) {
            return $map['category'][$last];
        }

        return null;
    }

    /**
     * @return array{exact: array<string, string>, category: array<string, string>}
     */
    public static function map(): array
    {
        return Cache::rememberForever(static::CACHE_KEY, fn () => static::build());
    }

    public static function flush(): void
    {
        Cache::forget(static::CACHE_KEY);
    }

    /**
     * @return array{exact: array<string, string>, category: array<string, string>}
     */
    public static function build(): array
    {
        $exact = [];
        $category = [];

        // Categorii vechi pe cale EXACTĂ (config moștenit din 4b).
        foreach (config('legacy_category_map.redirect_url_map', []) as $oldPath => $newSlug) {
            $exact[static::normalize($oldPath)] = '/categorie/'.$newSlug;
        }

        // Pagini-părinte vechi → cale nouă (exact).
        foreach (config('legacy_category_map.page_redirects', []) as $oldPath => $target) {
            $exact[static::normalize($oldPath)] = $target;
        }

        // Categorii vechi pe ULTIMUL segment (nested + flat).
        foreach (config('legacy_category_map.category_redirects', []) as $lastSeg => $newSlug) {
            $category[static::normalize($lastSeg)] = '/categorie/'.$newSlug;
        }

        // Produse: fiecare URL vechi din legacy_urls → /produs/{slug} (cale exactă, prioritar).
        Product::query()
            ->where('is_active', true)
            ->whereNotNull('legacy_urls')
            ->get(['id', 'slug', 'legacy_urls'])
            ->each(function (Product $product) use (&$exact) {
                foreach ((array) $product->legacy_urls as $url) {
                    $key = static::normalize((string) $url);
                    if ($key !== '') {
                        $exact[$key] = '/produs/'.$product->slug;
                    }
                }
            });

        return ['exact' => $exact, 'category' => $category];
    }

    /**
     * Normalizează un URL sau o cale la cheia de căutare: doar path-ul, fără
     * slash-uri la capete, lowercase.
     */
    public static function normalize(string $pathOrUrl): string
    {
        $path = parse_url($pathOrUrl, PHP_URL_PATH);

        return strtolower(trim($path ?? $pathOrUrl, '/'));
    }

    /** Ultimul segment al unei căi normalizate (ex. "a/b/c" → "c"). */
    public static function lastSegment(string $normalizedPath): string
    {
        if ($normalizedPath === '') {
            return '';
        }

        $segments = explode('/', $normalizedPath);

        return end($segments) ?: '';
    }
}
