<div class="p-6 relative">
    {{-- Topo --}}
    <div class="flex flex-col md:flex-row justify-between items-start mb-10 gap-6">
        <div>
            <h1 class="text-2xl font-black text-gray-900 uppercase italic tracking-tighter">
                Gestão de <span class="text-indigo-600">Fornecedores</span>
            </h1>
        </div>

        {{-- CORRIGIDO: Envolvido em um form com submit preventivo e adicionado indicador de carregamento --}}
        <form wire:submit.prevent="import" enctype="multipart/form-data" class="flex flex-col items-end gap-2 bg-white p-4 rounded-3xl shadow-sm border border-gray-100">
            <div class="flex items-center gap-4">
                <div class="relative">
                    <input type="file" wire:model="fileXls" id="upload_xls" class="text-[9px] font-bold text-gray-400 focus:outline-none">
                    {{-- Exibe mensagem visual enquanto o Livewire faz o upload temporário do XLS no seu PC --}}
                    <div wire:loading wire:target="fileXls" class="text-[9px] text-indigo-600 font-black uppercase mt-1 block animate-pulse">
                        ⏳ Enviando arquivo para o sistema...
                    </div>
                </div>

                <button type="submit"
                        wire:loading.attr="disabled"
                        wire:target="fileXls"
                        class="bg-indigo-600 text-white px-6 py-3 rounded-2xl font-black uppercase text-[10px] tracking-widest hover:bg-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading wire:target="import">Processando...</span>
                    <span wire:loading.remove wire:target="import">Importar XLS</span>
                </button>
            </div>
            @error('fileXls') <span class="text-red-500 text-[9px] font-black uppercase tracking-tight pr-2">{{ $message }}</span> @enderror
        </form>
    </div>

    {{-- Abas --}}
    <div class="flex gap-4 mb-6">
        <button wire:click="setStatus('pending')" class="px-8 py-4 rounded-2xl font-black uppercase text-[10px] tracking-widest transition-all {{ $status === 'pending' ? 'bg-indigo-600 text-white shadow-xl' : 'bg-white text-gray-400 border border-gray-100' }}">Pendentes</button>
        <button wire:click="setStatus('approved')" class="px-8 py-4 rounded-2xl font-black uppercase text-[10px] tracking-widest transition-all {{ $status === 'approved' ? 'bg-amber-500 text-white shadow-xl' : 'bg-white text-gray-400 border border-gray-100' }}">Aprovados</button>
    </div>

    {{-- Filtros Avançados --}}
    <div class="mb-8 bg-white p-6 rounded-[2.5rem] shadow-sm border border-gray-100 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="text-[9px] font-black uppercase text-gray-400 mb-2 block ml-2">Pesquisa</label>
            <input type="text" wire:model.live="search" placeholder="Nome ou Email..." class="w-full bg-gray-50 border-none rounded-2xl p-4 text-xs font-bold focus:ring-4 focus:ring-indigo-500/10">
        </div>
        <div>
            <label class="text-[9px] font-black uppercase text-gray-400 mb-2 block ml-2">Categoria</label>
            <select wire:model.live="filterCategory" class="w-full bg-gray-50 border-none rounded-2xl p-4 text-xs font-bold focus:ring-4 focus:ring-indigo-500/10">
                <option value="">Todas</option>
                @foreach($categories as $cat) <option value="{{ $cat->slug }}">{{ $cat->name }}</option> @endforeach
            </select>
        </div>
        <div>
            <label class="text-[9px] font-black uppercase text-gray-400 mb-2 block ml-2">Estado (UF)</label>
            <select wire:model.live="filterState" class="w-full bg-gray-50 border-none rounded-2xl p-4 text-xs font-bold focus:ring-4 focus:ring-indigo-500/10">
                <option value="">Todos</option>
                @foreach($states as $uf) <option value="{{ $uf }}">{{ $uf }}</option> @endforeach
            </select>
        </div>
        <div>
            <label class="text-[9px] font-black uppercase text-gray-400 mb-2 block ml-2">Cidade</label>
            <select wire:model.live="filterCity" class="w-full bg-gray-50 border-none rounded-2xl p-4 text-xs font-bold focus:ring-4 focus:ring-indigo-500/10" {{ empty($cities) ? 'disabled' : '' }}>
                <option value="">Todas</option>
                @foreach($cities as $c) <option value="{{ $c }}">{{ $c }}</option> @endforeach
            </select>
        </div>
    </div>

    {{-- Ações em Massa --}}
    @if(count($selectedSuppliers) > 0)
        <div class="fixed bottom-10 left-1/2 -translate-x-1/2 z-50 bg-indigo-900 text-white px-8 py-4 rounded-[2.5rem] shadow-2xl flex items-center gap-8 border border-white/10 animate-in slide-in-from-bottom duration-300">
            <p class="text-[10px] font-black uppercase">{{ count($selectedSuppliers) }} itens</p>
            <button wire:click="{{ $status === 'pending' ? 'approveSelected' : 'revokeSelected' }}" class="text-[10px] font-black uppercase tracking-widest text-green-400 hover:scale-105 transition">Processar Seleção</button>
        </div>
    @endif

    {{-- Feedback --}}
    @if (session()->has('message'))
        <div class="bg-indigo-600 text-white p-4 rounded-2xl font-black uppercase text-[10px] mb-6 text-center shadow-lg">{{ session('message') }}</div>
    @endif

    {{-- Tabela --}}
    <div class="bg-white rounded-[3rem] shadow-2xl overflow-hidden border border-gray-100">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50/50 border-b border-gray-100">
                    <th class="p-8 w-10"><input type="checkbox" wire:model.live="selectAll" class="w-5 h-5 rounded-lg border-gray-200 text-indigo-600 transition"></th>
                    <th class="p-8 text-[10px] font-black uppercase text-gray-400">Fornecedor / Localização</th>
                    <th class="p-8 text-[10px] font-black uppercase text-gray-400 text-right">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $supplier)
                    <tr class="border-b border-gray-50 hover:bg-indigo-50/20 transition-colors {{ in_array($supplier->id, $selectedSuppliers) ? 'bg-indigo-50/40' : '' }}" wire:key="supp-{{ $supplier->id }}">
                        <td class="p-8"><input type="checkbox" wire:model.live="selectedSuppliers" value="{{ $supplier->id }}" class="w-5 h-5 rounded-lg border-gray-200 text-indigo-600 transition"></td>
                        <td class="p-8">
                            <p class="font-black text-gray-900 uppercase text-sm">{{ $supplier->name }}</p>
                            <div class="flex gap-2 mt-1">
                                <span class="text-[8px] font-black px-2 py-0.5 rounded bg-indigo-50 text-indigo-500 uppercase">{{ str_replace('-', ' ', $supplier->category) }}</span>
                                <span class="text-[8px] font-bold text-gray-400 uppercase tracking-tighter">{{ $supplier->city }} - {{ $supplier->state }}</span>
                                <span class="text-[8px] font-bold text-green-600 uppercase tracking-tighter border-l border-gray-200 pl-2">
                                    {{ $supplier->phone ?? 'Sem Telefone' }}
                                </span>
                            </div>
                        </td>
                        <td class="p-8 text-right flex justify-end gap-2">
                            <button wire:click="edit({{ $supplier->id }})" class="p-3 bg-gray-50 text-gray-400 rounded-2xl hover:bg-indigo-600 hover:text-white transition shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </button>
                            @if($status === 'pending')
                                <button wire:click="approve({{ $supplier->id }})" class="bg-green-500 text-white px-6 py-3 rounded-2xl font-black uppercase text-[9px] shadow-lg shadow-green-100">Aprovar</button>
                            @else
                                <button wire:click="toggleVerify({{ $supplier->id }})" class="p-3 rounded-2xl {{ $supplier->is_verified ? 'bg-amber-100 text-amber-600 ring-2 ring-amber-400' : 'bg-gray-100 text-gray-400' }}">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                </button>
                                <button wire:click="revoke({{ $supplier->id }})" class="bg-red-50 text-red-500 px-6 py-3 rounded-2xl font-black uppercase text-[9px] shadow-sm">Revogar</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="p-20 text-center opacity-30 font-black uppercase text-xs">Nada encontrado</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal de Edição --}}
    @if($isEditing)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-indigo-950/40 backdrop-blur-sm">
            <div class="bg-white w-full max-w-2xl rounded-[3rem] shadow-2xl p-10 animate-in zoom-in duration-300">
                <h2 class="text-xl font-black uppercase italic text-gray-900 mb-8 border-l-4 border-indigo-600 pl-4">Editar Fornecedor</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2"><label class="text-[10px] font-black text-gray-400 uppercase mb-2 block">Nome</label><input type="text" wire:model="editName" class="w-full bg-gray-50 border-none rounded-2xl p-4 font-bold text-xs"></div>
                    <div><label class="text-[10px] font-black text-gray-400 uppercase mb-2 block">Categoria</label>
                        <select wire:model="editCategory" class="w-full bg-gray-50 border-none rounded-2xl p-4 font-bold text-xs">
                            @foreach($categories as $cat) <option value="{{ $cat->slug }}">{{ $cat->name }}</option> @endforeach
                        </select>
                    </div>
                    <div><label class="text-[10px] font-black text-gray-400 uppercase mb-2 block">E-mail</label><input type="email" wire:model="editEmail" class="w-full bg-gray-50 border-none rounded-2xl p-4 font-bold text-xs"></div>

                    <div>
                        <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">Telefone Fixo/Contato</label>
                        <input type="text" wire:model="editPhone" class="w-full bg-gray-50 border-none rounded-2xl p-4 font-bold text-xs">
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase mb-2 block text-green-600">WhatsApp (Apenas Números)</label>
                        <input type="text" wire:model="editWhatsapp" class="w-full bg-gray-50 border-none rounded-2xl p-4 font-bold text-xs" placeholder="5511999999999">
                    </div>
                    <div><label class="text-[10px] font-black text-gray-400 uppercase mb-2 block">Cidade</label><input type="text" wire:model="editCity" class="w-full bg-gray-50 border-none rounded-2xl p-4 font-bold text-xs"></div>
                    <div><label class="text-[10px] font-black text-gray-400 uppercase mb-2 block">UF</label><input type="text" wire:model="editState" class="w-full bg-gray-50 border-none rounded-2xl p-4 font-bold text-xs"></div>
                </div>
                <div class="flex justify-end gap-4 mt-10">
                    <button wire:click="cancelEdit" class="text-[10px] font-black uppercase text-gray-400">Cancelar</button>
                    <button wire:click="update" class="bg-indigo-600 text-white px-10 py-4 rounded-2xl font-black uppercase text-[10px] shadow-xl shadow-indigo-100">Salvar</button>
                </div>
            </div>
        </div>
    @endif

    <div class="mt-10"> {{ $suppliers->links() }} </div>
</div>
