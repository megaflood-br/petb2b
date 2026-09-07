<?php

namespace App\Models;

use App\Http\Controllers\HomeController;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class BlogCategory extends Model
{
    /** Chave de cache do menu de categorias (usado no view composer). */
    public const NAV_CACHE_KEY = 'nav.blog_categories';

    protected $fillable = ['name', 'slug', 'hide_from_home'];

    protected $casts = [
        'hide_from_home' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget(self::NAV_CACHE_KEY);
            Cache::forget(HomeController::CACHE_KEY);
        });
        static::deleted(function () {
            Cache::forget(self::NAV_CACHE_KEY);
            Cache::forget(HomeController::CACHE_KEY);
        });
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
