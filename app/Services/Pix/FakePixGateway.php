<?php

namespace App\Services\Pix;

use App\Models\Supplier;
use Illuminate\Support\Str;

/**
 * Driver PIX fake para desenvolvimento local e testes, usado quando não há
 * ASAAS_API_KEY configurada. Gera dados de cobrança determinísticos, sem
 * chamar nenhum serviço externo. A confirmação de pagamento é simulada
 * acionando o webhook manualmente.
 */
class FakePixGateway implements PixGateway
{
    public function createCharge(Supplier $supplier, float $amount): PixChargeResult
    {
        $paymentId = 'pay_fake_' . Str::lower(Str::random(16));

        return new PixChargeResult(
            paymentId: $paymentId,
            status: 'PENDING',
            payload: '00020126FAKE-PIX-COPIA-E-COLA-' . $paymentId,
            encodedImage: base64_encode('fake-qr-' . $paymentId),
            expiration: now()->addDay()->format('Y-m-d H:i:s'),
        );
    }
}
