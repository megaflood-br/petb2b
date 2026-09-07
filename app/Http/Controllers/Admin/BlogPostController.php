<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BlogPostController extends Controller
{
    public function create(): View
    {
        return view('admin.blog.form', [
            'post' => new Post(),
            'categories' => BlogCategory::orderBy('name')->get(),
            'selected' => old('selected_categories', []),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $post = new Post($this->payload($request, $data));
        $this->applyPublishedAt($post, $request);
        $post->save();
        $post->blogCategories()->sync($data['selected_categories']);

        return redirect()->route('admin.blog')->with('message', 'Nova notícia publicada com sucesso!');
    }

    public function edit(Post $post): View
    {
        $post->load('blogCategories');

        return view('admin.blog.form', [
            'post' => $post,
            'categories' => BlogCategory::orderBy('name')->get(),
            'selected' => old('selected_categories', $post->blogCategories->pluck('id')->map(fn ($id) => (string) $id)->all()),
        ]);
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $data = $this->validated($request);
        $post->fill($this->payload($request, $data, $post));
        $this->applyPublishedAt($post, $request);
        $post->save();
        $post->blogCategories()->sync($data['selected_categories']);

        return redirect()->route('admin.blog')->with('message', 'Notícia atualizada com sucesso!');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return redirect()->route('admin.blog')->with('message', 'Postagem removida do portal.');
    }

    /**
     * @return array{title: string, content: string, selected_categories: list<int|string>, meta_description: ?string, meta_keywords: ?string, created_at: ?string, image: mixed, is_featured: bool, is_premium: bool}
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => 'required|min:3',
            'content' => 'required',
            'selected_categories' => 'required|array|min:1',
            'meta_description' => 'nullable|max:160',
            'meta_keywords' => 'nullable',
            'image' => 'nullable|image|max:2048',
            'created_at' => 'nullable',
            'is_featured' => 'sometimes|boolean',
            'is_premium' => 'sometimes|boolean',
        ], [
            'selected_categories.required' => 'Selecione pelo menos uma categoria.',
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function payload(Request $request, array $data, ?Post $existing = null): array
    {
        $payload = [
            'title' => $data['title'],
            'slug' => Str::slug($data['title']) ?: ($existing?->slug ?: 'post-'.Str::lower(Str::random(8))),
            'content' => $data['content'],
            'is_featured' => $request->boolean('is_featured'),
            'is_premium' => $request->boolean('is_premium'),
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
            'is_active' => true,
        ];

        if ($request->hasFile('image')) {
            $payload['image'] = $request->file('image')->store('blog/posts', 'public');
        }

        return $payload;
    }

    private function applyPublishedAt(Post $post, Request $request): void
    {
        $post->created_at = $request->filled('created_at')
            ? Carbon::parse($request->input('created_at'))
            : ($post->created_at ?? now());
    }
}
