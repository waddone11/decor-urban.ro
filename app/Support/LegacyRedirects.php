<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;

/**
 * Hartă 301 din URL-urile vechi → canonicalul nou.
 * Critic pentru păstrarea poziționării Google la lansare.
 *
 * Sursa hărții: `legacy_urls` ale produselor active + maparea de categorii vechi
 * din config('legacy_category_map.redirect_url_map'). Harta e cache-uită (nu se
 * face full-scan la fiecare request) — bustează cu `cache:clear` după reimport.
 */
class LegacyRedirects
{
    public const CACHE_KEY = 'legacy_redirects_map';

    /**
     * Caută o cale veche în hartă. Întoarce calea canonică relativă (ex.
     * "/produs/cos-c120") sau null dacă nu există.
     */
    public static function lookup(string $pathOrUrl): ?string
    {
        return static::map()[static::normalize($pathOrUrl)] ?? null;
    }

    /**
     * Harta normalizată [cale_veche => cale_noua_relativa], cache-uită.
     *
     * @return array<string, string>
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
     * @return array<string, string>
     */
    public static function build(): array
    {
        $map = [];

        // Categorii vechi (URL-uri cunoscute) → /categorie/{slug-nou}.
        foreach (config('legacy_category_map.redirect_url_map', []) as $oldPath => $newSlug) {
            $map[static::normalize($oldPath)] = '/categorie/'.$newSlug;
        }

        // Produse: fiecare URL vechi din legacy_urls → /produs/{slug}.
        Product::query()
            ->where('is_active', true)
            ->whereNotNull('legacy_urls')
            ->get(['id', 'slug', 'legacy_urls'])
            ->each(function (Product $product) use (&$map) {
                foreach ((array) $product->legacy_urls as $url) {
                    $key = static::normalize((string) $url);
                    if ($key !== '') {
                        $map[$key] = '/produs/'.$product->slug;
                    }
                }
            });

        return $map;
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
}
