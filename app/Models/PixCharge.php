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
}
