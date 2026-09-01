<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    // CORREGIDO: Adicionado 'slug' para permitir a criação automática via Excel
    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * RELACIONAMENTO: Uma categoria possui muitos fornecedores.
     */
    public function suppliers()
    {
        return $this->hasMany(Supplier::class, 'category', 'slug');
    }
}
