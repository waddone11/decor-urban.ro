<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            // Produsul poate dispărea ulterior — păstrăm istoricul (null on delete).
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            // Snapshot la momentul comenzii (rămâne valid chiar dacă produsul se schimbă/șterge).
            $table->string('product_name');
            $table->string('product_code')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 12, 2)->nullable(); // „la cerere" → null
            $table->string('line_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
