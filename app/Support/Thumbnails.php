<?php

namespace App\Support;

/**
 * Convenții pentru variantele de imagine (thumbnails).
 * Sursă unică de adevăr pentru comanda de generare ȘI accesorii din modele,
 * ca să nu divergă schema de denumire `<base>-{size}.webp`.
 */
class Thumbnails
{
    /** Dimensiunile generate (px, pătrat). */
    public const SIZES = [400, 800];

    /** Coloana DB care reține calea variantei, per dimensiune. */
    public const COLUMNS = [400 => 'thumb_sm_path', 800 => 'thumb_md_path'];

    /** Coloana pentru o dimensiune cerută (≥800 → md, altfel sm). */
    public static function column(int $size): string
    {
        return $size >= 800 ? 'thumb_md_path' : 'thumb_sm_path';
    }

    /**
     * Calea variantei pentru o cale-sursă relativă (la disk-ul public).
     * `products/foo.jpg` + 400 → `products/foo-400.webp`. Null dacă n-are extensie.
     */
    public static function variantPath(string $path, int $size): ?string
    {
        $dot = strrpos($path, '.');
        if ($dot === false) {
            return null;
        }

        return substr($path, 0, $dot)."-{$size}.webp";
    }

    /** True dacă fișierul e deja o variantă generată (`-400.webp`/`-800.webp`). */
    public static function isVariant(string $path): bool
    {
        return (bool) preg_match('/-(?:'.implode('|', self::SIZES).')\.webp$/', $path);
    }
}
