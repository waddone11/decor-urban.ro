<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            // 'legacy' = poza scrape-uita originala, 'ai' = poza trecuta prin Nano Banana.
            // string (nu enum) pentru portabilitate sqlite (teste) <-> mysql (prod).
            $table->string('source')->default('legacy')->after('path');
            $table->timestamp('enhanced_at')->nullable()->after('source');
        });
    }

    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropColumn(['source', 'enhanced_at']);
        });
    }
};
