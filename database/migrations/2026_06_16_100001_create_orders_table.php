<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique(); // DU-2026-0007
            $table->string('customer_name');
            $table->string('company')->nullable();   // firmă/instituție (B2B)
            $table->string('cui')->nullable();
            $table->string('phone');
            $table->string('email');
            $table->string('county');                 // județ
            $table->string('city');
            $table->string('address');
            $table->string('payment_method');         // ramburs | whatsapp
            $table->text('notes')->nullable();
            $table->string('status')->default('noua'); // noua|in_lucru|ofertata|confirmata|livrata|anulata
            // Total „la cerere" — null până la ofertare; fără total fals.
            $table->decimal('total', 12, 2)->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
