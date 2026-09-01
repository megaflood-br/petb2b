<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('type')->default('CLT'); // CLT, PJ, Estágio, Freelancer, Temporário
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('salary')->nullable();
            $table->string('how_to_apply'); // e-mail, WhatsApp ou link de candidatura
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'state'], 'jobs_active_state_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_postings');
    }
};
