<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Aumenta a precisão monetária para 4 casas decimais.
 *
 * Motivo: o custo por impressão (modelo estilo Google Ads) é sub-centavo
 * (ex.: R$ 0,0070). Com colunas decimal(_,2) esse valor era arredondado a
 * cada débito no MySQL, gerando cobrança incorreta e histórico inconsistente.
 * Com decimal(_,4) o valor é armazenado de forma exata. A conversão é
 * lossless (decimal -> decimal mais amplo), sem necessidade de transformar
 * dados existentes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->decimal('credit_balance', 14, 4)->default(0)->change();
        });

        Schema::table('supplier_credit_transactions', function (Blueprint $table) {
            $table->decimal('amount', 14, 4)->change();
        });

        Schema::table('advertisements', function (Blueprint $table) {
            $table->decimal('cost_per_click', 8, 4)->default(0.50)->change();
            $table->decimal('cost_per_impression', 8, 4)->default(0.0050)->change();
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->decimal('credit_balance', 10, 2)->default(0)->change();
        });

        Schema::table('supplier_credit_transactions', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->change();
        });

        Schema::table('advertisements', function (Blueprint $table) {
            $table->decimal('cost_per_click', 5, 2)->default(0.50)->change();
            $table->decimal('cost_per_impression', 5, 4)->default(0.0050)->change();
        });
    }
};
