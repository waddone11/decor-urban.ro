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
     * URL-ul variantei thumbnail cu TRIPLU fallback (defensiv — merge indiferent
     * cum deployezi, cu sau fără re-import DB):
     *   1. calea din DB (thumb_sm_path / thumb_md_path), dacă e setată;
     *   2. convenția `<base>-{size}.webp`, dacă fișierul există pe disk;
     *   3. originalul (`url()`).
     */
    public function thumbUrl(int $size): string
    {
        // 1. Calea din DB (poate lipsi coloana înainte de migrație → getAttribute = null).
        $dbPath = $this->getAttribute(Thumbnails::column($size));
        if ($dbPath) {
            return Storage::disk('public')->url($dbPath);
        }

        // 2. Convenția pe disk.
        $variant = Thumbnails::variantPath($this->path, $size);
        if ($variant && is_file(Storage::disk('public')->path($variant))) {
            return Storage::disk('public')->url($variant);
        }

        // 3. Originalul.
        return $this->url();
    }
}
