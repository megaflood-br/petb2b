<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Índices FULLTEXT para busca textual em produção (MySQL/MariaDB).
 * O SQLite (dev/testes) não suporta FULLTEXT; nesse caso a migration é no-op
 * e a busca cai no fallback LIKE (ver App\Models\Concerns\Searchable).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        Schema::table('suppliers', function (Blueprint $table) {
            $table->fullText(['name', 'description'], 'suppliers_fulltext');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->fullText(['title', 'content'], 'posts_fulltext');
        });
    }

    public function down(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropFullText('suppliers_fulltext');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropFullText('posts_fulltext');
        });
    }
};
