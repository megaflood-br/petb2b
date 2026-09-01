<?php

namespace App\Livewire\Supplier;

use App\Models\Advertisement;
use App\Models\Supplier;
use App\Models\SupplierCreditTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class ManageAds extends Component
{
    use WithFileUploads;

    public $supplier;
    public $title, $link, $position, $image;
    public $amount;

    // Controles do Modal de Cadastro/Edição
    public $isModalOpen = false;
    public $isEditing = false;
    public $editingAdId;

    public function mount()
    {
        $this->supplier = Supplier::where('user_id', Auth::id())->firstOrFail();
    }

    public function render()
    {
        $this->supplier->refresh();

        $myAds = Advertisement::where('supplier_id', $this->supplier->id)
            ->latest()
            ->get();

        $transactions = SupplierCreditTransaction::where('supplier_id', $this->supplier->id)
            ->latest()
            ->take(10)
            ->get();

        return view('livewire.supplier.manage-ads', [
            'myAds' => $myAds,
            'transactions' => $transactions
        ])->layout('layouts.supplier');
    }

    public function addCredits()
    {
        $min = config('ads.recharge_min');
        $max = config('ads.recharge_max');

        $this->validate([
            'amount' => "required|numeric|min:{$min}|max:{$max}",
        ], [
            'amount.min' => 'O valor mínimo de recarga é R$ ' . number_format($min, 2, ',', '.'),
            'amount.max' => 'O valor máximo por recarga é R$ ' . number_format($max, 2, ',', '.'),
        ]);

        DB::transaction(function () {
            $this->supplier->increment('credit_balance', $this->amount);

            SupplierCreditTransaction::create([
                'supplier_id' => $this->supplier->id,
                'type' => 'deposit',
                'amount' => $this->amount,
                'description' => 'Recarga de saldo via PIX (Simulado Local)',
            ]);

            Advertisement::where('supplier_id', $this->supplier->id)
                ->where('is_active', false)
                ->update(['is_active' => true]);
        });

        $this->supplier->refresh();
        $this->reset('amount');

        session()->flash('message', 'Créditos adicionados e campanhas reativadas com sucesso!');
    }

    /**
     * Abre o modal limpo para criar uma nova campanha
     */
    public function openCreateModal()
    {
        $this->reset(['title', 'link', 'position', 'image', 'isEditing', 'editingAdId']);
        $this->isModalOpen = true;
    }

    /**
     * Carrega os dados e abre o modal para edição
     */
    public function editAd($id)
    {
        $ad = Advertisement::where('supplier_id', $this->supplier->id)->findOrFail($id);

        $this->editingAdId = $ad->id;
        $this->title = $ad->title;
        $this->link = $ad->link;
        $this->position = $ad->position;
        $this->image = null;
        $this->isEditing = true;
        $this->isModalOpen = true;
    }

    /**
     * Fecha o modal limpando o estado do formulário
     */
    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->reset(['title', 'link', 'position', 'image', 'isEditing', 'editingAdId']);
    }

    public function createAd()
    {
        $this->validate([
            'title' => 'required|min:3',
            'link' => 'required|url',
            'position' => 'required',
            'image' => 'required|image|max:2048',
        ]);

        $path = $this->image->store('advertisements/banners', 'public');

        Advertisement::create([
            'supplier_id' => $this->supplier->id,
            'title' => $this->title,
            'link' => $this->link,
            'position' => $this->position,
            'image_path' => $path,
            'is_active' => $this->supplier->credit_balance > 0,
            'cost_per_click' => config('ads.cost_per_click'),
            'cost_per_impression' => config('ads.cost_per_impression'),
        ]);

        $this->closeModal();
        session()->flash('message', 'Anúncio enviado com sucesso para a fila de exibição!');
    }

    public function updateAd()
    {
        $ad = Advertisement::where('supplier_id', $this->supplier->id)->findOrFail($this->editingAdId);

        $this->validate([
            'title' => 'required|min:3',
            'link' => 'required|url',
            'position' => 'required',
            'image' => 'nullable|image|max:2048',
        ]);

        $data = [
            'title' => $this->title,
            'link' => $this->link,
            'position' => $this->position,
        ];

        if ($this->image) {
            $data['image_path'] = $this->image->store('advertisements/banners', 'public');
        }

        $ad->update($data);

        $this->closeModal();
        session()->flash('message', 'Campanha de anúncio updated com sucesso!');
    }

    public function toggleStatus($id)
    {
        $ad = Advertisement::where('supplier_id', $this->supplier->id)->findOrFail($id);

        if (!$ad->is_active && $this->supplier->credit_balance <= 0) {
            session()->flash('error', 'Não é possível ativar campanhas sem saldo de créditos disponível.');
            return;
        }

        $ad->update([
            'is_active' => !$ad->is_active
        ]);

        session()->flash('message', 'Status da campanha alterado com sucesso!');
    }

    public function deleteAd($id)
    {
        $ad = Advertisement::where('supplier_id', $this->supplier->id)->findOrFail($id);
        $ad->delete();

        session()->flash('message', 'Campanha de anúncio removida permanentemente do portal.');
    }
}
