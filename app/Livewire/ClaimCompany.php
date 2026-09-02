<?php

namespace App\Livewire;

use App\Models\CompanyClaim;
use Livewire\Attributes\Layout;
use Livewire\Component;

class ClaimCompany extends Component
{
    public $supplierId;
    public $name = '';
    public $email = '';
    public $message = '';
    public $showModal = false;
    public $hasPendingClaim = false;

    public function mount($supplierId)
    {
        $this->supplierId = $supplierId;

        if (auth()->check()) {
            $this->name = auth()->user()->name;
            $this->email = auth()->user()->email;

            $this->hasPendingClaim = CompanyClaim::where('supplier_id', $this->supplierId)
                ->where('user_id', auth()->id())
                ->where('status', 'pending')
                ->exists();
        }
    }

    public function submitClaim()
    {
        $this->validate([
            'name' => 'required|min:3|max:120',
            'email' => 'required|email|max:150',
            'message' => 'required|min:10',
        ]);

        CompanyClaim::create([
            'supplier_id' => $this->supplierId,
            'user_id' => auth()->id(), // null quando visitante
            'claimant_name' => $this->name,
            'claimant_email' => $this->email,
            'message' => $this->message,
            'status' => 'pending',
        ]);

        $this->showModal = false;
        $this->reset('message');
        $this->hasPendingClaim = auth()->check();

        session()->flash('message', 'Solicitação de reivindicação enviada! Após a aprovação do administrador, você receberá um e-mail para ativar o acesso à sua empresa.');
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.claim-company');
    }
}
