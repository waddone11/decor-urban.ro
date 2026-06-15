<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            // Codul produs NU e unic la sursă (#B201, #PS100 pe produse distincte):
            // indexat pentru căutare, FĂRĂ constraint unique.
            $table->string('code')->nullable()->index();
            $table->longText('description')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->boolean('price_on_request')->default(true);
            $table->string('availability')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            // Toate URL-urile vechi (pentru 301 redirects ulterior) + categoriile sursă.
            $table->json('legacy_urls')->nullable();
            $table->json('legacy_categories')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
