<?php

namespace App\Livewire\Admin;

use App\Models\Advertisement;
use App\Models\Supplier;
use App\Support\Settings;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ManageAds extends Component
{
    use WithPagination, WithFileUploads;

    // Modal de Edição Comercial (ajuste de taxas)
    public $isModalOpen = false;
    public $selectedAdId;
    public $title, $cost_per_click, $cost_per_impression, $is_active;

    // Modal de Criação Manual (admin cria anúncio para uma empresa)
    public $isCreateModalOpen = false;
    public $newSupplierId;
    public $newTitle, $newLink, $newPosition, $newImage;
    public $newCostPerClick, $newCostPerImpression;
    public bool $newIsActive = true;

    protected $rules = [
        'cost_per_click' => 'required|numeric|min:0',
        'cost_per_impression' => 'required|numeric|min:0',
        'is_active' => 'boolean',
    ];

    public function render()
    {
        $ads = Advertisement::with('supplier')
            ->latest()
            ->paginate(10);

        return view('livewire.admin.manage-ads', [
            'ads' => $ads,
            'suppliers' => Supplier::orderBy('name')->get(['id', 'name']),
            'positions' => Advertisement::getPositions(),
        ])->layout('layouts.admin');
    }

    // ----- Criação manual -----

    public function openCreateModal()
    {
        $this->reset(['newSupplierId', 'newTitle', 'newLink', 'newPosition', 'newImage']);
        $this->newCostPerClick = Settings::adsCostPerClick();
        $this->newCostPerImpression = Settings::adsCostPerImpression();
        $this->newIsActive = true;
        $this->isCreateModalOpen = true;
    }

    public function createAd()
    {
        $validated = $this->validate([
            'newSupplierId' => 'required|exists:suppliers,id',
            'newTitle' => 'required|min:3|max:150',
            'newLink' => 'required|url',
            'newPosition' => 'required|in:' . implode(',', array_keys(Advertisement::getPositions())),
            'newImage' => 'required|image|max:2048',
            'newCostPerClick' => 'required|numeric|min:0',
            'newCostPerImpression' => 'required|numeric|min:0',
        ]);

        $path = $this->newImage->store('advertisements/banners', 'public');

        Advertisement::create([
            'supplier_id' => $this->newSupplierId,
            'title' => $this->newTitle,
            'link' => $this->newLink,
            'position' => $this->newPosition,
            'image_path' => $path,
            'is_active' => $this->newIsActive,
            'clicks' => 0,
            'views' => 0,
            'cost_per_click' => $this->newCostPerClick,
            'cost_per_impression' => $this->newCostPerImpression,
        ]);

        $this->closeCreateModal();
        session()->flash('message', 'Anúncio criado manualmente e vinculado à empresa com sucesso!');
    }

    public function closeCreateModal()
    {
        $this->isCreateModalOpen = false;
        $this->reset(['newSupplierId', 'newTitle', 'newLink', 'newPosition', 'newImage', 'newCostPerClick', 'newCostPerImpression', 'newIsActive']);
    }

    // ----- Edição de taxas (existente) -----

    public function editAd($id)
    {
        $ad = Advertisement::findOrFail($id);

        $this->selectedAdId = $ad->id;
        $this->title = $ad->title;
        $this->cost_per_click = $ad->cost_per_click;
        $this->cost_per_impression = $ad->cost_per_impression;
        $this->is_active = $ad->is_active;

        $this->isModalOpen = true;
    }

    public function saveAdSettings()
    {
        $this->validate();

        $ad = Advertisement::findOrFail($this->selectedAdId);

        $ad->update([
            'cost_per_click' => $this->cost_per_click,
            'cost_per_impression' => $this->cost_per_impression,
            'is_active' => $this->is_active,
        ]);

        $this->isModalOpen = false;
        $this->reset(['selectedAdId', 'title', 'cost_per_click', 'cost_per_impression', 'is_active']);

        session()->flash('message', 'Parâmetros financeiros do anúncio atualizados com sucesso!');
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->reset(['selectedAdId', 'title', 'cost_per_click', 'cost_per_impression', 'is_active']);
    }
}
