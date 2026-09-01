<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

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
}
