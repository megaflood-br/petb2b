<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\WithInfiniteScroll;
use App\Models\Post;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class ManageBlog extends Component
{
    use WithPagination;
    use WithInfiniteScroll;

    protected function infiniteIncrement(): int
    {
        return 10;
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        $search = trim((string) request('q', ''));

        $posts = Post::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('title', 'like', '%'.$search.'%')
                        ->orWhere('content', 'like', '%'.$search.'%');
                });
            })
            ->with('blogCategories')
            ->latest('created_at')
            ->paginate($this->perPage)
            ->withQueryString();

        return view('livewire.admin.manage-blog', [
            'posts' => $posts,
            'search' => $search,
        ]);
    }
}
