<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (! Schema::hasColumn('posts', 'supplier_id')) {
                $table->foreignId('supplier_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('posts', 'is_sponsored')) {
                $table->boolean('is_sponsored')->default(false)->after('is_premium');
            }
        });

        // Libera o tipo de transação (era enum fixo) para novos tipos como
        // 'expense_sponsored' (matéria patrocinada).
        Schema::table('supplier_credit_transactions', function (Blueprint $table) {
            $table->string('type')->change();
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (Schema::hasColumn('posts', 'supplier_id')) {
                $table->dropForeign(['supplier_id']);
                $table->dropColumn('supplier_id');
            }
            if (Schema::hasColumn('posts', 'is_sponsored')) {
                $table->dropColumn('is_sponsored');
            }
        });

        Schema::table('supplier_credit_transactions', function (Blueprint $table) {
            $table->enum('type', ['deposit', 'expense_click', 'expense_impression', 'refund'])->change();
        });
    }
};
