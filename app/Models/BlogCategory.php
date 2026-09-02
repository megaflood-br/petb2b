<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class BlogCategory extends Model
{
    /** Chave de cache do menu de categorias (usado no view composer). */
    public const NAV_CACHE_KEY = 'nav.blog_categories';

    protected $fillable = ['name', 'slug'];

    protected static function booted(): void
    {
        // Invalida o cache do menu sempre que uma categoria muda.
        static::saved(fn () => Cache::forget(self::NAV_CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::NAV_CACHE_KEY));
    }

    // Relacionamento: Uma categoria tem muitos posts
    public function posts()
    {
        return $this->hasMany(Post::class, 'blog_category_id');
    }

    /**
     * Categoria de análises de produto (slug/nome com "analis").
     * Cobre "Análises de Produtos", "analises", "analise-de-produtos", etc.
     */
    public function isProductAnalysis(): bool
    {
        $slug = Str::lower((string) $this->slug);
        $name = Str::lower(Str::ascii((string) $this->name));

        return str_contains($slug, 'analis') || str_contains($name, 'analis');
    }

    public function scopeProductAnalysis(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where('slug', 'like', '%analis%')
                ->orWhere('name', 'like', '%analis%')
                ->orWhere('name', 'like', '%anális%');
        });
    }
}
