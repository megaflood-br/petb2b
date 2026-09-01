<?php

namespace App\Livewire;

use App\Models\ProductReview;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

class ProductReviewsIndex extends Component
{
    use WithPagination;

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.product-reviews-index', [
            'reviews' => ProductReview::where('is_active', true)->latest()->paginate(12)
        ]);
    }
}
