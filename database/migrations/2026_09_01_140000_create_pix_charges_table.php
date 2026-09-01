<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('suppliers', 'asaas_customer_id')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->string('asaas_customer_id')->nullable()->after('cnpj');
            });
        }

        Schema::create('pix_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->string('asaas_payment_id')->nullable()->unique();
            $table->decimal('amount', 14, 4);
            $table->string('status')->default('PENDING')->index();
            $table->text('pix_payload')->nullable();       // copia e cola
            $table->longText('pix_encoded_image')->nullable(); // QR em base64
            $table->timestamp('pix_expiration')->nullable();
            $table->timestamp('credited_at')->nullable();  // idempotência do crédito
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pix_charges');

        if (Schema::hasColumn('suppliers', 'asaas_customer_id')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->dropColumn('asaas_customer_id');
            });
        }
    }
};
