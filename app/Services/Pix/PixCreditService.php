<?php

namespace App\Services\Pix;

use App\Models\Advertisement;
use App\Models\PixCharge;
use App\Models\Supplier;
use App\Models\SupplierCreditTransaction;
use Illuminate\Support\Facades\DB;

/**
 * Credita uma cobrança PIX confirmada no saldo do fornecedor, de forma
 * idempotente (uma cobrança só é creditada uma vez, mesmo com webhooks
 * duplicados/reenviados pelo Asaas).
 */
class PixCreditService
{
    public function confirmByPaymentId(string $paymentId): bool
    {
        $charge = PixCharge::where('asaas_payment_id', $paymentId)->first();

        if (! $charge) {
            return false;
        }

        return $this->credit($charge);
    }

    public function credit(PixCharge $charge): bool
    {
        return DB::transaction(function () use ($charge) {
            // Trava a cobrança e revalida o estado dentro da transação.
            $charge = PixCharge::whereKey($charge->id)->lockForUpdate()->first();

            if (! $charge || $charge->isCredited()) {
                return false;
            }

            $supplier = Supplier::whereKey($charge->supplier_id)->lockForUpdate()->first();

            if (! $supplier) {
                return false;
            }

            $amount = (float) $charge->amount;

            $supplier->increment('credit_balance', $amount);

            SupplierCreditTransaction::create([
                'supplier_id' => $supplier->id,
                'type' => 'deposit',
                'amount' => $amount,
                'description' => 'Recarga de saldo via PIX confirmada (Asaas)',
            ]);

            // Reativa campanhas que estavam pausadas por falta de saldo.
            Advertisement::where('supplier_id', $supplier->id)
                ->where('is_active', false)
                ->update(['is_active' => true]);

            $charge->forceFill([
                'status' => 'RECEIVED',
                'credited_at' => now(),
            ])->save();

            return true;
        });
    }
}
