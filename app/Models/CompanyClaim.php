<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyClaim extends Model
{
    use HasFactory;

    protected $fillable = ['supplier_id', 'user_id', 'message', 'status'];

    // Relacionamento com o Fornecedor
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    // Relacionamento com o Usuário
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
