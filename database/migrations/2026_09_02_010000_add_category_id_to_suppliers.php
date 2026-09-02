<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Normalização (incremental) da taxonomia de fornecedores.
 *
 * Adiciona suppliers.category_id como FK para categories, dando integridade
 * referencial e um relacionamento Eloquent. A coluna de texto `category`
 * (slug) é mantida para não quebrar filtros/telas existentes; o Supplier passa
 * a manter as duas em sincronia automaticamente (ver model).
 *
 * O backfill garante que toda categoria referenciada exista na tabela
 * `categories` (cria as que faltarem, ex.: 'racas', 'canis') e preenche o
 * category_id dos fornecedores atuais.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('suppliers', 'category_id')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->foreignId('category_id')->nullable()->after('category')->constrained('categories')->nullOnDelete();
            });
        }

        // Backfill DB-agnóstico (sem eventos de model).
        $slugs = DB::table('suppliers')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category');

        foreach ($slugs as $slug) {
            $categoryId = DB::table('categories')->where('slug', $slug)->value('id');

            if (! $categoryId) {
                $categoryId = DB::table('categories')->insertGetId([
                    'slug' => $slug,
                    'name' => Str::title(str_replace('-', ' ', $slug)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('suppliers')->where('category', $slug)->update(['category_id' => $categoryId]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('suppliers', 'category_id')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            });
        }
    }
};
