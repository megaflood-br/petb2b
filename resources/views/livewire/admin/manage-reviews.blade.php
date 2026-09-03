<div class="space-y-8">
    <div class="flex justify-between items-center">
        <h3 class="text-xl font-black text-gray-900 uppercase tracking-tight italic">Gerenciar <span class="text-indigo-600">Análises</span></h3>
        <button wire:click="toggleForm" class="bg-indigo-600 text-white px-6 py-3 rounded-2xl font-black uppercase text-[10px] tracking-widest shadow-lg transition hover:bg-indigo-700">
            {{ $showForm ? 'Cancelar' : 'Nova Análise' }}
        </button>
    </div>

    @if (session()->has('message'))
        <div class="p-4 bg-green-50 text-green-700 rounded-2xl font-bold border border-green-100 text-xs uppercase tracking-widest">
            {{ session('message') }}
        </div>
    @endif

    @if($showForm)
        <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm">
            <form wire:submit.prevent="save" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-black text-gray-400 uppercase mb-2 tracking-widest">Título</label>
                        <input type="text" wire:model="title" class="w-full border-gray-100 bg-gray-50 rounded-2xl py-4 font-bold focus:ring-indigo-500">
                        @error('title') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase mb-2 tracking-widest">Categoria</label>
                        <input type="text" wire:model="category" class="w-full border-gray-100 bg-gray-50 rounded-2xl py-4 font-bold">
                        @error('category') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-xs font-black text-gray-400 uppercase mb-2 tracking-widest">Foto</label>
                        <input type="file" wire:model="image" class="text-xs">
                        @if($existingImage && !$image)
                            <img src="{{ asset('storage/'.$existingImage) }}" class="mt-4 w-20 h-20 rounded-xl object-cover border">
                        @endif
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-xs font-black text-gray-400 uppercase mb-2 tracking-widest">Descrição</label>
                        <textarea wire:model="content" rows="8" class="w-full border-gray-100 bg-gray-50 rounded-2xl p-4 text-sm font-medium"></textarea>
                        @error('content') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
                <button type="submit" class="bg-indigo-600 text-white px-12 py-5 rounded-2xl font-black uppercase text-xs tracking-widest shadow-lg hover:bg-indigo-700 transition">
                    {{ $editingReviewId ? 'Atualizar Análise' : 'Publicar Análise' }}
                </button>
            </form>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($reviews as $review)
            <div class="bg-white p-6 rounded-[2rem] border border-gray-100 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-gray-50 rounded-xl overflow-hidden shrink-0 flex items-center justify-center border border-gray-100">
                        @if($review->image)
                            <img src="{{ asset('storage/'.$review->image) }}" class="w-full h-full object-cover">
                        @else
                            <span class="font-black text-indigo-600 text-[10px] uppercase">Foto</span>
                        @endif
                    </div>
                    <div>
                        <h4 class="font-black text-gray-900 uppercase text-[10px]">{{ $review->title }}</h4>
                        <p class="text-gray-400 text-[9px] uppercase tracking-widest">{{ $review->category }}</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button wire:click="edit({{ $review->id }})" class="p-2 text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-600 hover:text-white transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    </button>
                    <button onclick="confirm('Excluir análise?') || event.stopImmediatePropagation()" wire:click="delete({{ $review->id }})" class="p-2 text-red-600 bg-red-50 rounded-lg hover:bg-red-600 hover:text-white transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </div>
            </div>
        @endforeach
    </div>
</div>
