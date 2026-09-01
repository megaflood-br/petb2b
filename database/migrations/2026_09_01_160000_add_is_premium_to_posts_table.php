<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('posts', 'is_premium')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->boolean('is_premium')->default(false)->after('is_featured');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('posts', 'is_premium')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->dropColumn('is_premium');
            });
        }
    }
};
