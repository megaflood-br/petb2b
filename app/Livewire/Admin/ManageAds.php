<?php

namespace App\Livewire\Admin;

use App\Models\Advertisement;
use Livewire\Component;
use Livewire\WithPagination;

class ManageAds extends Component
{
    use WithPagination;

    // Propriedades para o Modal de Edição Comercial
    public $isModalOpen = false;
    public $selectedAdId;
    public $title, $cost_per_click, $cost_per_impression, $is_active;

    protected $rules = [
        'cost_per_click' => 'required|numeric|min:0',
        'cost_per_impression' => 'required|numeric|min:0',
        'is_active' => 'boolean',
    ];

    public function render()
    {
        // Busca todos os anúncios do portal paginados para o Admin gerenciar
        $ads = Advertisement::with('supplier')
            ->latest()
            ->paginate(10);

        return view('livewire.admin.manage-ads', [
            'ads' => $ads
        ])->layout('layouts.admin'); // Certifique-se de que o seu layout do admin se chama assim
    }

    /**
     * Carrega os dados do anúncio e abre o modal de edição
     */
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

    /**
     * Salva os novos valores ajustados pelo administrador
     */
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

    /**
     * Fecha o modal de edição
     */
    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->reset(['selectedAdId', 'title', 'cost_per_click', 'cost_per_impression', 'is_active']);
    }
}
