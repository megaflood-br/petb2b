<div class="space-y-8">
    {{-- Cabeçalho --}}
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-black text-gray-900 uppercase italic">Guia de <span class="text-brand-500">Canis & Criadores</span></h1>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">Gerencie os criadores profissionais do portal</p>
        </div>
        <button wire:click="toggleForm" class="bg-brand-500 text-white px-6 py-3 rounded-2xl font-black uppercase text-xs tracking-widest hover:bg-brand-600 transition shadow-lg shadow-brand-100">
            {{ $showForm ? 'Voltar para Lista' : 'Novo Canil' }}
        </button>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-50 p-4 rounded-2xl border border-green-100 text-green-600 text-[10px] font-black uppercase">
            {{ session('message') }}
        </div>
    @endif

    @if($showForm)
        <div class="bg-white p-10 rounded-[2.5rem] border border-gray-100 shadow-sm">
            <form wire:submit.prevent="save" class="space-y-6">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">Dono / Usuário Associado</label>
                        <select wire:model="user_id" class="w-full bg-gray-50 border-none rounded-xl p-4 font-bold text-gray-900 focus:ring-2 focus:ring-brand-500">
                            <option value="">Selecione o Criador</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                        @error('user_id') <span class="text-red-500 text-[10px] font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">Nome do Canil</label>
                        <input type="text" wire:model="name" placeholder="Ex: Canil de Atibaia Goldens" class="w-full bg-gray-50 border-none rounded-xl p-4 font-bold text-gray-900 focus:ring-2 focus:ring-brand-500">
                        @error('name') <span class="text-red-500 text-[10px] font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">Afixo do Canil (Opcional)</label>
                        <input type="text" wire:model="affix" placeholder="Ex: Von Atibaia" class="w-full bg-gray-50 border-none rounded-xl p-4 font-bold text-gray-900 focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">Registro CBKC / FCI</label>
                        <input type="text" wire:model="registration_number" placeholder="Ex: 1234/26" class="w-full bg-gray-50 border-none rounded-xl p-4 font-bold text-gray-900 focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">Raças Que Cria (Separe por vírgula)</label>
                        <input type="text" wire:model="breeds_input" placeholder="Ex: Golden Retriever, Border Collie" class="w-full bg-gray-50 border-none rounded-xl p-4 font-bold text-gray-900 focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>

                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">Descrição da Criação</label>
                    <textarea wire:model="description" rows="4" placeholder="Fale sobre as diretrizes de saúde, exames dos parents..." class="w-full bg-gray-50 border-none rounded-xl p-4 font-bold text-gray-900 focus:ring-2 focus:ring-brand-500"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">Cidade</label>
                        <input type="text" wire:model="city" class="w-full bg-gray-50 border-none rounded-xl p-4 font-bold text-gray-900 focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">Estado (UF)</label>
                        <input type="text" wire:model="state" class="w-full bg-gray-50 border-none rounded-xl p-4 font-bold text-gray-900 focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">WhatsApp de Contato</label>
                        <input type="text" wire:model="whatsapp" placeholder="11999999999" class="w-full bg-gray-50 border-none rounded-xl p-4 font-bold text-gray-900 focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">Instagram (Sem @)</label>
                        <input type="text" wire:model="instagram" placeholder="canilatibaia" class="w-full bg-gray-50 border-none rounded-xl p-4 font-bold text-gray-900 focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">Logo do Canil (.jpg ou .png)</label>
                        <input type="file" wire:model="logo" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-brand-50 file:text-brand-700">
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">Imagem de Capa / Fachada</label>
                        <input type="file" wire:model="cover_image" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-brand-50 file:text-brand-700">
                    </div>
                </div>

                {{-- BLOCO NOVO: COMPONENTE DE UPLOAD DA GALERIA --}}
                <div class="bg-gray-50 p-6 rounded-[2rem] border border-gray-100 space-y-4">
                    <div>
                        <label class="text-[10px] font-black uppercase text-brand-600 mb-2 block tracking-widest font-black">Galeria de Fotos (Envie várias de uma vez)</label>
                        <input type="file" wire:model="gallery" multiple class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-brand-50 file:text-brand-700">
                        @error('gallery.*') <span class="text-red-500 text-[10px] font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Mostra as fotos atuais prontas para deletar na edição --}}
                    @if(!empty($existingGallery))
                        <div>
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-3">Fotos na Galeria (Clique para Excluir):</p>
                            <div class="grid grid-cols-3 md:grid-cols-6 gap-3">
                                @foreach($existingGallery as $photo)
                                    <div wire:key="photo-{{ $photo['id'] }}" wire:click="deletePhoto({{ $photo['id'] }})" class="aspect-square bg-gray-200 rounded-xl overflow-hidden relative cursor-pointer group border border-gray-100">
                                        <img src="{{ asset('storage/' . $photo['image_path']) }}" class="w-full h-full object-cover group-hover:opacity-40 transition">
                                        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition text-red-500 font-black text-[9px] uppercase tracking-tighter">
                                            Excluir
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="flex gap-6">
                    <div class="flex items-center gap-3 bg-gray-50 p-4 rounded-2xl flex-1 border border-gray-100">
                        <input type="checkbox" wire:model="is_active" id="is_active" class="w-5 h-5 text-brand-500 rounded-lg border-gray-300 focus:ring-brand-500">
                        <label for="is_active" class="text-[10px] font-black uppercase text-gray-700 tracking-widest cursor-pointer">Canil Ativo no Portal</label>
                    </div>
                    <div class="flex items-center gap-3 bg-brand-50/50 p-4 rounded-2xl flex-1 border border-brand-100">
                        <input type="checkbox" wire:model="is_verified" id="is_verified" class="w-5 h-5 text-brand-600 rounded-lg border-gray-300 focus:ring-brand-500">
                        <label for="is_verified" class="text-[10px] font-black uppercase text-brand-900 tracking-widest cursor-pointer">⭐ Criador Verificado (VIP)</label>
                    </div>
                </div>

                <button type="submit" class="w-full bg-gray-900 text-white p-5 rounded-2xl font-black uppercase tracking-widest hover:bg-brand-500 transition shadow-lg">
                    Salvar Dados do Canil
                </button>
            </form>
        </div>
    @else
        {{-- Listagem --}}
        <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden text-xs">
            <table class="w-full text-left">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="py-4 px-6 font-black text-gray-400 uppercase tracking-widest">Canil</th>
                        <th class="py-4 px-6 font-black text-gray-400 uppercase tracking-widest">Criador Associado</th>
                        <th class="py-4 px-6 font-black text-gray-400 uppercase tracking-widest">Localização</th>
                        <th class="py-4 px-6 font-black text-gray-400 uppercase tracking-widest">Status</th>
                        <th class="py-4 px-6 font-black text-gray-400 uppercase tracking-widest text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($kennels as $k)
                        <tr class="hover:bg-gray-50/50 transition font-bold">
                            <td class="py-6 px-6 text-gray-900 uppercase">
                                <div class="flex items-center gap-2">
                                    <span>{{ $k->name }}</span>
                                    @if($k->is_verified)
                                        <span class="text-amber-500 text-sm">★</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-6 px-6 text-gray-500 font-medium">{{ $k->user->name }}</td>
                            <td class="py-6 px-6 text-gray-400 uppercase">{{ $k->city }}/{{ $k->state }}</td>
                            <td class="py-6 px-6">
                                <span class="px-2.5 py-1 rounded-full text-[9px] font-black uppercase {{ $k->is_active ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600' }}">
                                    {{ $k->is_active ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td class="py-6 px-6 text-right space-x-3">
                                <button wire:click="edit({{ $k->id }})" class="text-brand-600 uppercase">Editar</button>
                                <button onclick="confirm('Tem certeza que deseja remover este canil?') || event.stopImmediatePropagation()" wire:click="delete({{ $k->id }})" class="text-red-400 uppercase">Excluir</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-gray-400 italic">Nenhum canil cadastrado localmente.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $kennels->links() }}</div>
    @endif
</div>
