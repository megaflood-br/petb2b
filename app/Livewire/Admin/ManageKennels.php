<?php

namespace App\Livewire\Admin;

use App\Models\Kennel;
use App\Models\User;
use App\Models\RelatedBreed;
use App\Models\KennelImage;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ManageKennels extends Component
{
    use WithPagination, WithFileUploads;

    public $name, $affix, $registration_number, $description, $city = 'Atibaia', $state = 'SP';
    public $whatsapp, $instagram, $user_id, $logo, $cover_image, $kennelId, $existingLogo, $existingCover;
    public $is_active = true;
    public $is_verified = false;
    public $breeds_input;

    // NOVAS PROPRIEDADES PARA GALERIA
    public $gallery = []; // Para receber múltiplos uploads de uma vez
    public $existingGallery = []; // Para listar na edição

    public $showForm = false;

    protected $rules = [
        'name' => 'required|min:3',
        'user_id' => 'required|exists:users,id',
        'city' => 'required',
        'state' => 'required',
        'logo' => 'nullable|image|max:1024',
        'cover_image' => 'nullable|image|max:2048',
        'gallery.*' => 'nullable|image|max:3072', // Valida cada imagem da galeria (Max 3MB)
        'whatsapp' => 'nullable',
        'instagram' => 'nullable',
    ];

    #[Layout('layouts.admin')]
    public function render()
    {
        return view('livewire.admin.manage-kennels', [
            'kennels' => Kennel::with('user')->latest()->paginate(10),
            'users' => User::orderBy('name')->get()
        ]);
    }

    public function toggleForm()
    {
        $this->reset([
            'name', 'affix', 'registration_number', 'description', 'city', 'state',
            'whatsapp', 'instagram', 'user_id', 'logo', 'cover_image', 'kennelId',
            'existingLogo', 'existingCover', 'breeds_input', 'is_active', 'is_verified',
            'gallery', 'existingGallery'
        ]);
        $this->showForm = !$this->showForm;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'user_id' => $this->user_id,
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'affix' => $this->affix,
            'registration_number' => $this->registration_number,
            'description' => $this->description,
            'city' => $this->city,
            'state' => $this->state,
            'whatsapp' => $this->whatsapp,
            'instagram' => $this->instagram,
            'is_active' => $this->is_active,
            'is_verified' => $this->is_verified,
        ];

        if ($this->logo) {
            if ($this->kennelId && $this->existingLogo) {
                Storage::disk('public')->delete($this->existingLogo);
            }
            $data['logo'] = $this->logo->store('kennels/logos', 'public');
        }

        if ($this->cover_image) {
            if ($this->kennelId && $this->existingCover) {
                Storage::disk('public')->delete($this->existingCover);
            }
            $data['cover_image'] = $this->cover_image->store('kennels/covers', 'public');
        }

        $kennel = Kennel::updateOrCreate(['id' => $this->kennelId], $data);

        // Processa as Raças
        if ($this->breeds_input) {
            RelatedBreed::where('kennel_id', $kennel->id)->delete();
            $breeds = explode(',', $this->breeds_input);
            foreach ($breeds as $breed) {
                $breedName = trim($breed);
                if (!empty($breedName)) {
                    RelatedBreed::create([
                        'kennel_id' => $kennel->id,
                        'breed_name' => $breedName
                    ]);
                }
            }
        }

        // SALVA AS FOTOS DA NOVA GALERIA (SE HOUVER)
        if (!empty($this->gallery)) {
            foreach ($this->gallery as $photo) {
                $path = $photo->store('kennels/gallery', 'public');
                KennelImage::create([
                    'kennel_id' => $kennel->id,
                    'image_path' => $path
                ]);
            }
        }

        session()->flash('message', $this->kennelId ? 'Canil e galeria atualizados!' : 'Canil cadastrado com sucesso!');
        $this->toggleForm();
    }

    public function edit($id)
    {
        $kennel = Kennel::findOrFail($id);
        $this->kennelId = $id;
        $this->user_id = $kennel->user_id;
        $this->name = $kennel->name;
        $this->affix = $kennel->affix;
        $this->registration_number = $kennel->registration_number;
        $this->description = $kennel->description;
        $this->city = $kennel->city;
        $this->state = $kennel->state;
        $this->whatsapp = $kennel->whatsapp;
        $this->instagram = $kennel->instagram;
        $this->is_active = $kennel->is_active;
        $this->is_verified = $kennel->is_verified;
        $this->existingLogo = $kennel->logo;
        $this->existingCover = $kennel->cover_image;

        $this->breeds_input = RelatedBreed::where('kennel_id', $id)->pluck('breed_name')->implode(', ');

        // CARREGA AS FOTOS EXISTENTES DA GALERIA
        $this->existingGallery = KennelImage::where('kennel_id', $id)->get()->toArray();

        $this->showForm = true;
    }

    // MÉTODO NOVO: Permite deletar fotos específicas direto pela tela de edição
    public function deletePhoto($photoId)
    {
        $photo = KennelImage::findOrFail($photoId);
        Storage::disk('public')->delete($photo->image_path);
        $photo->delete();

        // Atualiza a lista na tela
        $this->existingGallery = KennelImage::where('kennel_id', $this->kennelId)->get()->toArray();
    }

    public function delete($id)
    {
        $kennel = Kennel::findOrFail($id);

        if ($kennel->logo) Storage::disk('public')->delete($kennel->logo);
        if ($kennel->cover_image) Storage::disk('public')->delete($kennel->cover_image);

        // Deleta os arquivos de fotos da galeria vinculada do SSD
        foreach ($kennel->images as $img) {
            Storage::disk('public')->delete($img->image_path);
        }

        $kennel->delete();
        session()->flash('message', 'Canil e todos os seus arquivos removidos.');
    }
}
