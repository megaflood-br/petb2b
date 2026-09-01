<?php

namespace App\Livewire;

use App\Models\Breed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class BreedList extends Component
{
    use WithPagination;

    #[Url]
    public $search = '';

    #[Url]
    public $species = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSpecies()
    {
        $this->resetPage();
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $breeds = Breed::query()
            ->where('is_active', true)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->species, fn ($q) => $q->where('species', $this->species))
            ->orderBy('name')
            ->paginate(12);

        return view('livewire.breed-list', [
            'breeds' => $breeds,
            'speciesList' => Breed::SPECIES,
        ]);
    }
}
