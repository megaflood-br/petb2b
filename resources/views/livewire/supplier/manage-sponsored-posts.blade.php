<div class="space-y-8">

    @if (session()->has('message'))
        <div class="bg-green-50 p-4 rounded-xl border border-green-100 text-green-600 uppercase text-[10px] font-black">{{ session('message') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="bg-red-50 p-4 rounded-xl border border-red-100 text-red-600 uppercase text-[10px] font-black">{{ session('error') }}</div>
    @endif

    {{-- Cabeçalho --}}
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 border-b pb-6">
        <div>
            <h1 class="text-2xl font-black text-gray-900 uppercase italic tracking-tight">Matérias Patrocinadas</h1>
            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mt-0.5">Publique um publieditorial da sua marca no blog do portal</p>
        </div>
        <div class="text-right">
            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Saldo · Custo por matéria</p>
            <p class="text-sm font-black text-gray-900">R$ {{ number_format($supplier->credit_balance, 2, ',', '.') }} <span class="text-gray-300">·</span> <span class="text-brand-600">R$ {{ number_format($cost, 2, ',', '.') }}</span></p>
        </div>
    </div>

    <button wire:click="toggleForm" class="bg-brand-500 hover:bg-brand-600 text-white px-6 py-3.5 rounded-xl font-black uppercase text-[10px] tracking-wider transition shadow-md shadow-brand-500/10">
        {{ $showForm ? 'Fechar' : '+ Nova Matéria Patrocinada' }}
    </button>

    {{-- Formulário --}}
    @if($showForm)
        <form wire:submit.prevent="publish" class="bg-white border border-gray-100 rounded-[2rem] p-8 shadow-sm space-y-5">
            <div>
                <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Título da Matéria</label>
                <input type="text" wire:model="title" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-gray-900 focus:ring-2 focus:ring-brand-500">
                @error('title') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Categoria (opcional)</label>
                    <select wire:model="blogCategoryId" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-gray-900 focus:ring-2 focus:ring-brand-500">
                        <option value="">Sem categoria</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('blogCategoryId') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Imagem de Capa (opcional)</label>
                    <input type="file" wire:model="image" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-brand-50 file:text-brand-700">
                    @error('image') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Conteúdo</label>
                <textarea wire:model="content" rows="8" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-gray-900 focus:ring-2 focus:ring-brand-500"></textarea>
                @error('content') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="bg-amber-50 border border-amber-100 rounded-xl p-3 text-[10px] font-bold text-amber-800 normal-case">
                Ao publicar, serão debitados <strong>R$ {{ number_format($cost, 2, ',', '.') }}</strong> do seu saldo de créditos.
            </div>

            <div class="flex gap-3 pt-4 border-t border-gray-100">
                <button type="button" wire:click="toggleForm" class="w-1/3 bg-gray-100 text-gray-700 p-4 rounded-xl font-black uppercase tracking-widest hover:bg-gray-200 transition">Cancelar</button>
                <button type="submit" wire:loading.attr="disabled" class="flex-1 bg-brand-500 text-white p-4 rounded-xl font-black uppercase tracking-widest hover:bg-brand-600 transition disabled:opacity-50">
                    Publicar e Debitar R$ {{ number_format($cost, 2, ',', '.') }}
                </button>
            </div>
        </form>
    @endif

    {{-- Lista --}}
    <div class="space-y-4">
        @forelse($posts as $post)
            <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <h3 class="text-sm font-black text-gray-900 uppercase truncate">{{ $post->title }}</h3>
                    <p class="text-[10px] text-gray-400 font-mono mt-1">Publicada em {{ $post->created_at->format('d/m/Y') }} · {{ $post->is_active ? 'Ativa' : 'Inativa' }}</p>
                </div>
                <button wire:click="delete({{ $post->id }})" wire:confirm="Remover esta matéria? (sem reembolso)" class="text-[9px] font-black uppercase tracking-wider px-3 py-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition shrink-0">Excluir</button>
            </div>
        @empty
            <div class="py-16 text-center bg-white rounded-2xl border border-dashed border-gray-200">
                <p class="text-gray-400 font-bold uppercase tracking-widest text-xs">Você ainda não publicou matérias patrocinadas.</p>
            </div>
        @endforelse
    </div>
</div>
