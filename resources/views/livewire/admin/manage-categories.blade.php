<div class="space-y-8">
    {{-- Cabeçalho --}}
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-black text-gray-900 uppercase italic">Categorias do <span class="text-brand-500">Blog</span></h1>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">Gerencie os assuntos das suas notícias</p>
        </div>
        <button wire:click="toggleForm" class="bg-brand-500 text-white px-6 py-3 rounded-2xl font-black uppercase text-xs tracking-widest hover:bg-brand-600 transition shadow-lg shadow-brand-100">
            {{ $showForm ? 'Voltar para Lista' : 'Nova Categoria' }}
        </button>
    </div>

    {{-- Alertas de Sucesso ou Erro --}}
    @if (session()->has('message'))
        <div class="bg-green-50 p-4 rounded-2xl border border-green-100 text-green-600 text-[10px] font-black uppercase">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-50 p-4 rounded-2xl border border-red-100 text-red-600 text-[10px] font-black uppercase">
            {{ session('error') }}
        </div>
    @endif

    @if($showForm)
        {{-- Formulário de Cadastro/Edição --}}
        <div class="bg-white p-10 rounded-[2.5rem] border border-gray-100 shadow-sm max-w-xl animate-in fade-in slide-in-from-top-4 duration-300">
            <h2 class="text-xs font-black uppercase text-brand-600 mb-6 tracking-widest">
                {{ $categoryId ? 'Editando Assunto' : 'Cadastrando Novo Assunto' }}
            </h2>

            <form wire:submit.prevent="save" class="space-y-6">
                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block tracking-widest">Nome da Categoria</label>
                    <input type="text" wire:model="name" placeholder="Ex: Mercado, Saúde, Nutrição Animal..." class="w-full bg-gray-50 border-none rounded-xl p-4 font-bold text-gray-900 focus:ring-2 focus:ring-brand-500">
                    @error('name') <span class="text-red-500 text-[10px] font-bold mt-1 block">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="w-full bg-gray-900 text-white p-5 rounded-2xl font-black uppercase tracking-widest hover:bg-brand-600 transition shadow-lg">
                    Salvar Categoria
                </button>
            </form>
        </div>
    @else
        {{-- Listagem em Tabela --}}
        <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden text-xs max-w-3xl">
            <table class="w-full text-left">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="py-4 px-6 font-black text-gray-400 uppercase tracking-widest">Nome</th>
                        <th class="py-4 px-6 font-black text-gray-400 uppercase tracking-widest">Slug (URL)</th>
                        <th class="py-4 px-6 font-black text-gray-400 uppercase tracking-widest text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($categories as $category)
                        <tr class="hover:bg-gray-50/50 transition font-bold">
                            <td class="py-6 px-6 text-gray-900 uppercase tracking-wider">{{ $category->name }}</td>
                            <td class="py-6 px-6 text-gray-400 font-mono">{{ $category->slug }}</td>
                            <td class="py-6 px-6 text-right space-x-3">
                                <button wire:click="edit({{ $category->id }})" class="text-brand-600 uppercase">Editar</button>
                                <button onclick="confirm('Tem certeza? Isso pode afetar posts antigos.') || event.stopImmediatePropagation()" wire:click="delete({{ $category->id }})" class="text-red-400 uppercase">Excluir</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-8 text-center text-gray-400 italic">
                                Nenhuma categoria cadastrada ainda. Clique em "Nova Categoria" para começar!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4 max-w-3xl">{{ $categories->links() }}</div>
    @endif
</div>
