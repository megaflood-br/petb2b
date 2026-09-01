<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $row) {
            // Adiciona a coluna após o telefone
            $row->string('whatsapp', 20)->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $row) {
            $row->dropColumn('whatsapp');
        });
    }
};
