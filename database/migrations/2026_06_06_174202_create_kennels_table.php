<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Corrigido para apenas um "Blueprint"
        Schema::create('kennels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Dono do canil
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('affix')->nullable(); // Afixo do canil
            $table->string('registration_number')->nullable(); // Registro CBKC / FCI
            $table->text('description')->nullable();

            // Localização
            $table->string('city')->default('Atibaia');
            $table->string('state')->default('SP');

            // Mídias e Contato
            $table->string('logo')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('instagram')->nullable();

            // Controle
            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false); // Canil verificado pela revista
            $table->timestamps();
        });

        // Tabela pivot simples para as raças criadas
        Schema::create('breed_kennel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kennel_id')->constrained()->onDelete('cascade');
            $table->string('breed_name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('breed_kennel');
        Schema::dropIfExists('kennels');
    }
};
