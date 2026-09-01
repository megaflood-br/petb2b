<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Post extends Model
{
    use HasFactory;

    // Define quais campos podem ser preenchidos em massa (Removida a FK direta)
    protected $fillable = [
        'title',
        'slug',
        'content',
        'image',
        'is_active',
        'is_featured',
        'meta_description',
        'meta_keywords',
    ];

    // Garante que o Laravel trate como booleano
    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * RELACIONAMENTO: Uma matéria (Post) pertence a várias categorias de blog.
     * Mapeia a tabela pivô intermediária para permitir multiseleção sem quebras.
     */
    public function blogCategories(): BelongsToMany
    {
        return $this->belongsToMany(BlogCategory::class, 'blog_category_post');
    }
}
