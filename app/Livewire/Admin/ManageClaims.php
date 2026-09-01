<?php

namespace App\Livewire\Admin;

use App\Models\CompanyClaim;
use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

class ManageClaims extends Component
{
    use WithPagination;

    #[Layout('layouts.admin')]
    public function render()
    {
        // Pegamos as solicitações pendentes carregando os relacionamentos
        $claims = CompanyClaim::with(['supplier', 'user'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(10);

        return view('livewire.admin.manage-claims', [
            'claims' => $claims
        ]);
    }

    public function approve($claimId)
    {
        $claim = CompanyClaim::findOrFail($claimId);
        $supplier = $claim->supplier;

        // 1. Vincula o usuário como dono da empresa
        $supplier->update([
            'user_id' => $claim->user_id,
            'is_verified' => true // Aproveitamos para dar o selo de verificado
        ]);

        // 2. Marca a solicitação como aprovada
        $claim->update(['status' => 'approved']);

        session()->flash('message', "Empresa '{$supplier->name}' agora pertence a {$claim->user->name}.");
    }

    public function reject($claimId)
    {
        CompanyClaim::findOrFail($claimId)->update(['status' => 'rejected']);
        session()->flash('error', "Solicitação rejeitada.");
    }
}
