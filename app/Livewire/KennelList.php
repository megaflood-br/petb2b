<?php

namespace App\Livewire;

use App\Models\Kennel;
use App\Models\RelatedBreed;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

class KennelList extends Component
{
    use WithPagination;

    public $searchBreed = '';
    public $searchName = '';

    protected $queryString = [
        'searchBreed' => ['except' => ''],
        'searchName' => ['except' => ''],
    ];

    #[Layout('layouts.app')]
    public function render()
    {
        // Query base: Apenas canis ativos
        $query = Kennel::where('is_active', true);

        // Filtro por Nome do Canil
        if (!empty($this->searchName)) {
            $query->where('name', 'like', '%' . $this->searchName . '%');
        }

        // Filtro por Raça (Busca na tabela vinculada breed_kennel)
        if (!empty($this->searchBreed)) {
            $query->whereHas('breeds', function ($q) {
                $q->where('breed_name', 'like', '%' . $this->searchBreed . '%');
            });
        }

        // Lista de raças únicas cadastradas no banco para popular o filtro select
        $availableBreeds = RelatedBreed::select('breed_name')
            ->groupBy('breed_name')
            ->orderBy('breed_name')
            ->pluck('breed_name');

        return view('livewire.kennel-list', [
            // Canis verificados (VIPs) aparecem primeiro na listagem
            'kennels' => $query->orderBy('is_verified', 'desc')->latest()->paginate(9),
            'availableBreeds' => $availableBreeds
        ]);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
}
