<?php

namespace App\Livewire\Breeder;

use App\Models\Kennel;
use App\Models\KennelImage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ManagePhotos extends Component
{
    use WithFileUploads;

    public $photos = [];
    public $kennelId;

    protected $rules = [
        'photos.*' => 'required|image|max:3072', // Valida cada foto (Max 3MB)
    ];

    public function mount()
    {
        // Puxa o canil do usuário logado
        $kennel = Kennel::where('user_id', Auth::id())->firstOrFail();
        $this->kennelId = $kennel->id;
    }

    public function render()
    {
        return view('livewire.breeder.manage-photos', [
            'existingPhotos' => KennelImage::where('kennel_id', $this->kennelId)->latest()->get()
        ]);
    }

    public function uploadPhotos()
    {
        $this->validate();

        foreach ($this->photos as $photo) {
            $path = $photo->store('kennels/gallery', 'public');
            KennelImage::create([
                'kennel_id' => $this->kennelId,
                'image_path' => $path
            ]);
        }

        $this->reset('photos');
        session()->flash('message', 'Fotos adicionadas com sucesso à sua galeria!');
    }

    public function deletePhoto($photoId)
    {
        $photo = KennelImage::where('id', $photoId)->where('kennel_id', $this->kennelId)->firstOrFail();
        Storage::disk('public')->delete($photo->image_path);
        $photo->delete();

        session()->flash('message', 'Foto removida da galeria.');
    }
}
