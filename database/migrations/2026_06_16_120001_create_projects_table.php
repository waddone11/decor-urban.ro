<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('location')->nullable();       // ex. „Primăria Slatina, Olt"
            $table->string('client_type')->nullable();     // primarie|scoala|firma|alt
            $table->string('summary')->nullable();         // scurt, pentru card
            $table->longText('body')->nullable();          // „ce am făcut" (rich)
            $table->string('year')->nullable();
            $table->boolean('is_published')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('is_published');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
