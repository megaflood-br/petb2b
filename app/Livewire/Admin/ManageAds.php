<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\WithInfiniteScroll;
use App\Models\Advertisement;
use App\Models\Supplier;
use App\Support\Settings;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ManageAds extends Component
{
    use WithPagination, WithFileUploads, WithInfiniteScroll;

    protected function infiniteIncrement(): int
    {
        return 10;
    }

    public $isModalOpen = false;
    public $isEditing = false;
    public $editingAdId;
    public $existingImagePath;

    public $supplierId;
    public $title, $link, $position, $image;
    public $cost_per_click, $cost_per_impression;
    public $is_active = true;
    public bool $skip_credits = false;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('access-admin'), 403);
    }

    public function render()
    {
        $ads = Advertisement::with('supplier')
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.admin.manage-ads', [
            'ads' => $ads,
            'suppliers' => Supplier::orderBy('name')->get(['id', 'name']),
            'positions' => Advertisement::getPositions(),
        ])->layout('layouts.admin');
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->cost_per_click = Settings::adsCostPerClick();
        $this->cost_per_impression = Settings::adsCostPerImpression();
        $this->is_active = true;
        $this->skip_credits = false;
        $this->isEditing = false;
        $this->isModalOpen = true;
    }

    public function editAd($id)
    {
        $ad = Advertisement::findOrFail($id);

        $this->resetValidation();
        $this->image = null;
        $this->editingAdId = $ad->id;
        $this->existingImagePath = $ad->image_path;
        $this->supplierId = $ad->supplier_id;
        $this->title = $ad->title;
        $this->link = $ad->link;
        $this->position = $ad->position;
        $this->cost_per_click = $ad->cost_per_click;
        $this->cost_per_impression = $ad->cost_per_impression;
        $this->is_active = (bool) $ad->is_active;
        $this->skip_credits = (bool) $ad->skip_credits;
        $this->isEditing = true;
        $this->isModalOpen = true;
    }

    public function createAd()
    {
        $this->validate($this->formRules(imageRequired: true));

        $path = $this->image->store('advertisements/banners', 'public');

        Advertisement::create($this->adPayload($path));

        $this->closeModal();
        session()->flash('message', 'Anúncio criado manualmente e vinculado à empresa com sucesso!');
    }

    public function updateAd()
    {
        $ad = Advertisement::findOrFail($this->editingAdId);

        $this->validate($this->formRules(imageRequired: false));

        $data = $this->adPayload();

        if ($this->image) {
            if ($ad->image_path) {
                Storage::disk('public')->delete($ad->image_path);
            }
            $data['image_path'] = $this->image->store('advertisements/banners', 'public');
        }

        $ad->update($data);

        $this->closeModal();
        session()->flash('message', 'Campanha atualizada com sucesso!');
    }

    public function deleteAd($id)
    {
        $ad = Advertisement::findOrFail($id);

        if ($ad->image_path) {
            Storage::disk('public')->delete($ad->image_path);
        }

        $ad->delete();

        session()->flash('message', 'Campanha de anúncio removida permanentemente do portal.');
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetForm();
    }

    /**
     * @return array<string, mixed>
     */
    private function formRules(bool $imageRequired): array
    {
        return [
            'supplierId' => 'required|exists:suppliers,id',
            'title' => 'required|min:3|max:150',
            'link' => 'required|url',
            'position' => 'required|in:' . implode(',', array_keys(Advertisement::getPositions())),
            'image' => ($imageRequired ? 'required' : 'nullable') . '|image|max:2048',
            'cost_per_click' => 'required|numeric|min:0',
            'cost_per_impression' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'skip_credits' => 'boolean',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function adPayload(?string $imagePath = null): array
    {
        $data = [
            'supplier_id' => $this->supplierId,
            'title' => $this->title,
            'link' => $this->link,
            'position' => $this->position,
            'is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN),
            'skip_credits' => (bool) $this->skip_credits,
            'cost_per_click' => $this->cost_per_click,
            'cost_per_impression' => $this->cost_per_impression,
        ];

        if ($imagePath !== null) {
            $data['image_path'] = $imagePath;
            $data['clicks'] = 0;
            $data['views'] = 0;
        }

        return $data;
    }

    private function resetForm(): void
    {
        $this->reset([
            'supplierId',
            'title',
            'link',
            'position',
            'image',
            'existingImagePath',
            'cost_per_click',
            'cost_per_impression',
            'is_active',
            'skip_credits',
            'isEditing',
            'editingAdId',
        ]);
        $this->resetValidation();
    }
}
