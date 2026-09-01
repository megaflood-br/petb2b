<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique(); // Importante para SEO: petbusiness.com.br/fornecedor/nome-da-empresa
            $table->string('document')->nullable(); // CNPJ
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->text('description');
            $table->string('logo')->nullable();
            $table->string('category'); // Ex: Ração, Medicamentos, Equipamentos

            // Campos de SEO
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();

            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
