<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Breed extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'species',
        'origin',
        'size',
        'temperament',
        'description',
        'image',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /** Espécies disponíveis no guia. */
    public const SPECIES = ['Cão', 'Gato', 'Ave', 'Roedor', 'Peixe', 'Réptil', 'Outro'];

    /** Portes disponíveis. */
    public const SIZES = ['Pequeno', 'Médio', 'Grande'];

    protected static function booted(): void
    {
        static::creating(function (Breed $breed) {
            if (empty($breed->slug)) {
                $breed->slug = Str::slug($breed->name) . '-' . Str::lower(Str::random(5));
            }
        });
    }
}
