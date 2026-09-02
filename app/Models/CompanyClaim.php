<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyClaim extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id', 'user_id', 'claimant_name', 'claimant_email', 'message', 'status',
        'approval_token', 'approval_token_expires_at', 'token_used_at',
    ];

    protected $casts = [
        'approval_token_expires_at' => 'datetime',
        'token_used_at' => 'datetime',
    ];

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

    public function claimantDisplayName(): string
    {
        return $this->claimant_name
            ?? $this->user?->name
            ?? 'Visitante';
    }

    public function claimantDisplayEmail(): ?string
    {
        return $this->claimant_email ?? $this->user?->email;
    }
}
