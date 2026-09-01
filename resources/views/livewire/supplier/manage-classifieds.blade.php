<div class="space-y-8">
    <div class="flex justify-between items-center">
        <h3 class="text-xl font-black text-gray-900 uppercase tracking-tight italic">
            Gerenciar <span class="text-indigo-600">Classificados</span>
        </h3>
        <button wire:click="toggleForm" class="bg-indigo-600 text-white px-6 py-3 rounded-2xl font-black uppercase text-[10px] tracking-widest shadow-lg shadow-indigo-100 transition hover:bg-indigo-700">
            {{ $showForm ? 'Cancelar' : 'Novo Anúncio' }}
        </button>
    </div>

    @if (session()->has('message'))
        <div class="p-4 bg-green-50 text-green-700 rounded-2xl font-bold border border-green-100 text-xs uppercase tracking-widest">
            {{ session('message') }}
        </div>
    @endif

    @if($showForm)
        <form wire:submit.prevent="save" class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm space-y-6">
            <h4 class="font-black uppercase text-xs text-gray-400 tracking-widest">{{ $editingAdId ? 'Editar Anúncio' : 'Informações do Produto' }}</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-xs font-black text-gray-400 uppercase mb-2 tracking-widest">Título do Anúncio</label>
                    <input type="text" wire:model="title" class="w-full border-gray-100 bg-gray-50 rounded-2xl py-4 font-bold focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-black text-gray-400 uppercase mb-2 tracking-widest">Preço (R$)</label>
                    <input type="number" step="0.01" wire:model="price" class="w-full border-gray-100 bg-gray-50 rounded-2xl py-4 font-bold focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-black text-gray-400 uppercase mb-2 tracking-widest">Condição</label>
                    <select wire:model="condition" class="w-full border-gray-100 bg-gray-50 rounded-2xl py-4 font-bold focus:ring-indigo-500">
                        <option value="Novo">Novo</option>
                        <option value="Usado">Usado</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-black text-gray-400 uppercase mb-2 tracking-widest">Descrição Detalhada</label>
                    <textarea wire:model="description" rows="4" class="w-full border-gray-100 bg-gray-50 rounded-2xl py-4 font-bold focus:ring-indigo-500"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-black text-gray-400 uppercase mb-2 tracking-widest">Foto do Produto</label>
                    <input type="file" wire:model="image" class="text-xs font-black uppercase">
                    @if($existingImage && !$image)
                        <img src="{{ asset('storage/'.$existingImage) }}" class="mt-4 w-20 h-20 rounded-xl border object-cover">
                    @endif
                </div>
            </div>
            <button type="submit" class="w-full md:w-auto bg-green-600 text-white px-12 py-5 rounded-2xl font-black uppercase text-xs tracking-widest hover:bg-green-700 transition">
                {{ $editingAdId ? 'Salvar Alterações' : 'Publicar Agora' }}
            </button>
        </form>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($myAds as $ad)
            <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm flex items-center justify-between group">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 bg-gray-50 rounded-2xl overflow-hidden shrink-0 border border-gray-100">
                        @if($ad->image) <img src="{{ asset('storage/'.$ad->image) }}" class="w-full h-full object-cover"> @endif
                    </div>
                    <div>
                        <h4 class="font-black text-gray-900 uppercase text-[10px] tracking-tight">{{ $ad->title }}</h4>
                        <p class="text-indigo-600 font-bold text-sm">R$ {{ number_format($ad->price, 2, ',', '.') }}</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button wire:click="edit({{ $ad->id }})" class="p-3 bg-gray-50 text-indigo-600 rounded-xl hover:bg-indigo-600 hover:text-white transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    </button>
                    <button onclick="confirm('Excluir anúncio?') || event.stopImmediatePropagation()" wire:click="delete({{ $ad->id }})" class="p-3 bg-gray-50 text-red-600 rounded-xl hover:bg-red-600 hover:text-white transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </div>
            </div>
        @endforeach
    </div>
</div>
