<?php

namespace App\Livewire\Admin;

use App\Models\Post;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class ManageBlog extends Component
{
    use WithPagination;

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
            ->paginate(10)
            ->withQueryString();

        return view('livewire.admin.manage-blog', [
            'posts' => $posts,
            'search' => $search,
        ]);
    }
}
