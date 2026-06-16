<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Căile variantelor WebP (thumbnails) generate local de `images:thumbnails`.
 * thumb_sm_path = 400×400, thumb_md_path = 800×800. Nullable — fallback la
 * convenția `<base>-{size}.webp` / la original dacă rămân goale.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['product_images', 'project_images'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->string('thumb_sm_path')->nullable()->after('path');
                $t->string('thumb_md_path')->nullable()->after('thumb_sm_path');
            });
        }
    }

    public function down(): void
    {
        foreach (['product_images', 'project_images'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn(['thumb_sm_path', 'thumb_md_path']);
            });
        }
    }
};
