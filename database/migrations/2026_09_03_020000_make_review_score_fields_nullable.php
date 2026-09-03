<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->decimal('rating', 3, 1)->nullable()->change();
            $table->text('pros')->nullable()->change();
            $table->text('cons')->nullable()->change();
            $table->text('verdict')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->decimal('rating', 3, 1)->nullable(false)->change();
            $table->text('pros')->nullable(false)->change();
            $table->text('cons')->nullable(false)->change();
            $table->text('verdict')->nullable(false)->change();
        });
    }
};
