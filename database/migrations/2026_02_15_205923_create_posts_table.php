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
    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->string('slug')->unique();
        $table->longText('content');
        $table->string('image')->nullable();

        // RELAÇÃO COM CATEGORIA
        $table->foreignId('blog_category_id')->nullable()->constrained('blog_categories')->onDelete('set null');

        // CAMPOS DE STATUS E DESTAQUE (O que estava faltando!)
        $table->boolean('is_active')->default(true);
        $table->boolean('is_featured')->default(false); // ADICIONE ESTA LINHA

        // SEO
        $table->string('meta_description')->nullable();
        $table->string('meta_keywords')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
