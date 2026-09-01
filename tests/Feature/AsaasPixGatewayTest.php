<?php

namespace Tests\Feature;

use App\Models\Supplier;
use App\Services\Pix\AsaasPixGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AsaasPixGatewayTest extends TestCase
{
    use RefreshDatabase;

    public function test_cria_customer_cobranca_e_retorna_qrcode(): void
    {
        Http::fake([
            '*/v3/customers' => Http::response(['id' => 'cus_abc'], 200),
            '*/v3/payments' => Http::response(['id' => 'pay_abc', 'status' => 'PENDING'], 200),
            '*/v3/payments/pay_abc/pixQrCode' => Http::response([
                'encodedImage' => 'QR_BASE64',
                'payload' => '00020126COPIA-E-COLA',
                'expirationDate' => '2026-09-02 23:59:59',
            ], 200),
        ]);

        $supplier = Supplier::create([
            'name' => 'Loja X',
            'email' => 'loja_' . uniqid() . '@t.com',
            'description' => 'd',
            'category' => 'racas',
            'cnpj' => '12.345.678/0001-99',
            'is_active' => true,
            'is_approved' => true,
        ]);

        $gateway = new AsaasPixGateway('https://api-sandbox.asaas.com', 'fake-key');
        $result = $gateway->createCharge($supplier, 50.0);

        $this->assertEquals('pay_abc', $result->paymentId);
        $this->assertEquals('00020126COPIA-E-COLA', $result->payload);
        $this->assertEquals('QR_BASE64', $result->encodedImage);

        // O customer criado foi persistido no fornecedor (reuso futuro).
        $this->assertEquals('cus_abc', $supplier->fresh()->asaas_customer_id);

        Http::assertSent(fn ($req) => $req->method() === 'POST'
            && str_ends_with($req->url(), '/v3/payments')
            && ($req['billingType'] ?? null) === 'PIX'
            && (float) ($req['value'] ?? 0) === 50.0);
    }

    public function test_reusa_customer_existente(): void
    {
        Http::fake([
            '*/v3/payments' => Http::response(['id' => 'pay_z', 'status' => 'PENDING'], 200),
            '*/v3/payments/pay_z/pixQrCode' => Http::response([
                'encodedImage' => 'QR', 'payload' => 'COPIA', 'expirationDate' => null,
            ], 200),
        ]);

        $supplier = Supplier::create([
            'name' => 'Loja Y',
            'email' => 'loja_' . uniqid() . '@t.com',
            'description' => 'd',
            'category' => 'racas',
            'is_active' => true,
            'is_approved' => true,
        ]);
        $supplier->forceFill(['asaas_customer_id' => 'cus_existente'])->save();

        $gateway = new AsaasPixGateway('https://api-sandbox.asaas.com', 'fake-key');
        $gateway->createCharge($supplier, 30.0);

        // Não deve criar novo customer.
        Http::assertNotSent(fn ($req) => str_contains($req->url(), '/v3/customers'));
    }
}
