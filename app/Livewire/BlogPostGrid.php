<?php

namespace App\Livewire;

use App\Livewire\Concerns\WithInfiniteScroll;
use App\Models\Post;
use Livewire\Component;

class BlogPostGrid extends Component
{
    use WithInfiniteScroll;

    public ?string $category = null;

    protected function infiniteIncrement(): int
    {
        return 6;
    }

    public function render()
    {
        $posts = Post::query()
            ->where('is_active', true)
            ->with('blogCategories')
            ->when(filled($this->category), function ($query) {
                $query->whereHas('blogCategories', fn ($inner) => $inner->where('slug', $this->category));
            })
            ->orderBy('is_featured', 'desc')
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.blog-post-grid', [
            'posts' => $posts,
        ]);
    }
}
