<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kennel extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'slug', 'affix', 'registration_number', 'description',
        'city', 'state', 'logo', 'cover_image', 'whatsapp', 'instagram', 'is_active', 'is_verified'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function breeds(): HasMany
    {
        return $this->hasMany(RelatedBreed::class);
    }

    /**
     * RELACIONAMENTO NOVO: Um Canil possui muitas fotos na galeria
     */
    public function images(): HasMany
    {
        return $this->hasMany(KennelImage::class);
    }
}
