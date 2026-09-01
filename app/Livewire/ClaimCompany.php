<?php

namespace App\Livewire;

use App\Models\CompanyClaim;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;

class ClaimCompany extends Component
{
    public $supplierId;
    public $message;
    public $showModal = false;

    public $hasPendingClaim = false;

public function mount($supplierId)
{
    $this->supplierId = $supplierId;
    // Verifica se o utilizador logado já tem um pedido pendente para esta empresa
    if (auth()->check()) {
        $this->hasPendingClaim = \App\Models\CompanyClaim::where('supplier_id', $this->supplierId)
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->exists();
    }
}

    public function submitClaim()
    {
        $this->validate([
            'message' => 'required|min:10',
        ]);

        // Se o usuário não estiver logado, salvamos a mensagem na sessão e mandamos para o registro
        if (!Auth::check()) {
            session(['pending_claim_msg' => $this->message, 'pending_claim_supplier' => $this->supplierId]);
            return redirect()->route('register');
        }

        // Se já estiver logado, salva direto (O Admin recebe aqui)
        CompanyClaim::create([
            'supplier_id' => $this->supplierId,
            'user_id' => Auth::id(),
            'message' => $this->message,
            'status' => 'pending',
        ]);

        $this->showModal = false;
        $this->reset('message');
        session()->flash('message', 'Sua solicitação foi enviada com sucesso!');
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.claim-company');
    }
    public function redirectToRegister()
{
    // Salva a URL atual na sessão para o Laravel saber onde voltar
    session(['url.intended' => url()->previous()]);

    return redirect()->route('register');
}


}
