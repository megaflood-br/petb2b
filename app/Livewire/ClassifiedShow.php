<?php

namespace App\Livewire;

use App\Models\Classified;
use Livewire\Component;
use Livewire\Attributes\Layout;

class ClassifiedShow extends Component
{
    public $ad;

    public function mount($classified)
    {
        // Busca o anúncio principal pelo slug
        $this->ad = Classified::with('supplier')->where('slug', $classified)->firstOrFail();
    }

    #[Layout('layouts.app')]
    public function render()
    {
        // Busca outros anúncios do MESMO fornecedor, excluindo o atual
        $otherAds = Classified::where('supplier_id', $this->ad->supplier_id)
            ->where('id', '!=', $this->ad->id)
            ->where('is_active', true)
            ->take(3)
            ->get();

        return view('livewire.classified-show', [
            'otherAds' => $otherAds
        ]);
    }
}
