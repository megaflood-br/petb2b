<?php

namespace App\Livewire\Admin;

use App\Models\Supplier;
use App\Models\Category;
use App\Imports\SuppliersImport;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;

class ApproveSuppliers extends Component
{
    use WithPagination, WithFileUploads;

    // Filtros e Navegação
    public $search = '';
    public $filterCategory = '';
    public $filterState = '';
    public $filterCity = '';
    public $status = 'pending';

    // Edição
    public $isEditing = false;
    public $editingId;
    public $editName, $editCategory, $editEmail, $editPhone, $editCity, $editState, $editWhatsapp;

    // Seleção e Arquivo
    public $selectedSuppliers = [];
    public $selectAll = false;
    public $fileXls;

    // Resetar página ao alterar filtros
    public function updatedSearch() { $this->resetPage(); }
    public function updatedFilterCategory() { $this->resetPage(); }
    public function updatedFilterState() { $this->resetPage(); $this->filterCity = ''; }
    public function updatedFilterCity() { $this->resetPage(); }

    #[Layout('layouts.admin')]
    public function render()
    {
        $suppliers = $this->getSuppliersProperty();

        // Dados para os Selects de Filtro
        $states = Supplier::select('state')->distinct()->whereNotNull('state')->orderBy('state')->pluck('state');
        $cities = $this->filterState
            ? Supplier::where('state', $this->filterState)->select('city')->distinct()->whereNotNull('city')->orderBy('city')->pluck('city')
            : [];

        return view('livewire.admin.approve-suppliers', [
            'suppliers' => $suppliers,
            'categories' => Category::orderBy('name')->get(),
            'states' => $states,
            'cities' => $cities,
        ]);
    }

    public function getSuppliersProperty()
    {
        return Supplier::query()
            ->where('is_approved', $this->status === 'approved')
            ->when($this->search, function($q) {
                $q->where(function($subQuery) {
                    $subQuery->where('name', 'like', "%{$this->search}%")
                             ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterCategory, fn($q) => $q->where('category', $this->filterCategory))
            ->when($this->filterState, fn($q) => $q->where('state', $this->filterState))
            ->when($this->filterCity, fn($q) => $q->where('city', $this->filterCity))
            ->latest()
            ->paginate(15);
    }

    // Ações de Edição
    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);
        $this->editingId = $id;
        $this->editName = $supplier->name;
        $this->editCategory = $supplier->category;
        $this->editEmail = $supplier->email;
        $this->editPhone = $supplier->phone;
        $this->editCity = $supplier->city;
        $this->editState = $supplier->state;
        $this->editWhatsapp = $supplier->whatsapp;
        $this->isEditing = true;
    }

    public function update()
    {
        $this->validate([
            'editName' => 'required|min:3',
            'editCategory' => 'required',
            'editEmail' => 'required|email',
            'editPhone' => 'nullable',
        ]);

        Supplier::find($this->editingId)->update([
            'name' => $this->editName,
            'category' => $this->editCategory,
            'email' => $this->editEmail,
            'phone' => $this->editPhone,
            'whatsapp' => $this->editWhatsapp,
            'city' => $this->editCity,
            'state' => $this->editState,
        ]);

        $this->isEditing = false;
        session()->flash('message', 'DADOS ATUALIZADOS COM SUCESSO!');
    }

    public function cancelEdit() { $this->isEditing = false; }

    // Ações em Massa
    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedSuppliers = $this->getSuppliersProperty()->pluck('id')->map(fn($id) => (string)$id)->toArray();
        } else {
            $this->selectedSuppliers = [];
        }
    }

    public function approveSelected()
    {
        Supplier::whereIn('id', $this->selectedSuppliers)->update(['is_approved' => true]);
        $this->reset(['selectedSuppliers', 'selectAll']);
        session()->flash('message', 'SELECIONADOS APROVADOS!');
    }

    public function revokeSelected()
    {
        Supplier::whereIn('id', $this->selectedSuppliers)->update(['is_approved' => false]);
        $this->reset(['selectedSuppliers', 'selectAll']);
        session()->flash('message', 'APROVAÇÕES REVOGADAS!');
    }

    // Ações Individuais
    public function approve($id) {
        Supplier::find($id)->update(['is_approved' => true]);
        session()->flash('message', 'FORNECEDOR APROVADO!');
    }

    public function revoke($id) {
        Supplier::find($id)->update(['is_approved' => false]);
        session()->flash('message', 'APROVAÇÃO REVOGADA!');
    }

    public function toggleVerify($id) {
        $s = Supplier::find($id);
        $s->update(['is_verified' => !$s->is_verified]);
    }

    public function setStatus($status)
    {
        $this->status = $status;
        $this->reset(['selectedSuppliers', 'selectAll', 'search', 'filterCategory', 'filterState', 'filterCity']);
        $this->resetPage();
    }

    /**
     * MÓDULO DE IMPORTAÇÃO CORRIGIDO
     */
    public function import()
    {
        $this->validate([
            'fileXls' => 'required|mimes:xlsx,xls,csv|max:10240'
        ]);

        try {
            // CORREÇÃO CRÍTICA: Extrai o caminho físico real (string) do arquivo temporário do Livewire
            Excel::import(new SuppliersImport, $this->fileXls->getRealPath());

            $this->reset('fileXls');
            session()->flash('message', 'IMPORTAÇÃO CONCLUÍDA COM SUCESSO!');
        } catch (\Exception $e) {
            session()->flash('message', 'ERRO AO IMPORTAR: ' . $e->getMessage());
        }
    }
}
