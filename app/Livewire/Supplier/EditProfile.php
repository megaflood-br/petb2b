<?php

namespace App\Livewire\Supplier;

use App\Models\Supplier;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditProfile extends Component
{
    use WithFileUploads;

    public $supplierId;
    public $name, $email, $website, $category, $cnpj, $description, $address;
    public $city, $state; // ADICIONADO: Mapeamento de colunas separadas do banco
    public $whatsapp, $phone;
    public $logo, $existingLogo;

    protected $rules = [
        'name' => 'required|min:3',
        'email' => 'required|email',
        'category' => 'required',
        'description' => 'required|min:10',
        'address' => 'required',
        'city' => 'required',  // Obrigatórios para não quebrar a busca regional
        'state' => 'required', // Obrigatórios para não quebrar a busca regional
        'website' => 'nullable',
        'cnpj' => 'nullable',
        'whatsapp' => 'nullable|min:10',
        'phone' => 'nullable|min:10',
        'logo' => 'nullable|image|max:2048',
    ];

    public function mount()
    {
        $supplier = Supplier::firstOrCreate(
            ['user_id' => Auth::id()],
            [
                'name' => Auth::user()->name ?? 'Empresa Nova',
                'slug' => Str::slug(Auth::user()->name ?? 'empresa-nova'),
                'email' => Auth::user()->email ?? 'contacto@empresa.com',
                'description' => 'Preencha a descrição da sua empresa aqui.',
                'address' => 'Preencha o seu endereço comercial.',
                'city' => 'Atibaia',
                'state' => 'SP',
                'category' => '',
                'is_active' => true,
                'is_approved' => true
            ]
        );

        $this->supplierId = $supplier->id;
        $this->name = $supplier->name;
        $this->email = $supplier->email;
        $this->website = $supplier->website;
        $this->category = $supplier->category;
        $this->cnpj = $supplier->cnpj;
        $this->description = $supplier->description;
        $this->address = $supplier->address;
        $this->city = $supplier->city;   // Carrega Cidade do banco
        $this->state = $supplier->state; // Carrega Estado do banco
        $this->whatsapp = $supplier->whatsapp;
        $this->phone = $supplier->phone;
        $this->existingLogo = $supplier->logo;
    }

    public function render()
    {
        $categoriesList = class_exists(Category::class) ? Category::orderBy('name')->get() : collect();

        return view('livewire.supplier.edit-profile', [
            'categoriesList' => $categoriesList
        ]);
    }

    public function save()
    {
        $this->validate();

        $supplier = Supplier::findOrFail($this->supplierId);

        // AUTOMATIZAÇÃO DE SEO INTELIGENTE:
        // Cria títulos e descrições otimizados para o Google indexar a nível regional
        $computedSeoTitle = "{$this->name} | Fornecedor de {$this->category} em {$this->city} - {$this->state}";
        $computedSeoDescription = Str::limit("Procurando por {$this->category}? Conheça a empresa {$this->name} localizada em {$this->city}. Veja o catálogo B2B completo, classificados, fotos e contatos diretos no portal.", 155);

        $data = [
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'email' => $this->email,
            'website' => $this->website,
            'category' => $this->category,
            'cnpj' => $this->cnpj,
            'description' => $this->description,
            'address' => $this->address,
            'city' => $this->city,   // Salva Cidade isolada
            'state' => $this->state, // Salva Estado isolado
            'whatsapp' => $this->whatsapp,
            'phone' => $this->phone,

            // Injeção automatizada nos campos de SEO do seu banco
            'seo_title' => $computedSeoTitle,
            'seo_description' => $computedSeoDescription,
        ];

        if ($this->logo) {
            if ($this->existingLogo) {
                Storage::disk('public')->delete($this->existingLogo);
            }
            $data['logo'] = $this->logo->store('suppliers/logos', 'public');
            $this->existingLogo = $data['logo'];
        }

        $supplier->update($data);

        session()->flash('message', 'Perfil corporativo e metatags de SEO atualizados com sucesso!');
        $this->reset('logo');
    }
}
