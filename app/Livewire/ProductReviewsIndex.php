<?php

namespace App\Livewire;

use App\Models\Post;
use App\Models\ProductReview;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class ProductReviewsIndex extends Component
{
    use WithPagination;

    #[Layout('layouts.app')]
    public function render()
    {
        $reviews = Post::with('blogCategories')
            ->where('is_active', true)
            ->productAnalyses()
            ->latest()
            ->paginate(12);

        // Fallback do módulo legado (tabela product_reviews) se ainda não houver posts.
        if ($reviews->total() === 0) {
            $reviews = ProductReview::where('is_active', true)->latest()->paginate(12);
        }

        return view('livewire.product-reviews-index', [
            'reviews' => $reviews,
        ]);
    }
}
