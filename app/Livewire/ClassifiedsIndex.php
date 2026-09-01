<?php

namespace App\Livewire;

use App\Models\Classified;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;

class ClassifiedsIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $condition = '';
    public $city = '';
    public $state = '';

    public function updatingSearch() { $this->resetPage(); }
    public function updatingCity() { $this->resetPage(); }
    public function updatingState() { $this->resetPage(); }

    #[Layout('layouts.app')]
    public function render()
    {
        // Busca cidades e estados únicos que possuem anúncios para preencher os selects
        $locations = DB::table('suppliers')
            ->join('classifieds', 'suppliers.id', '=', 'classifieds.supplier_id')
            ->select('suppliers.city', 'suppliers.state')
            ->distinct()
            ->get();

        $ads = Classified::query()
            ->where('is_active', true)
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->condition, function($query) {
                $query->where('condition', $this->condition);
            })
            ->when($this->city, function($query) {
                $query->whereHas('supplier', function($q) {
                    $q->where('city', $this->city);
                });
            })
            ->when($this->state, function($query) {
                $query->whereHas('supplier', function($q) {
                    $q->where('state', $this->state);
                });
            })
            ->with('supplier')
            ->latest()
            ->paginate(12);

        return view('livewire.classifieds-index', [
            'ads' => $ads,
            'availableLocations' => $locations
        ]);
    }
}
