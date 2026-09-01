<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('breeds', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('species')->default('Cão'); // Cão, Gato, Ave, etc.
            $table->string('origin')->nullable();
            $table->string('size')->nullable();        // Pequeno, Médio, Grande
            $table->string('temperament')->nullable();
            $table->text('description');
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'species'], 'breeds_active_species_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('breeds');
    }
};
