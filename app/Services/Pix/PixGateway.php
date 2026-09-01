<?php

namespace App\Services\Pix;

use App\Models\Supplier;

interface PixGateway
{
    /**
     * Cria uma cobrança PIX no provedor e retorna os dados do QR Code.
     */
    public function createCharge(Supplier $supplier, float $amount): PixChargeResult;
}
