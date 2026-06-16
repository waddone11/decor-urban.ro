<?php

namespace App\Models\Concerns;

use App\Support\Thumbnails;
use Illuminate\Support\Facades\Storage;

/**
 * Derivare URL variantă (thumbnail) cu FALLBACK defensiv la original.
 * Necesită ca modelul să aibă proprietatea `path` (relativă la disk-ul public)
 * și o metodă `url()` care întoarce URL-ul originalului.
 */
trait HasImageThumbnails
{
    /**
     * URL-ul variantei `<base>-{size}.webp` dacă fișierul există pe disk,
     * altfel URL-ul originalului. Site-ul nu se strică dacă varianta nu-i urcată.
     */
    public function thumbUrl(int $size): string
    {
        $variant = Thumbnails::variantPath($this->path, $size);

        if ($variant && is_file(Storage::disk('public')->path($variant))) {
            return Storage::disk('public')->url($variant);
        }

        return $this->url();
    }
}
