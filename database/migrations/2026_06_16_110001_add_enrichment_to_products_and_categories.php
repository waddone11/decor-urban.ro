<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Specificații structurate extrase determinist (key→value). Null = neextras.
            $table->json('specs')->nullable()->after('description');
            // Backup descriere veche (plasă de siguranță pentru revert).
            $table->text('legacy_description')->nullable()->after('specs');
            // Staging descriere AI — NU se afișează până la promovare.
            $table->text('description_draft')->nullable()->after('legacy_description');
            // Sursa descrierii live: legacy (scrapată) | ai (promovată).
            $table->string('description_source')->default('legacy')->after('description_draft');
        });

        Schema::table('categories', function (Blueprint $table) {
            // Paragraf intro SEO sus pe pagina de categorie.
            $table->text('intro')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['specs', 'legacy_description', 'description_draft', 'description_source']);
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('intro');
        });
    }
};
