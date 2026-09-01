<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->index(['is_approved', 'is_active'], 'suppliers_status_index');
            $table->index('category', 'suppliers_category_index');
            $table->index('state', 'suppliers_state_index');
            $table->index('city', 'suppliers_city_index');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->index(['is_active', 'is_featured'], 'posts_active_featured_index');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->index(['is_active', 'start_date'], 'events_active_start_index');
        });

        Schema::table('advertisements', function (Blueprint $table) {
            $table->index(['is_active', 'position'], 'ads_active_position_index');
        });

        Schema::table('classifieds', function (Blueprint $table) {
            $table->index('is_active', 'classifieds_active_index');
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropIndex('suppliers_status_index');
            $table->dropIndex('suppliers_category_index');
            $table->dropIndex('suppliers_state_index');
            $table->dropIndex('suppliers_city_index');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex('posts_active_featured_index');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex('events_active_start_index');
        });

        Schema::table('advertisements', function (Blueprint $table) {
            $table->dropIndex('ads_active_position_index');
        });

        Schema::table('classifieds', function (Blueprint $table) {
            $table->dropIndex('classifieds_active_index');
        });
    }
};
