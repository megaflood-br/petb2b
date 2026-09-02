<?php

namespace App\Livewire;

use App\Models\Post;
use App\Models\ProductReview;
use Livewire\Attributes\Layout;
use Livewire\Component;

class ProductReviewShow extends Component
{
    public $review;

    public bool $isHtmlContent = false;

    public function mount(string $slug)
    {
        $legacy = ProductReview::where('slug', $slug)->first();
        if ($legacy) {
            $this->review = $legacy;
            $this->isHtmlContent = false;

            return;
        }

        $this->review = Post::with('blogCategories')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();
        $this->isHtmlContent = true;
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.product-review-show');
    }
}
