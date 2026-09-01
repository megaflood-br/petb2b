<?php

namespace App\Services\Pix;

/**
 * Dados retornados por um provedor PIX ao criar uma cobrança.
 */
class PixChargeResult
{
    public function __construct(
        public string $paymentId,
        public string $status,
        public ?string $payload = null,        // copia e cola
        public ?string $encodedImage = null,   // QR em base64
        public ?string $expiration = null,
    ) {
    }
}
