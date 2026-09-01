<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Cria a coluna credit_balance se não existir
        if (!Schema::hasColumn('suppliers', 'credit_balance')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->decimal('credit_balance', 10, 2)->default(0.00);
            });
        }

        // 2. CORREÇÃO CRÍTICA: Garante que TODAS as colunas de controle financeiro existam no MySQL
        Schema::table('advertisements', function (Blueprint $table) {
            if (!Schema::hasColumn('advertisements', 'supplier_id')) {
                $table->foreignId('supplier_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            }
            // ADICIONADO: Criando a coluna de visualizações que estava faltando na estrutura antiga
            if (!Schema::hasColumn('advertisements', 'views')) {
                $table->unsignedInteger('views')->default(0)->after('clicks');
            }
            if (!Schema::hasColumn('advertisements', 'cost_per_click')) {
                $table->decimal('cost_per_click', 5, 2)->default(0.50);
            }
            if (!Schema::hasColumn('advertisements', 'cost_per_impression')) {
                $table->decimal('cost_per_impression', 5, 4)->default(0.0050);
            }
        });

        // 3. Tabela de histórico de transações
        if (!Schema::hasTable('supplier_credit_transactions')) {
            Schema::create('supplier_credit_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
                $table->enum('type', ['deposit', 'expense_click', 'expense_impression', 'refund']);
                $table->decimal('amount', 10, 2);
                $table->string('description');
                $table->foreignId('advertisement_id')->nullable()->constrained()->onDelete('set null');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_credit_transactions');

        Schema::table('advertisements', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn(['supplier_id', 'views', 'cost_per_click', 'cost_per_impression']);
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('credit_balance');
        });
    }
};
