<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_claims', function (Blueprint $table) {
            // Permite reivindicação de visitante (sem conta ainda).
            $table->foreignId('user_id')->nullable()->change();

            if (! Schema::hasColumn('company_claims', 'claimant_name')) {
                $table->string('claimant_name')->nullable()->after('user_id');
            }
            if (! Schema::hasColumn('company_claims', 'claimant_email')) {
                $table->string('claimant_email')->nullable()->after('claimant_name');
            }
            if (! Schema::hasColumn('company_claims', 'approval_token')) {
                $table->string('approval_token')->nullable()->unique()->after('status');
            }
            if (! Schema::hasColumn('company_claims', 'approval_token_expires_at')) {
                $table->timestamp('approval_token_expires_at')->nullable()->after('approval_token');
            }
            if (! Schema::hasColumn('company_claims', 'token_used_at')) {
                $table->timestamp('token_used_at')->nullable()->after('approval_token_expires_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('company_claims', function (Blueprint $table) {
            $table->dropColumn(['claimant_name', 'claimant_email', 'approval_token', 'approval_token_expires_at', 'token_used_at']);
        });
    }
};
