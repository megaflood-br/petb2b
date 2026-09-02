<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Campos da análise de produto (nota / prós / contras / veredito).
            // Nulos em matérias comuns; preenchidos nas da categoria de análises.
            $table->decimal('rating', 3, 1)->nullable()->after('is_sponsored');
            $table->text('pros')->nullable()->after('rating');
            $table->text('cons')->nullable()->after('pros');
            $table->text('verdict')->nullable()->after('cons');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['rating', 'pros', 'cons', 'verdict']);
        });
    }
};
