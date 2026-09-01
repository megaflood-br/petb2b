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
    Schema::table('suppliers', function (Blueprint $table) {
        // nullable() permite que o lojista deixe em branco
        $table->string('cnpj')->nullable()->after('email');
    });
}

public function down(): void
{
    Schema::table('suppliers', function (Blueprint $table) {
        $table->dropColumn('cnpj');
    });
}
};
