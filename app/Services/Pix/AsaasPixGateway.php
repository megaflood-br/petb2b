<?php

namespace App\Services\Pix;

use App\Models\Supplier;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Driver PIX do Asaas.
 *
 * Fluxo:
 *  1. Garante um customer no Asaas para o fornecedor (cria e persiste o id).
 *  2. Cria a cobrança (POST /v3/payments, billingType=PIX).
 *  3. Recupera o QR Code dinâmico (GET /v3/payments/{id}/pixQrCode).
 */
class AsaasPixGateway implements PixGateway
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $apiKey,
    ) {
    }

    public function createCharge(Supplier $supplier, float $amount): PixChargeResult
    {
        $customerId = $this->ensureCustomer($supplier);

        $payment = $this->request('post', '/v3/payments', [
            'customer' => $customerId,
            'billingType' => 'PIX',
            'value' => round($amount, 2),
            'dueDate' => now()->format('Y-m-d'),
            'description' => 'Recarga de créditos - ' . $supplier->name,
            'externalReference' => 'supplier:' . $supplier->id,
        ]);

        $paymentId = $payment['id'] ?? null;

        if (! $paymentId) {
            throw new RuntimeException('Asaas: resposta de cobrança sem id.');
        }

        $qr = $this->request('get', "/v3/payments/{$paymentId}/pixQrCode");

        return new PixChargeResult(
            paymentId: $paymentId,
            status: $payment['status'] ?? 'PENDING',
            payload: $qr['payload'] ?? null,
            encodedImage: $qr['encodedImage'] ?? null,
            expiration: $qr['expirationDate'] ?? null,
        );
    }

    private function ensureCustomer(Supplier $supplier): string
    {
        if ($supplier->asaas_customer_id) {
            return $supplier->asaas_customer_id;
        }

        $customer = $this->request('post', '/v3/customers', [
            'name' => $supplier->name,
            'cpfCnpj' => preg_replace('/\D/', '', (string) $supplier->cnpj) ?: '00000000000',
            'email' => $supplier->email,
        ]);

        $customerId = $customer['id'] ?? null;

        if (! $customerId) {
            throw new RuntimeException('Asaas: resposta de customer sem id.');
        }

        $supplier->forceFill(['asaas_customer_id' => $customerId])->save();

        return $customerId;
    }

    private function request(string $method, string $path, array $data = []): array
    {
        $response = Http::baseUrl($this->baseUrl)
            ->withHeaders(['access_token' => $this->apiKey])
            ->acceptJson()
            ->asJson()
            ->{$method}($path, $data);

        if ($response->failed()) {
            throw new RuntimeException(
                "Asaas API error ({$response->status()}) em {$path}: " . $response->body()
            );
        }

        return $response->json() ?? [];
    }
}
