<?php

namespace Tests\Feature;

use App\Models\Advertisement;
use App\Models\PixCharge;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PixWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.asaas.webhook_token' => 'test-webhook-token']);
    }

    private function makeSupplierWithCharge(float $balance, float $amount, string $paymentId): array
    {
        $supplier = Supplier::create([
            'name' => 'Fornecedor',
            'email' => 'f_' . uniqid() . '@t.com',
            'description' => 'd',
            'category' => 'racas',
            'is_active' => true,
            'is_approved' => true,
        ]);
        $supplier->credit_balance = $balance;
        $supplier->save();

        $charge = PixCharge::create([
            'supplier_id' => $supplier->id,
            'asaas_payment_id' => $paymentId,
            'amount' => $amount,
            'status' => 'PENDING',
        ]);

        return [$supplier, $charge];
    }

    public function test_webhook_credita_saldo_uma_unica_vez(): void
    {
        [$supplier, $charge] = $this->makeSupplierWithCharge(0.0, 50.0, 'pay_123');

        // Anúncio pausado por falta de saldo deve ser reativado no crédito.
        $ad = Advertisement::create([
            'supplier_id' => $supplier->id,
            'title' => 'Camp', 'link' => 'https://x.com', 'position' => 'banner_topo',
            'image_path' => 'x.png', 'is_active' => false, 'clicks' => 0, 'views' => 0,
            'cost_per_click' => 0.50, 'cost_per_impression' => 0.0070,
        ]);

        $payload = ['event' => 'PAYMENT_RECEIVED', 'payment' => ['id' => 'pay_123', 'status' => 'RECEIVED']];
        $headers = ['asaas-access-token' => 'test-webhook-token'];

        $this->postJson('/webhooks/asaas', $payload, $headers)->assertOk();

        $this->assertEqualsWithDelta(50.0, (float) $supplier->fresh()->credit_balance, 0.0001);
        $this->assertNotNull($charge->fresh()->credited_at);
        $this->assertTrue($ad->fresh()->is_active);
        $this->assertDatabaseHas('supplier_credit_transactions', [
            'supplier_id' => $supplier->id, 'type' => 'deposit',
        ]);

        // Reenvio do mesmo evento não credita de novo (idempotência).
        $this->postJson('/webhooks/asaas', $payload, $headers)->assertOk();
        $this->assertEqualsWithDelta(50.0, (float) $supplier->fresh()->credit_balance, 0.0001);
        $this->assertEquals(1, \App\Models\SupplierCreditTransaction::where('supplier_id', $supplier->id)->where('type', 'deposit')->count());
    }

    public function test_webhook_token_invalido_retorna_401(): void
    {
        $this->makeSupplierWithCharge(0.0, 50.0, 'pay_999');

        $this->postJson('/webhooks/asaas',
            ['event' => 'PAYMENT_RECEIVED', 'payment' => ['id' => 'pay_999']],
            ['asaas-access-token' => 'errado']
        )->assertStatus(401);
    }

    public function test_webhook_evento_nao_pago_e_ignorado(): void
    {
        [$supplier] = $this->makeSupplierWithCharge(0.0, 50.0, 'pay_777');

        $this->postJson('/webhooks/asaas',
            ['event' => 'PAYMENT_CREATED', 'payment' => ['id' => 'pay_777']],
            ['asaas-access-token' => 'test-webhook-token']
        )->assertOk();

        $this->assertEqualsWithDelta(0.0, (float) $supplier->fresh()->credit_balance, 0.0001);
    }

    public function test_webhook_pagamento_desconhecido_nao_quebra(): void
    {
        $this->postJson('/webhooks/asaas',
            ['event' => 'PAYMENT_RECEIVED', 'payment' => ['id' => 'pay_inexistente']],
            ['asaas-access-token' => 'test-webhook-token']
        )->assertOk();
    }
}
