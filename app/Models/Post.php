<?php

namespace App\Models;

use App\Models\Concerns\FlushesHomeCache;
use App\Models\Concerns\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Post extends Model
{
    use HasFactory, Searchable, FlushesHomeCache;

    // Define quais campos podem ser preenchidos em massa (Removida a FK direta)
    protected $fillable = [
        'supplier_id',
        'title',
        'slug',
        'content',
        'image',
        'is_active',
        'is_featured',
        'is_premium',
        'is_sponsored',
        'rating',
        'pros',
        'cons',
        'verdict',
        'meta_description',
        'meta_keywords',
    ];

    // Garante que o Laravel trate como booleano
    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'is_premium' => 'boolean',
        'is_sponsored' => 'boolean',
        'rating' => 'float',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * RELACIONAMENTO: Uma matéria (Post) pertence a várias categorias de blog.
     * Mapeia a tabela pivô intermediária para permitir multiseleção sem quebras.
     */
    public function blogCategories(): BelongsToMany
    {
        return $this->belongsToMany(BlogCategory::class, 'blog_category_post');
    }

    /**
     * Rótulo de categoria para cards de análise (home / vitrine).
     * O módulo legado ProductReview usa a coluna `category`; posts usam o pivô.
     */
    public function getCategoryAttribute(): string
    {
        return $this->blogCategories->first()?->name ?? 'Análise';
    }

    public function hasReviewExtras(): bool
    {
        return $this->rating !== null
            || filled($this->pros)
            || filled($this->cons)
            || filled($this->verdict);
    }

    public function isProductAnalysis(): bool
    {
        if ($this->hasReviewExtras()) {
            return true;
        }

        return $this->blogCategories->contains(
            fn (BlogCategory $category) => $category->isProductAnalysis()
        );
    }

    /**
     * Matérias da vitrine de análises: categoria "analises*" ou campos de review preenchidos.
     */
    public function scopeProductAnalyses($query)
    {
        return $query->where(function ($q) {
            $q->whereNotNull('rating')
                ->orWhereNotNull('verdict')
                ->orWhereHas('blogCategories', fn ($c) => $c->productAnalysis());
        });
    }

    /**
     * Exclui posts cuja categoria está marcada para não aparecer na home.
     */
    public function scopeVisibleOnHome($query)
    {
        return $query->whereDoesntHave('blogCategories', function ($q) {
            $q->where('hide_from_home', true);
        });
    }

    public function hasCover(): bool
    {
        return $this->coverUrl() !== null;
    }

    /**
     * Capa gravada no storage ou, se vazia, a primeira imagem do HTML do post
     * (ainda aponta para /wp-content/uploads quando o import não baixou).
     */
    public function coverUrl(): ?string
    {
        if (filled($this->image)) {
            $image = ltrim((string) $this->image, '/');
            if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
                return (string) $this->image;
            }

            return asset('storage/' . $image);
        }

        return $this->firstContentImageUrl();
    }

    public function firstContentImageUrl(): ?string
    {
        if (! is_string($this->content) || $this->content === '') {
            return null;
        }

        if (preg_match('/<img\b[^>]*\bsrc=["\']([^"\']+)/i', $this->content, $match) !== 1) {
            return null;
        }

        $src = html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if (str_starts_with($src, '//')) {
            $src = 'https:' . $src;
        }

        return $src !== '' ? $src : null;
    }
}
