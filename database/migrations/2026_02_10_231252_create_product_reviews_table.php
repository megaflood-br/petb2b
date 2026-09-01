<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::create('product_reviews', function (Blueprint $table) {
        $table->id();
        $table->string('title'); // Nome do Produto
        $table->string('slug')->unique();
        $table->string('category'); // Ex: Sopradores, Shampoos
        $table->decimal('rating', 3, 1); // Ex: 4.8
        $table->text('pros'); // Pontos positivos
        $table->text('cons'); // Pontos negativos
        $table->text('content'); // A análise completa
        $table->text('verdict'); // Resumo final
        $table->string('image')->nullable();
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};
