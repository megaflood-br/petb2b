<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'name',
        'email',
        'phone',
        'message',
        'is_read'
    ];

    // Relacionamento: Um lead pertence a um fornecedor
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
