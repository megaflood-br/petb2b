<?php

namespace App\Livewire\Supplier;

use App\Models\BlogCategory;
use App\Models\Post;
use App\Models\Supplier;
use App\Models\SupplierCreditTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class ManageSponsoredPosts extends Component
{
    use WithFileUploads;

    public $supplier;

    public $title, $content, $image, $blogCategoryId;
    public bool $showForm = false;

    public function mount()
    {
        $this->supplier = Supplier::where('user_id', Auth::id())->first();

        if (! $this->supplier) {
            return $this->redirect(route('supplier.dashboard'), navigate: true);
        }
    }

    protected function rules(): array
    {
        return [
            'title' => 'required|min:5|max:150',
            'content' => 'required|min:20',
            'image' => 'nullable|image|max:2048',
            'blogCategoryId' => 'nullable|exists:blog_categories,id',
        ];
    }

    public function toggleForm()
    {
        $this->reset(['title', 'content', 'image', 'blogCategoryId']);
        $this->showForm = ! $this->showForm;
    }

    public function publish()
    {
        $this->validate();

        $cost = (float) config('ads.sponsored_post_cost');

        $published = DB::transaction(function () use ($cost) {
            $supplier = Supplier::whereKey($this->supplier->id)->lockForUpdate()->first();

            // Checagem de saldo dentro do lock (evita gasto além do saldo).
            if ((float) $supplier->credit_balance < $cost) {
                return false;
            }

            $imagePath = $this->image ? $this->image->store('blog/posts', 'public') : null;

            $post = Post::create([
                'supplier_id' => $supplier->id,
                'title' => $this->title,
                'slug' => Str::slug($this->title) . '-' . Str::lower(Str::random(6)),
                'content' => $this->content,
                'image' => $imagePath,
                'is_active' => true,
                'is_sponsored' => true,
            ]);

            if ($this->blogCategoryId) {
                $post->blogCategories()->sync([$this->blogCategoryId]);
            }

            $supplier->decrement('credit_balance', $cost);

            SupplierCreditTransaction::create([
                'supplier_id' => $supplier->id,
                'type' => 'expense_sponsored',
                'amount' => $cost,
                'description' => 'Matéria patrocinada: ' . $post->title,
            ]);

            return true;
        });

        if (! $published) {
            session()->flash('error', 'Saldo de créditos insuficiente para publicar uma matéria patrocinada (custo: R$ ' . number_format($cost, 2, ',', '.') . ').');
            return;
        }

        $this->supplier->refresh();
        $this->reset(['title', 'content', 'image', 'blogCategoryId', 'showForm']);
        session()->flash('message', 'Matéria patrocinada publicada com sucesso!');
    }

    public function delete($id)
    {
        // Remove apenas as próprias matérias (sem reembolso).
        Post::where('supplier_id', $this->supplier->id)
            ->where('is_sponsored', true)
            ->findOrFail($id)
            ->delete();

        session()->flash('message', 'Matéria removida.');
    }

    public function render()
    {
        $posts = Post::where('supplier_id', $this->supplier->id)
            ->where('is_sponsored', true)
            ->latest()
            ->get();

        return view('livewire.supplier.manage-sponsored-posts', [
            'posts' => $posts,
            'categories' => BlogCategory::orderBy('name')->get(),
            'cost' => (float) config('ads.sponsored_post_cost'),
        ])->layout('layouts.supplier');
    }
}
