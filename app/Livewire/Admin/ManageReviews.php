<?php

namespace App\Livewire\Admin;

use App\Models\ProductReview;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class ManageReviews extends Component
{
    use WithFileUploads;

    public $title, $category, $content, $image, $existingImage;
    public $editingReviewId = null;
    public $showForm = false;

    public function toggleForm()
    {
        $this->reset(['title', 'category', 'content', 'image', 'editingReviewId', 'existingImage']);
        $this->showForm = ! $this->showForm;
    }

    public function edit($id)
    {
        $review = ProductReview::findOrFail($id);
        $this->editingReviewId = $review->id;
        $this->title = $review->title;
        $this->category = $review->category;
        $this->content = $review->content;
        $this->existingImage = $review->image;
        $this->showForm = true;
    }

    public function save()
    {
        $this->validate([
            'title' => 'required|min:3',
            'category' => 'required',
            'content' => 'required',
            'image' => 'nullable|image|max:2048',
        ]);

        $data = [
            'title' => $this->title,
            'slug' => Str::slug($this->title),
            'category' => $this->category,
            'content' => $this->content,
            'rating' => null,
            'pros' => null,
            'cons' => null,
            'verdict' => null,
        ];

        if ($this->image) {
            if ($this->editingReviewId && $this->existingImage) {
                Storage::disk('public')->delete($this->existingImage);
            }
            $data['image'] = $this->image->store('reviews', 'public');
        }

        if ($this->editingReviewId) {
            ProductReview::find($this->editingReviewId)->update($data);
            session()->flash('message', 'Análise atualizada com sucesso!');
        } else {
            ProductReview::create($data);
            session()->flash('message', 'Análise publicada com sucesso!');
        }

        $this->toggleForm();
    }

    public function delete($id)
    {
        $review = ProductReview::find($id);
        if ($review->image) {
            Storage::disk('public')->delete($review->image);
        }
        $review->delete();
        session()->flash('message', 'Análise excluída!');
    }

    public function render()
    {
        return view('livewire.admin.manage-reviews', [
            'reviews' => ProductReview::latest()->get(),
        ]);
    }
}
