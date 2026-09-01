<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            // Adiciona a coluna se ela não existir
            if (!Schema::hasColumn('suppliers', 'is_approved')) {
                $table->boolean('is_approved')->default(false)->after('category');
            }
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('is_approved');
        });
    }
};
