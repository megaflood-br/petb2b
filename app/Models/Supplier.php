<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Supplier extends Model
{
    use HasFactory;

   protected $fillable = [
    'user_id', // Adicione esta linha
    'name',
    'slug',
    'category',
    'description',
    'email',
    'cnpj',
    'city',
    'address',
    'state',
    'seo_title',       // LIBERADO PARA ENTRADA AUTOMÁTICA
    'seo_description', // LIBERADO PARA ENTRADA AUTOMÁTICA
    'phone',
    'whatsapp',
    'website',
    'logo',
    'is_active',
    'is_verified',
    'is_approved',
];

    // Otimização de SEO: Gera o slug automaticamente ao salvar o nome
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($supplier) {
            if (empty($supplier->slug)) {
                $supplier->slug = Str::slug($supplier->name);
            }
        });
    }
}
