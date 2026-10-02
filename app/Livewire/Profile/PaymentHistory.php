<?php

namespace App\Livewire\Profile;

use App\Models\PixCharge;
use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PaymentHistory extends Component
{
    public function mount(): void
    {
        abort_unless(Auth::check(), 403);
    }

    public function render()
    {
        $supplier = $this->supplier();

        $open = collect();
        $paid = collect();

        if ($supplier) {
            $charges = PixCharge::query()
                ->where('supplier_id', $supplier->id)
                ->latest()
                ->get();

            $open = $charges->filter(fn (PixCharge $charge) => ! $charge->isCredited())->values();
            $paid = $charges->filter(fn (PixCharge $charge) => $charge->isCredited())->values();
        }

        return view('livewire.profile.payment-history', [
            'supplier' => $supplier,
            'openCharges' => $open,
            'paidCharges' => $paid,
        ]);
    }

    private function supplier(): ?Supplier
    {
        return Auth::user()?->supplier;
    }
}
