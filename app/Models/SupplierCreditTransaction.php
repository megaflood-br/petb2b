<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierCreditTransaction extends Model
{
    // Define a tabela exata do banco para não haver erro de pluralização automática do Laravel
    protected $table = 'supplier_credit_transactions';

    protected $fillable = [
        'supplier_id',
        'type',
        'amount',
        'description',
        'advertisement_id'
    ];

    /**
     * Relacionamento com o Fornecedor dono da transação
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Relacionamento opcional com o Anúncio que gerou o custo
     */
    public function advertisement()
    {
        return $this->belongsTo(Advertisement::class);
    }
}
