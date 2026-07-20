<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

/**
 * Randare sigură pentru descrieri cu format mixt: text simplu (enrichment
 * Gemini) sau HTML (re-salvat din Filament RichEditor). HTML-ul trece printr-un
 * whitelist strict de tag-uri, fără niciun atribut — nu randa {!! !!} pe brut.
 */
class SafeHtml
{
    private const ALLOWED_TAGS = ['p', 'br', 'ul', 'ol', 'li', 'strong', 'em', 'b', 'i'];

    public static function looksLikeHtml(string $text): bool
    {
        return (bool) preg_match('/<\s*\/?[a-z][^>]*>/i', $text);
    }

    /** Păstrează doar tag-urile din whitelist și le elimină toate atributele. */
    public static function sanitize(string $html): string
    {
        $clean = strip_tags($html, self::ALLOWED_TAGS);
        $clean = preg_replace(
            '/<(\/?)('.implode('|', self::ALLOWED_TAGS).')\b[^>]*>/i',
            '<$1$2>',
            $clean
        );

        return trim($clean);
    }

    /** Text simplu → escaped + <br>; HTML → sanitizat. Null dacă e gol. */
    public static function render(?string $text): ?HtmlString
    {
        $text = trim((string) $text);
        if ($text === '') {
            return null;
        }

        return new HtmlString(
            self::looksLikeHtml($text) ? self::sanitize($text) : nl2br(e($text))
        );
    }
}
