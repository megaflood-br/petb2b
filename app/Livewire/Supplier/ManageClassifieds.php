<?php

namespace App\Livewire\Supplier;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Classified;
use App\Models\Supplier;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ManageClassifieds extends Component
{
    use WithFileUploads;

    public $title, $description, $price, $condition = 'Novo', $image;
    public $showForm = false;
    public $editingAdId = null; // Armazena o ID se estiver editando
    public $existingImage = null;

    public function toggleForm()
    {
        $this->reset(['title', 'description', 'price', 'image', 'editingAdId', 'existingImage']);
        $this->showForm = !$this->showForm;
    }

    public function edit($id)
    {
        $ad = Classified::findOrFail($id);
        $this->editingAdId = $ad->id;
        $this->title = $ad->title;
        $this->description = $ad->description;
        $this->price = $ad->price;
        $this->condition = $ad->condition;
        $this->existingImage = $ad->image;
        $this->showForm = true;
    }

    public function delete($id)
    {
        $ad = Classified::findOrFail($id);
        if ($ad->image) {
            Storage::disk('public')->delete($ad->image);
        }
        $ad->delete();
        session()->flash('message', 'Anúncio removido com sucesso!');
    }

    public function save()
    {
        $this->validate([
            'title' => 'required|min:5',
            'description' => 'required',
            'price' => 'required|numeric',
            'image' => 'nullable|image|max:1024',
        ]);

        $supplier = Supplier::where('user_id', Auth::id())->first();

        $data = [
            'supplier_id' => $supplier->id,
            'title' => $this->title,
            'slug' => Str::slug($this->title) . '-' . rand(100, 999),
            'description' => $this->description,
            'price' => $this->price,
            'condition' => $this->condition,
            'is_active' => true,
        ];

        if ($this->image) {
            // Se houver imagem antiga ao editar, remove ela
            if ($this->editingAdId && $this->existingImage) {
                Storage::disk('public')->delete($this->existingImage);
            }
            $data['image'] = $this->image->store('classifieds', 'public');
        }

        if ($this->editingAdId) {
            Classified::find($this->editingAdId)->update($data);
            session()->flash('message', 'Anúncio atualizado!');
        } else {
            Classified::create($data);
            session()->flash('message', 'Anúncio publicado!');
        }

        $this->toggleForm();
    }

    public function render()
    {
        $supplier = Supplier::where('user_id', Auth::id())->first();
        $myAds = $supplier ? Classified::where('supplier_id', $supplier->id)->latest()->get() : collect();

        return view('livewire.supplier.manage-classifieds', ['myAds' => $myAds]);
    }
}
