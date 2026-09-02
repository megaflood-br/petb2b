<?php

namespace App\Livewire\Admin;

use App\Mail\ClaimApprovedMail;
use App\Models\CompanyClaim;
use App\Models\Supplier;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
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
        $claim = CompanyClaim::with(['supplier', 'user'])->findOrFail($claimId);
        $supplier = $claim->supplier;

        // Empresa fica online/verificada ao aprovar.
        $supplier->update([
            'is_verified' => true,
            'is_active' => true,
            'is_approved' => true,
        ]);
        $claim->update(['status' => 'approved']);

        if ($claim->user_id) {
            // Reivindicador já tem conta: vincula como dono e promove a fornecedor.
            $supplier->update(['user_id' => $claim->user_id]);
            if ($claim->user) {
                $claim->user->forceFill(['role' => 'supplier'])->save();
            }

            $to = $claim->claimant_email ?? optional($claim->user)->email;
            if ($to) {
                Mail::to($to)->send(new ClaimApprovedMail($claim->fresh(['supplier', 'user']), null));
            }

            session()->flash('message', "Empresa '{$supplier->name}' vinculada e acesso liberado. E-mail de boas-vindas enviado.");

            return;
        }

        // Reivindicador sem conta: gera link tokenizado de cadastro e envia por e-mail.
        $token = Str::random(64);
        $claim->update([
            'approval_token' => $token,
            'approval_token_expires_at' => now()->addDays(7),
        ]);

        if ($claim->claimant_email) {
            Mail::to($claim->claimant_email)->send(
                new ClaimApprovedMail($claim->fresh('supplier'), route('claim.register', $token))
            );
        }

        session()->flash('message', "Reivindicação de '{$supplier->name}' aprovada. E-mail com link de cadastro enviado ao reivindicador.");
    }

    public function reject($claimId)
    {
        CompanyClaim::findOrFail($claimId)->update(['status' => 'rejected']);
        session()->flash('error', "Solicitação rejeitada.");
    }
}
