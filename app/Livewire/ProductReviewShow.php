<?php

namespace App\Livewire; // Corrigido aqui

use App\Models\ProductReview;
use Livewire\Component;
use Livewire\Attributes\Layout;

class ProductReviewShow extends Component
{
    public ProductReview $review;

    public function mount($slug)
    {
        $this->review = ProductReview::where('slug', $slug)->firstOrFail();
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.product-review-show');
    }
}
