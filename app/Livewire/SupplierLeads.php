<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Lead;
use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;

class SupplierLeads extends Component
{
    public function markAsRead($id)
    {
        $lead = Lead::findOrFail($id);
        $lead->update(['is_read' => true]);
    }

    public function render()
    {
        $supplier = Supplier::where('user_id', Auth::id())->first();

        $leads = $supplier
            ? Lead::where('supplier_id', $supplier->id)->latest()->get()
            : collect();

        return view('livewire.supplier-leads', [
            'leads' => $leads
        ]);
    }
}
