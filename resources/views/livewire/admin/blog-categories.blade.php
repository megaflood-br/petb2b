<?php
use Livewire\Volt\Component;
use App\Models\BlogCategory;
use Illuminate\Support\Str;

new class extends Component {
    public $name = '';

    public function save()
    {
        $validated = $this->validate([
            'name' => 'required|min:3|unique:blog_categories,name',
        ]);

        BlogCategory::create([
            'name' => $this->name,
            'slug' => Str::slug($this->name),
        ]);

        $this->name = ''; // Limpa o campo
        session()->flash('message', 'Categoria criada com sucesso!');
    }

    public function delete($id)
    {
        BlogCategory::find($id)->delete();
    }

    public function with()
    {
        return [
            'categories' => BlogCategory::orderBy('name')->get(),
        ];
    }
}; ?>

<div class="p-8">
    <h2 class="text-2xl font-black uppercase italic mb-8">Categorias do Blog</h2>

    {{-- Formulário de Cadastro --}}
    <form wire:submit="save" class="mb-12 bg-white p-8 rounded-[2rem] shadow-sm border border-gray-100">
        <div class="flex gap-4">
            <input type="text" wire:model="name" placeholder="Nome da Categoria (ex: Saúde, Mercado, Eventos)"
                   class="flex-1 bg-gray-50 border-none rounded-2xl p-4 font-bold text-xs focus:ring-4 focus:ring-brand-500/10">
            <button type="submit" class="bg-brand-500 text-white px-8 rounded-2xl font-black uppercase text-xs tracking-widest shadow-lg shadow-brand-100">
                Cadastrar
            </button>
        </div>
        @error('name') <span class="text-red-500 text-[10px] font-bold mt-2 block">{{ $message }}</span> @enderror
    </form>

    {{-- Listagem --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($categories as $cat)
            <div class="bg-white p-6 rounded-3xl flex justify-between items-center shadow-sm border border-gray-50">
                <span class="font-bold text-gray-700 uppercase text-xs tracking-wider">{{ $cat->name }}</span>
                <button wire:click="delete({{ $cat->id }})" class="text-red-400 hover:text-red-600 transition">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        @endforeach
    </div>
</div>
