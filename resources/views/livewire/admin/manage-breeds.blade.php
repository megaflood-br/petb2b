<div class="space-y-8">

    @if (session()->has('message'))
        <div class="bg-green-50 p-4 rounded-xl border border-green-100 text-green-600 uppercase text-[10px] font-black">{{ session('message') }}</div>
    @endif

    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 border-b pb-6">
        <div>
            <h1 class="text-2xl font-black text-gray-900 uppercase italic tracking-tight">Guia de Raças</h1>
            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mt-0.5">Cadastre espécies e raças do guia público</p>
        </div>
        <div class="flex gap-3">
            <input type="text" wire:model.live.debounce.400ms="search" placeholder="Buscar..." class="bg-gray-50 border border-gray-100 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500">
            <button wire:click="toggleForm" class="bg-brand-500 hover:bg-brand-600 text-white px-6 py-3 rounded-xl font-black uppercase text-[10px] tracking-wider transition whitespace-nowrap">
                {{ $showForm ? 'Fechar' : '+ Nova Raça' }}
            </button>
        </div>
    </div>

    @if($showForm)
        <form wire:submit.prevent="save" class="bg-white border border-gray-100 rounded-[2rem] p-8 shadow-sm space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Nome da Raça</label>
                    <input type="text" wire:model="name" class="w-full bg-gray-50 border-none rounded-xl p-3.5 focus:ring-2 focus:ring-brand-500">
                    @error('name') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Espécie</label>
                    <select wire:model="species" class="w-full bg-gray-50 border-none rounded-xl p-3.5 focus:ring-2 focus:ring-brand-500">
                        @foreach($speciesList as $sp)<option value="{{ $sp }}">{{ $sp }}</option>@endforeach
                    </select>
                    @error('species') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Porte</label>
                    <select wire:model="size" class="w-full bg-gray-50 border-none rounded-xl p-3.5 focus:ring-2 focus:ring-brand-500">
                        <option value="">—</option>
                        @foreach($sizesList as $sz)<option value="{{ $sz }}">{{ $sz }}</option>@endforeach
                    </select>
                    @error('size') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Origem</label>
                    <input type="text" wire:model="origin" class="w-full bg-gray-50 border-none rounded-xl p-3.5 focus:ring-2 focus:ring-brand-500">
                    @error('origin') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Temperamento</label>
                    <input type="text" wire:model="temperament" placeholder="Ex: Dócil, brincalhão, protetor" class="w-full bg-gray-50 border-none rounded-xl p-3.5 focus:ring-2 focus:ring-brand-500">
                    @error('temperament') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Descrição</label>
                    <textarea wire:model="description" rows="6" class="w-full bg-gray-50 border-none rounded-xl p-3.5 focus:ring-2 focus:ring-brand-500"></textarea>
                    @error('description') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Imagem</label>
                    <input type="file" wire:model="image" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-brand-50 file:text-brand-700">
                    @error('image') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div class="flex items-center gap-3 mt-6">
                    <input type="checkbox" wire:model="is_active" id="is_active" class="w-5 h-5 rounded-lg border-gray-300 text-brand-600 focus:ring-brand-500">
                    <label for="is_active" class="text-[10px] font-black uppercase text-gray-700 tracking-widest cursor-pointer">Ativa (visível no guia)</label>
                </div>
            </div>

            <div class="flex gap-3 pt-4 border-t border-gray-100">
                <button type="button" wire:click="toggleForm" class="w-1/3 bg-gray-100 text-gray-700 p-4 rounded-xl font-black uppercase tracking-widest hover:bg-gray-200 transition">Cancelar</button>
                <button type="submit" class="flex-1 bg-brand-500 text-white p-4 rounded-xl font-black uppercase tracking-widest hover:bg-brand-600 transition">{{ $breedId ? 'Salvar' : 'Cadastrar' }}</button>
            </div>
        </form>
    @endif

    <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-gray-950 text-white text-[9px] uppercase tracking-wider">
                <tr>
                    <th class="p-4">Raça</th>
                    <th class="p-4">Espécie</th>
                    <th class="p-4">Porte</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-right">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($breeds as $breed)
                    <tr class="text-xs">
                        <td class="p-4 font-black text-gray-900 uppercase">{{ $breed->name }}</td>
                        <td class="p-4 text-gray-600">{{ $breed->species }}</td>
                        <td class="p-4 text-gray-600">{{ $breed->size ?: '—' }}</td>
                        <td class="p-4">
                            <span class="text-[8px] font-black uppercase px-2 py-1 rounded-full {{ $breed->is_active ? 'bg-green-500/10 text-green-600' : 'bg-gray-200 text-gray-500' }}">{{ $breed->is_active ? 'Ativa' : 'Inativa' }}</span>
                        </td>
                        <td class="p-4 text-right space-x-2 whitespace-nowrap">
                            <button wire:click="edit({{ $breed->id }})" class="text-[9px] font-black uppercase tracking-wider px-3 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition">Editar</button>
                            <button wire:click="delete({{ $breed->id }})" wire:confirm="Remover esta raça?" class="text-[9px] font-black uppercase tracking-wider px-3 py-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition">Excluir</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-10 text-center text-gray-400 font-bold uppercase tracking-widest text-xs">Nenhuma raça cadastrada.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $breeds->links() }}</div>
</div>
