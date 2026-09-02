<?php

namespace App\Livewire\Admin;

use App\Models\Post;
use App\Models\BlogCategory; // Garantindo o uso da model correta das categorias do blog
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ManageBlog extends Component
{
    use WithPagination, WithFileUploads;

    public $showForm = false;
    public $postId, $title, $content, $image, $meta_description, $meta_keywords;
    public $is_featured = false;
    public $is_premium = false;
    public $rating;
    public $pros = '';
    public $cons = '';
    public $verdict = '';

    public $search = '';
    public $created_at;
    public $selected_categories = [];

    protected $rules = [
        'title' => 'required|min:3',
        'content' => 'required',
        'selected_categories' => 'required|array|min:1',
        'meta_description' => 'nullable|max:160',
        'meta_keywords' => 'nullable',
        'image' => 'nullable|image|max:2048',
        'created_at' => 'nullable',
        'rating' => 'nullable|numeric|min:0|max:5',
        'pros' => 'nullable|string',
        'cons' => 'nullable|string',
        'verdict' => 'nullable|string',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        // Puxa os posts filtrando pelo termo de busca e trazendo as categorias vinculadas
        $posts = Post::where(function($query) {
                $query->where('title', 'like', '%' . $this->search . '%')
                      ->orWhere('content', 'like', '%' . $this->search . '%');
            })
            ->with('blogCategories')
            ->latest('created_at') // Ordena pela data de criação original de forma decrescente
            ->paginate(10);

        return view('livewire.admin.manage-blog', [
            'posts' => $posts,
            'categories' => BlogCategory::orderBy('name', 'asc')->get() // Carrega da tabela blog_categories
        ])->layout('layouts.admin');
    }

    public function save()
    {
        $this->validate();

        $data = [
            'title' => $this->title,
            'slug' => Str::slug($this->title),
            'content' => $this->content,
            'is_featured' => $this->is_featured,
            'is_premium' => $this->is_premium,
            'rating' => $this->rating === '' || $this->rating === null ? null : $this->rating,
            'pros' => $this->pros ?: null,
            'cons' => $this->cons ?: null,
            'verdict' => $this->verdict ?: null,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
            'is_active' => true,
            'created_at' => $this->created_at ? Carbon::parse($this->created_at) : now()
        ];

        if ($this->image) {
            $data['image'] = $this->image->store('blog/posts', 'public');
        }

        if ($this->postId) {
            $post = Post::findOrFail($this->postId);
            $post->update($data);
            session()->flash('message', 'Notícia atualizada com sucesso!');
        } else {
            $post = Post::create($data);
            session()->flash('message', 'Nova notícia publicada com sucesso!');
        }

        $post->blogCategories()->sync($this->selected_categories);

        $this->resetForm();
    }

    public function edit($id)
    {
        $post = Post::with('blogCategories')->findOrFail($id);

        $this->postId = $post->id;
        $this->title = $post->title;
        $this->content = $post->content;
        $this->is_featured = $post->is_featured;
        $this->is_premium = $post->is_premium;
        $this->rating = $post->rating;
        $this->pros = $post->pros ?? '';
        $this->cons = $post->cons ?? '';
        $this->verdict = $post->verdict ?? '';
        $this->meta_description = $post->meta_description;
        $this->meta_keywords = $post->meta_keywords;
        $this->created_at = $post->created_at ? $post->created_at->format('Y-m-d\TH:i') : null;

        $this->selected_categories = $post->blogCategories->pluck('id')->map(fn($id) => (string)$id)->toArray();

        $this->showForm = true;
        $this->dispatch('set-post-content', content: $this->content);
    }

    public function delete($id)
    {
        Post::findOrFail($id)->delete();
        session()->flash('message', 'Postagem removida do portal.');
    }

    public function resetForm()
    {
        $this->reset([
            'postId', 'title', 'content', 'image', 'meta_description',
            'meta_keywords', 'is_featured', 'is_premium', 'rating', 'pros', 'cons', 'verdict',
            'selected_categories',
            'showForm', 'search', 'created_at'
        ]);
    }
}
