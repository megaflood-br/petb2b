<div class="space-y-8">
    <div class="flex justify-between items-center">
        <h3 class="text-xl font-black text-gray-900 uppercase tracking-tight italic">Gerenciar <span class="text-indigo-600">Eventos</span></h3>
        <button wire:click="toggleForm" class="bg-indigo-600 text-white px-6 py-3 rounded-2xl font-black uppercase text-[10px] tracking-widest shadow-lg transition hover:bg-indigo-700">
            {{ $showForm ? 'Cancelar' : 'Novo Evento' }}
        </button>
    </div>

    @if (session()->has('message'))
        <div class="p-4 bg-green-50 text-green-700 rounded-2xl font-bold border border-green-100 text-xs uppercase tracking-widest">
            {{ session('message') }}
        </div>
    @endif

    @if($showForm)
        <form wire:submit.prevent="save" class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-xs font-black text-gray-400 uppercase mb-2 tracking-widest">Nome do Evento</label>
                    <input type="text" wire:model="title" class="w-full border-gray-100 bg-gray-50 rounded-2xl py-4 font-bold focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-black text-gray-400 uppercase mb-2 tracking-widest">Data de Início</label>
                    <input type="date" wire:model="start_date" class="w-full border-gray-100 bg-gray-50 rounded-2xl py-4 font-bold">
                </div>
                <div>
                    <label class="block text-xs font-black text-gray-400 uppercase mb-2 tracking-widest">Local</label>
                    <input type="text" wire:model="location" class="w-full border-gray-100 bg-gray-50 rounded-2xl py-4 font-bold">
                </div>
                <div>
                    <label class="block text-xs font-black text-gray-400 uppercase mb-2 tracking-widest">Cidade</label>
                    <input type="text" wire:model="city" class="w-full border-gray-100 bg-gray-50 rounded-2xl py-4 font-bold">
                </div>
                <div>
                    <label class="block text-xs font-black text-gray-400 uppercase mb-2 tracking-widest">Estado</label>
                    <input type="text" wire:model="state" class="w-full border-gray-100 bg-gray-50 rounded-2xl py-4 font-bold">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-black text-gray-400 uppercase mb-2 tracking-widest">Banner do Evento</label>
                    <input type="file" wire:model="image" class="text-xs">
                    @if($existingImage && !$image)
                        <img src="{{ asset('storage/'.$existingImage) }}" class="mt-4 w-32 h-20 rounded-xl object-cover border">
                    @endif
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-black text-gray-400 uppercase mb-2 tracking-widest">Link Externo</label>
                    <input type="url" wire:model="external_link" class="w-full border-gray-100 bg-gray-50 rounded-2xl py-4 font-bold">

                </div>
                {{-- Adicione este bloco exatamente acima do botão SALVAR ALTERAÇÕES --}}
                <div class="md:col-span-2">
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-gray-400 mb-2">
                            Sobre o Evento / Informações Gerais
                        </label>
                        <textarea
                            wire:model="description"
                            rows="5"
                            class="w-full bg-gray-50 border border-gray-100 rounded-2xl p-4 text-sm font-medium text-gray-700 focus:outline-none focus:border-brand-500 transition resize-none"
                            placeholder="Digite os detalhes, cronograma e informações gerais do evento..."></textarea>
                        @error('description') <span class="text-red-500 text-xs font-bold mt-1 block uppercase tracking-tight">{{ $message }}</span> @enderror
                    </div>
                </div>

            </div>
            <button type="submit" class="bg-indigo-600 text-white px-12 py-5 rounded-2xl font-black uppercase text-xs tracking-widest">
                {{ $editingEventId ? 'Salvar Alterações' : 'Criar Evento' }}
            </button>
        </form>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($events as $event)
            <div class="bg-white p-6 rounded-[2rem] border border-gray-100 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 bg-gray-100 rounded-xl overflow-hidden shrink-0 border border-gray-100">
                        @if($event->image) <img src="{{ asset('storage/'.$event->image) }}" class="w-full h-full object-cover"> @endif
                    </div>
                    <div>
                        <h4 class="font-black text-gray-900 uppercase text-[10px]">{{ $event->title }}</h4>
                        <p class="text-gray-400 text-[9px]">{{ \Carbon\Carbon::parse($event->start_date)->format('d/m/Y') }}</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button wire:click="edit({{ $event->id }})" class="p-2 text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-600 hover:text-white transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    </button>
                    <button onclick="confirm('Excluir evento?') || event.stopImmediatePropagation()" wire:click="delete({{ $event->id }})" class="p-2 text-red-600 bg-red-50 rounded-lg hover:bg-red-600 hover:text-white transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </div>
            </div>
        @endforeach
    </div>
</div>
