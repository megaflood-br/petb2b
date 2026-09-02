<?php

namespace App\Models;

use App\Models\Concerns\FlushesHomeCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory, FlushesHomeCache;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'start_date',
        'end_date',
        'image',
        'location',
        'city',
        'state',
        'external_link',
        'is_active'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean'
    ];

    /**
     * Boot do modelo para escutar eventos do Eloquent
     */
    protected static function boot()
    {
        parent::boot();

        // Antes de criar um novo evento no banco de dados local
        static::creating(function ($event) {
            if (empty($event->slug)) {
                $event->slug = static::generateUniqueSlug($event->title);
            }
        });

        // Antes de atualizar um evento existente no banco
        static::updating(function ($event) {
            // Se o título mudou, gera um novo slug limpo automaticamente
            if ($event->isDirty('title')) {
                $event->slug = static::generateUniqueSlug($event->title);
            }
        });
    }

    /**
     * Gera um slug limpo e único baseado em Regex para evitar números sequenciais feios
     */
    private static function generateUniqueSlug($title)
    {
        $slug = Str::slug($title);

        // Verifica no banco se já existe esse slug limpo (excluindo IDs)
        $count = static::where('slug', 'REGEXP', "^{$slug}(-[0-9]+)?$")->count();

        // Se já existir, ele adiciona um sufixo numérico incremental limpo (ex: -2, -3) e não o ID bruto do banco
        return $count ? "{$slug}-" . ($count + 1) : $slug;
    }
}
