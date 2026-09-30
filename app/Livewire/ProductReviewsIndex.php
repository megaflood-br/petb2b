<?php

namespace App\Livewire;

use App\Livewire\Concerns\WithInfiniteScroll;
use App\Models\Post;
use App\Models\ProductReview;
use App\Support\Seo;
use App\Support\Settings;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class ProductReviewsIndex extends Component
{
    use WithPagination;
    use WithInfiniteScroll;

    #[Layout('layouts.app')]
    public function render()
    {
        Seo::page(
            'Análises de produtos pet | '.Settings::siteName(),
            'Avaliações técnicas de rações, acessórios e produtos do mercado pet.'
        );

        $reviews = Post::with('blogCategories')
            ->where('is_active', true)
            ->productAnalyses()
            ->latest()
            ->paginate($this->perPage);

        // Fallback do módulo legado (tabela product_reviews) se ainda não houver posts.
        if ($reviews->total() === 0) {
            $reviews = ProductReview::where('is_active', true)->latest()->paginate($this->perPage);
        }

        return view('livewire.product-reviews-index', [
            'reviews' => $reviews,
        ]);
    }
}
