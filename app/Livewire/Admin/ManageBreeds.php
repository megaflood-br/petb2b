<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\WithInfiniteScroll;
use App\Models\Breed;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ManageBreeds extends Component
{
    use WithPagination, WithFileUploads, WithInfiniteScroll;

    protected function infiniteIncrement(): int
    {
        return 10;
    }

    public $breedId;
    public $name, $species = 'Cão', $origin, $size, $temperament, $description;
    public $image, $existingImage;
    public string $created_at = '';
    public bool $is_active = true;
    public $showForm = false;
    public $search = '';

    public function updatingSearch()
    {
        $this->resetInfiniteScroll();
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|min:2|max:120',
            'species' => 'required|in:' . implode(',', Breed::SPECIES),
            'origin' => 'nullable|max:120',
            'size' => 'nullable|in:' . implode(',', Breed::SIZES),
            'temperament' => 'nullable|max:150',
            'description' => 'required|min:20',
            'image' => 'nullable|image|max:2048',
            'created_at' => 'nullable|date',
        ];
    }

    public function toggleForm()
    {
        $this->reset(['breedId', 'name', 'origin', 'size', 'temperament', 'description', 'image', 'existingImage']);
        $this->species = 'Cão';
        $this->is_active = true;
        $this->created_at = now()->format('Y-m-d\TH:i');
        $this->showForm = ! $this->showForm;
    }

    public function edit($id)
    {
        $breed = Breed::findOrFail($id);
        $this->breedId = $breed->id;
        $this->name = $breed->name;
        $this->species = $breed->species;
        $this->origin = $breed->origin;
        $this->size = $breed->size;
        $this->temperament = $breed->temperament;
        $this->description = $breed->description;
        $this->existingImage = $breed->image;
        $this->is_active = $breed->is_active;
        $this->created_at = $breed->created_at?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i');
        $this->showForm = true;
    }

    public function save()
    {
        $data = $this->validate();
        unset($data['image'], $data['created_at']);
        $data['is_active'] = $this->is_active;

        if ($this->image) {
            if ($this->breedId && $this->existingImage) {
                Storage::disk('public')->delete($this->existingImage);
            }
            $data['image'] = $this->image->store('breeds', 'public');
        }

        $breed = $this->breedId ? Breed::findOrFail($this->breedId) : new Breed();
        $breed->fill($data);
        $breed->created_at = $this->created_at !== ''
            ? Carbon::parse($this->created_at)
            : ($breed->created_at ?? now());
        $breed->save();

        session()->flash('message', $this->breedId ? 'Raça atualizada!' : 'Raça cadastrada!');

        $this->toggleForm();
    }

    public function delete($id)
    {
        $breed = Breed::findOrFail($id);
        if ($breed->image) {
            Storage::disk('public')->delete($breed->image);
        }
        $breed->delete();
        session()->flash('message', 'Raça removida.');
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        $breeds = Breed::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->orderBy('name')
            ->paginate($this->perPage);

        return view('livewire.admin.manage-breeds', [
            'breeds' => $breeds,
            'speciesList' => Breed::SPECIES,
            'sizesList' => Breed::SIZES,
        ]);
    }
}
