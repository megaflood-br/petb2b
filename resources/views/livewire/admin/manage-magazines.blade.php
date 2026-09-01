<div class="p-6">
    {{-- Cabeçalho com Botão de Criar --}}
    <div class="flex justify-between items-center mb-10">
        <div>
            <h1 class="text-2xl font-black text-gray-900 uppercase italic tracking-tighter">
                Revistas <span class="text-indigo-600">Mensais</span>
            </h1>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Gestão de publicações digitais</p>
        </div>

        {{-- Botão para mostrar/esconder o formulário --}}
        <button wire:click="$set('showForm', {{ !$showForm ? 'true' : 'false' }})"
                class="bg-indigo-600 text-white px-8 py-4 rounded-2xl font-black uppercase text-[10px] tracking-widest hover:bg-indigo-700 transition shadow-lg shadow-indigo-200">
            {{ $showForm ? 'Voltar para Lista' : 'Nova Edição' }}
        </button>
    </div>

    {{-- Formulário de Cadastro/Edição --}}
    @if($showForm)
    <div class="bg-white p-10 rounded-[3rem] shadow-xl border border-gray-100 mb-12 animate-in fade-in slide-in-from-top-4 duration-500">

        {{-- Mensagens de Erro de Validação --}}
        @if ($errors->any())
            <div class="bg-red-50 p-4 rounded-2xl border border-red-100 mb-6">
                <ul class="list-disc list-inside text-red-600 text-[10px] font-black uppercase tracking-widest">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Alerta de Sucesso --}}
        @if (session()->has('message'))
            <div class="bg-green-50 p-4 rounded-2xl border border-green-100 text-green-600 text-[10px] font-black uppercase tracking-widest mb-6">
                {{ session('message') }}
            </div>
        @endif

        <form wire:submit.prevent="save" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest mb-2 block">Título da Revista</label>
                    <input type="text" wire:model="title" placeholder="Ex: Mercado Pet em Foco"
                           class="w-full bg-gray-50 border-none rounded-2xl p-5 font-bold focus:ring-4 focus:ring-indigo-500/10 transition">
                </div>
                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest mb-2 block">Mês/Ano (Para a URL)</label>
                    <input type="text" wire:model="issue_period" placeholder="Ex: Janeiro/2026"
                           class="w-full bg-gray-50 border-none rounded-2xl p-5 font-bold focus:ring-4 focus:ring-indigo-500/10 transition">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest mb-2 block">Arquivo PDF (Máx 50MB)</label>
                    <input type="file" wire:model="pdf" class="w-full text-xs font-bold text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-[10px] file:font-black file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100 transition">
                </div>
                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest mb-2 block">Imagem da Capa (JPG/PNG)</label>
                    <input type="file" wire:model="cover" class="w-full text-xs font-bold text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-[10px] file:font-black file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100 transition">
                </div>
            </div>

            <button type="submit" wire:loading.attr="disabled"
                    class="w-full bg-gray-900 text-white p-6 rounded-2xl font-black uppercase tracking-widest hover:bg-indigo-600 transition shadow-xl disabled:opacity-50 mt-4">
                <span wire:loading.remove wire:target="save">Publicar Revista</span>
                <span wire:loading wire:target="save">Gravando no Banco de Dados...</span>
                <span wire:loading wire:target="pdf, cover">Enviando Arquivos...</span>
            </button>
        </form>
    </div>
@endif

    {{-- Listagem das Revistas --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
        @foreach($magazines as $mag)
            <div class="group bg-white p-4 rounded-[2.5rem] border border-gray-50 shadow-sm hover:shadow-xl transition relative">
                <div class="aspect-[3/4] rounded-[2rem] overflow-hidden mb-4 relative">
                    <img src="{{ asset('storage/' . $mag->cover_path) }}" class="w-full h-full object-cover">

                    {{-- Overlay de Ações --}}
                    <div class="absolute inset-0 bg-indigo-900/80 opacity-0 group-hover:opacity-100 transition-all flex flex-col items-center justify-center gap-3">
                        <button wire:click="edit({{ $mag->id }})" class="bg-white text-indigo-600 px-6 py-2 rounded-xl font-black uppercase text-[10px] tracking-widest">Editar</button>
                        <button onclick="confirm('Excluir esta edição?') || event.stopImmediatePropagation()"
                                wire:click="delete({{ $mag->id }})" class="bg-red-500 text-white px-6 py-2 rounded-xl font-black uppercase text-[10px] tracking-widest">Excluir</button>
                    </div>
                </div>
                <div class="text-center">
                    <h3 class="font-black text-gray-900 uppercase text-[11px]">{{ $mag->title }}</h3>
                    <p class="text-gray-400 font-bold uppercase text-[9px] tracking-widest">{{ $mag->issue_period }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-12">
        {{ $magazines->links() }}
    </div>
</div>
