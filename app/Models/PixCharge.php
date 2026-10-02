<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PixCharge extends Model
{
    protected $fillable = [
        'supplier_id',
        'asaas_payment_id',
        'amount',
        'status',
        'pix_payload',
        'pix_encoded_image',
        'pix_expiration',
        'credited_at',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'pix_expiration' => 'datetime',
        'credited_at' => 'datetime',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function isCredited(): bool
    {
        return ! is_null($this->credited_at);
    }

    public function isExpired(): bool
    {
        return ! $this->isCredited()
            && $this->pix_expiration
            && $this->pix_expiration->isPast();
    }

    public function isAwaitingPayment(): bool
    {
        return ! $this->isCredited() && ! $this->isExpired();
    }

    public function statusLabel(): string
    {
        if ($this->isCredited()) {
            return 'Pago';
        }

        if ($this->isExpired()) {
            return 'Expirado';
        }

        return 'Aguardando pagamento';
    }
}
