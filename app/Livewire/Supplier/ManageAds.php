<?php

namespace App\Livewire\Supplier;

use App\Models\Advertisement;
use App\Models\PixCharge;
use App\Models\Supplier;
use App\Models\SupplierCreditTransaction;
use App\Services\Pix\PixGateway;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class ManageAds extends Component
{
    use WithFileUploads;

    public $supplier;
    public $title, $link, $position, $image;
    public $amount;

    // Estado da cobrança PIX em aberto (QR Code / copia e cola).
    public ?int $pixChargeId = null;
    public ?string $pixPayload = null;
    public ?string $pixQrImage = null;
    public ?string $pixExpiration = null;

    // Controles do Modal de Cadastro/Edição
    public $isModalOpen = false;
    public $isEditing = false;
    public $editingAdId;

    public function mount()
    {
        $this->supplier = Supplier::where('user_id', Auth::id())->first();

        // Fornecedor recém-cadastrado ainda sem perfil: o registro é criado no
        // dashboard (EditProfile). Redireciona para lá em vez de dar erro.
        if (! $this->supplier) {
            return $this->redirect(route('supplier.dashboard'), navigate: true);
        }
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

    /**
     * Gera uma cobrança PIX (QR Code dinâmico) no provedor. O saldo NÃO é
     * creditado aqui: o crédito acontece de forma assíncrona quando o webhook
     * de pagamento confirmado chega (idempotente).
     */
    public function generatePix(PixGateway $gateway)
    {
        $min = config('ads.recharge_min');
        $max = config('ads.recharge_max');

        $this->validate([
            'amount' => "required|numeric|min:{$min}|max:{$max}",
        ], [
            'amount.min' => 'O valor mínimo de recarga é R$ ' . number_format($min, 2, ',', '.'),
            'amount.max' => 'O valor máximo por recarga é R$ ' . number_format($max, 2, ',', '.'),
        ]);

        $result = $gateway->createCharge($this->supplier, (float) $this->amount);

        $charge = PixCharge::create([
            'supplier_id' => $this->supplier->id,
            'asaas_payment_id' => $result->paymentId,
            'amount' => $this->amount,
            'status' => $result->status,
            'pix_payload' => $result->payload,
            'pix_encoded_image' => $result->encodedImage,
            'pix_expiration' => $result->expiration ? Carbon::parse($result->expiration) : null,
        ]);

        $this->pixChargeId = $charge->id;
        $this->pixPayload = $result->payload;
        $this->pixQrImage = $result->encodedImage;
        $this->pixExpiration = $charge->pix_expiration?->format('d/m/Y');

        session()->flash('message', 'Cobrança PIX gerada! Escaneie o QR Code ou copie o código. O saldo é creditado automaticamente após o pagamento.');
    }

    /**
     * Consultado por wire:poll enquanto há uma cobrança em aberto: quando o
     * webhook creditar a cobrança, limpa o QR e atualiza o saldo na tela.
     */
    public function checkPixStatus()
    {
        if (! $this->pixChargeId) {
            return;
        }

        $charge = PixCharge::find($this->pixChargeId);

        if ($charge && $charge->isCredited()) {
            $this->reset(['amount', 'pixChargeId', 'pixPayload', 'pixQrImage', 'pixExpiration']);
            $this->supplier->refresh();
            session()->flash('message', 'Pagamento PIX confirmado! Seu saldo foi atualizado.');
        }
    }

    public function cancelPix()
    {
        $this->reset(['pixChargeId', 'pixPayload', 'pixQrImage', 'pixExpiration']);
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
