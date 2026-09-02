<?php

namespace App\Models;

use App\Models\Concerns\FlushesHomeCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Magazine extends Model
{
    use HasFactory, FlushesHomeCache;

    protected $fillable = [
        'title',
        'slug', // Adicionado
        'issue_period',
        'pdf_path',
        'cover_path',
        'is_active'
    ];

    // Faz o Laravel buscar pelo slug em vez do ID nas rotas automaticamente
    public function getRouteKeyName()
    {
        return 'slug';
    }
}
