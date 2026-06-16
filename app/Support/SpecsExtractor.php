<?php

namespace App\Support;

use App\Models\Product;

/**
 * Extrage specificații structurate DETERMINIST din textul sursă (descriere scrapată
 * + meta). REGULA DE AUR: nu inventează nimic — câmp negăsit = absent. Fără AI.
 */
class SpecsExtractor
{
    /** Materiale: keyword (deburr, lowercase) → etichetă canonică. */
    private const MATERIALS = [
        'rasinoasa' => 'lemn', 'brad' => 'lemn', 'stejar' => 'lemn', 'pin' => 'lemn',
        'fag' => 'lemn', 'lemn' => 'lemn',
        'teava' => 'metal', 'platbanda' => 'metal', 'fier forjat' => 'metal',
        'otel' => 'metal', 'metal' => 'metal',
        'inox' => 'inox',
        'beton' => 'beton',
        'alucobond' => 'alucobond', 'alcobond' => 'alucobond', 'alubond' => 'alucobond',
        'policarbonat' => 'policarbonat',
    ];

    public static function fromProduct(Product $product): array
    {
        $text = strip_tags((string) $product->description).' '.(string) $product->meta_keywords;

        return self::fromText($text);
    }

    /**
     * @return array<string, mixed> doar câmpurile găsite
     */
    public static function fromText(string $text): array
    {
        $text = preg_replace('/\s+/u', ' ', trim($text));
        $deburred = self::deburr($text);

        $specs = [];

        if ($dims = self::dimensions($text)) {
            $specs['dimensiuni'] = $dims;
        }
        if ($materials = self::materials($deburred)) {
            $specs['material'] = $materials;
        }
        if ($montaj = self::montaj($deburred)) {
            $specs['montaj'] = $montaj;
        }
        if ($finisaj = self::finisaj($deburred)) {
            $specs['finisaj'] = $finisaj;
        }

        return $specs;
    }

    /**
     * Dimensiuni: WxHxD în mm (zecimale cu , sau .), opțional cu Φ/Ø.
     *
     * @return list<string>
     */
    private static function dimensions(string $text): array
    {
        preg_match_all(
            '/(?:Φ|Ø|ø|⌀)?\s*\d+(?:[.,]\d+)?(?:\s*[x×X\/]\s*\d+(?:[.,]\d+)?)+\s*mm/u',
            $text,
            $m
        );

        $dims = array_map(fn ($d) => preg_replace('/\s+/', '', trim($d)), $m[0]);

        return array_values(array_unique(array_filter($dims)));
    }

    /** @return list<string> */
    private static function materials(string $deburred): array
    {
        $found = [];
        foreach (self::MATERIALS as $keyword => $label) {
            if (str_contains($deburred, $keyword)) {
                $found[$label] = true;
            }
        }

        return array_keys($found);
    }

    private static function montaj(string $deburred): ?string
    {
        // Conservator: doar indicii clare de montaj, nu cuvântul „beton" singur (e și material).
        if (preg_match('/dibluri|diblu|ancora/', $deburred)) {
            return 'cu dibluri';
        }
        if (preg_match('/betonat|in beton|fundatie/', $deburred)) {
            return 'fixare în beton';
        }
        if (str_contains($deburred, 'sudat') || str_contains($deburred, 'sudura')) {
            return 'sudat';
        }
        if (preg_match('/\bmobil\b/', $deburred)) {
            return 'mobil';
        }

        return null;
    }

    /** @return list<string> */
    private static function finisaj(string $deburred): array
    {
        $found = [];
        if (str_contains($deburred, 'electrostatic')) {
            $found[] = 'vopsit electrostatic';
        } elseif (str_contains($deburred, 'vopsit') || str_contains($deburred, 'vopsea') || str_contains($deburred, 'vopsita')) {
            $found[] = 'vopsit';
        }
        if (str_contains($deburred, 'lacuit')) {
            $found[] = 'lăcuit';
        }
        if (str_contains($deburred, 'zincat')) {
            $found[] = 'zincat';
        }

        return array_values(array_unique($found));
    }

    /** Lowercase + fără diacritice românești, pentru keyword-match robust. */
    private static function deburr(string $text): string
    {
        $map = ['ă' => 'a', 'â' => 'a', 'î' => 'i', 'ș' => 's', 'ş' => 's', 'ț' => 't', 'ţ' => 't'];

        return strtr(mb_strtolower($text), $map);
    }
}
