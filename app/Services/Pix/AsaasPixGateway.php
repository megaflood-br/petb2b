<?php

namespace App\Services\Pix;

use App\Models\Supplier;
use Illuminate\Http\Client\Response;
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
    public const MISSING_DOCUMENT_MESSAGE = 'Cadastre um CPF ou CNPJ válido no perfil da empresa para gerar o PIX.';

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
            throw new PixException('Não foi possível gerar o PIX agora. Tente novamente em instantes.');
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

        $document = $supplier->pixDocument();

        if (! $document) {
            throw new PixException(self::MISSING_DOCUMENT_MESSAGE);
        }

        $customer = $this->request('post', '/v3/customers', [
            'name' => $supplier->name,
            'cpfCnpj' => $document,
            'email' => $supplier->email,
        ]);

        $customerId = $customer['id'] ?? null;

        if (! $customerId) {
            throw new PixException('Não foi possível cadastrar a empresa no PIX. Confira o CPF/CNPJ no perfil.');
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
            throw new PixException($this->friendlyError($path, $response));
        }

        return $response->json() ?? [];
    }

    private function friendlyError(string $path, Response $response): string
    {
        $descriptions = collect($response->json('errors') ?? [])
            ->pluck('description')
            ->filter()
            ->values();

        $joined = mb_strtolower($descriptions->implode(' '));

        if (str_contains($joined, 'cpf') || str_contains($joined, 'cnpj')) {
            return self::MISSING_DOCUMENT_MESSAGE;
        }

        if ($response->clientError() && $descriptions->isNotEmpty()) {
            return (string) $descriptions->first();
        }

        report(new RuntimeException(
            "Asaas API error ({$response->status()}) em {$path}: " . $response->body()
        ));

        return 'Não foi possível gerar o PIX agora. Tente novamente em instantes.';
    }
}
