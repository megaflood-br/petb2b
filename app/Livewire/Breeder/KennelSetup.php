<?php

namespace App\Livewire\Breeder;

use App\Models\Kennel;
use App\Models\RelatedBreed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;

class KennelSetup extends Component
{
    use WithFileUploads;

    public $name, $affix, $registration_number, $description, $city = 'Atibaia', $state = 'SP';
    public $whatsapp, $instagram, $logo, $cover_image, $breeds_input;

    protected $rules = [
        'name' => 'required|min:3|unique:kennels,name',
        'city' => 'required',
        'state' => 'required',
        'logo' => 'nullable|image|max:1024',
        'cover_image' => 'nullable|image|max:2048',
        'whatsapp' => 'required',
    ];

    #[Layout('layouts.app')] // Usa o layout padrão do usuário logado
    public function render()
    {
        return view('livewire.breeder.kennel-setup');
    }

    public function save()
    {
        $this->validate();

        $data = [
            'user_id' => Auth::id(),
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'affix' => $this->affix,
            'registration_number' => $this->registration_number,
            'description' => $this->description,
            'city' => $this->city,
            'state' => $this->state,
            'whatsapp' => $this->whatsapp,
            'instagram' => $this->instagram,
            'is_active' => true, // Fica ativo automaticamente ao criar
            'is_verified' => false, // Só o admin dá o selo VIP depois
        ];

        if ($this->logo) {
            $data['logo'] = $this->logo->store('kennels/logos', 'public');
        }

        if ($this->cover_image) {
            $data['cover_image'] = $this->cover_image->store('kennels/covers', 'public');
        }

        $kennel = Kennel::create($data);

        // Processa e vincula as raças digitadas
        if ($this->breeds_input) {
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

        return redirect()->route('breeder.dashboard');
    }
}
