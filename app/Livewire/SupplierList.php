<?php

namespace App\Livewire;

use App\Models\Supplier;
use App\Models\Category; // Importamos o modelo de Categorias real
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

class SupplierList extends Component
{
    use WithPagination;

    public $search = '';
    public $category = '';
    public $state = '';
    public $city = '';

    // Reseta a paginação ao digitar ou mudar filtros para não "sumir" com os resultados
    public function updatingSearch() { $this->resetPage(); }
    public function updatingCategory() { $this->resetPage(); }
    public function updatingState() { $this->resetPage(); $this->city = ''; }
    public function updatingCity() { $this->resetPage(); }

    public function mount()
    {
        // Captura a busca vinda da Home via URL (Ex: ?search=banho)
        $this->search = request()->query('search', $this->search);
    }

    #[Layout('layouts.app')]
    public function render()
    {
        // Query principal refletindo apenas fornecedores aprovados e ativos
        $suppliers = Supplier::where('is_approved', true) // Filtro de aprovação corrigido
            ->where('is_active', true)
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->category, function($query) {
                $query->where('category', $this->category);
            })
            ->when($this->state, function($query) {
                $query->where('state', $this->state);
            })
            ->when($this->city, function($query) {
                $query->where('city', $this->city);
            })
            ->orderBy('is_verified', 'desc') // Fornecedores VIP aparecem primeiro
            ->latest()
            ->paginate(9);

        // Busca dados dinâmicos para preencher os selects do filtro na View
        return view('livewire.supplier-list', [
            'suppliers' => $suppliers,
            // Agora buscamos as categorias da tabela oficial criada pelo Seeder
            'categories' => Category::orderBy('name')->get(),
            'states' => Supplier::where('is_approved', true)->select('state')->distinct()->whereNotNull('state')->orderBy('state')->pluck('state'),
            'cities' => Supplier::where('is_approved', true)->when($this->state, function($q) {
                            $q->where('state', $this->state);
                        })->select('city')->distinct()->whereNotNull('city')->orderBy('city')->pluck('city')
        ]);
    }
}
