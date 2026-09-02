<div class="space-y-8">
    {{-- Cabeçalho & Filtro de Busca Dinâmico --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-3xl font-black text-gray-900 uppercase italic">Gestão do <span class="text-brand-500">Blog</span></h1>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 w-full md:w-auto flex-1 md:justify-end">
            {{-- ADICIONADO: Campo de busca reativa nas matérias (visível apenas quando a listagem estiver aberta) --}}
            @if(!$showForm)
                <div class="relative w-full sm:w-72">
                    <input type="text" wire:model.live="search" placeholder="Buscar por título ou conteúdo..."
                        class="w-full bg-white border border-gray-100 rounded-xl py-3 px-4 pl-10 text-xs font-bold text-gray-900 shadow-sm focus:ring-2 focus:ring-brand-500 transition-all outline-none">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
            @endif

            <button wire:click="{{ $showForm ? 'resetForm' : '$toggle(\'showForm\')' }}" class="bg-brand-500 text-white px-6 py-3 rounded-2xl font-black uppercase text-xs tracking-widest hover:bg-brand-600 transition shadow-lg shadow-brand-100 shrink-0 text-center">
                {{ $showForm ? 'Voltar para Lista' : 'Nova Postagem' }}
            </button>
        </div>
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
                    {{-- Título da Notícia --}}
                    <div class="md:col-span-2">
                        <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block tracking-widest">Título da Notícia</label>
                        <input type="text" wire:model="title" class="w-full bg-gray-50 border-none rounded-xl p-4 font-bold text-gray-900 focus:ring-2 focus:ring-brand-500">
                        @error('title') <span class="text-red-500 text-[10px] font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Grid Multiselecção de Categorias com Checkboxes em Box de Rolagem --}}
                    <div class="md:col-span-1">
                        <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block tracking-widest">Categorias Vinculadas (Selecione 1 ou mais)</label>
                        <div class="bg-gray-50 rounded-xl p-4 max-h-[160px] overflow-y-auto space-y-2 border border-gray-100 shadow-inner">
                            @foreach($categories as $category)
                                <label class="flex items-center gap-3 cursor-pointer p-1 rounded-lg hover:bg-white transition-colors">
                                    <input type="checkbox" wire:model="selected_categories" value="{{ $category->id }}" class="w-4 h-4 text-brand-500 rounded border-gray-300 focus:ring-brand-500">
                                    <span class="text-xs font-bold text-gray-700 uppercase tracking-tight">{{ $category->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('selected_categories') <span class="text-red-500 text-[10px] font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Editor Trix --}}
                <div class="space-y-2" wire:ignore>
                    <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block tracking-widest">Conteúdo HTML</label>
                    <input id="content_input" type="hidden" name="content" value="{{ $content }}">
                    <trix-editor input="content_input" class="trix-content bg-white border border-gray-100 rounded-2xl font-medium text-gray-900 min-h-[400px] p-6 outline-none focus:border-brand-500 transition-all"></trix-editor>
                </div>
                @error('content') <span class="text-red-500 text-[10px] font-bold block">{{ $message }}</span> @enderror

                {{-- Configurações de SEO (Google) --}}
                <div class="bg-brand-50/30 p-8 rounded-[2rem] border border-brand-100/50 space-y-4">
                    <h3 class="text-[10px] font-black uppercase text-brand-600 tracking-widest flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        Configurações de SEO (Google)
                    </h3>

                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">Meta Description (Máx. 160 caracteres)</label>
                            <textarea wire:model="meta_description" rows="2" class="w-full bg-white border-none rounded-xl p-4 font-bold text-gray-900 focus:ring-2 focus:ring-brand-500 shadow-sm"></textarea>
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">Keywords (Separe por vírgula)</label>
                            <input type="text" wire:model="meta_keywords" placeholder="ex: pet shop atibaia, banho e tosa, saude animal" class="w-full bg-white border-none rounded-xl p-4 font-bold text-gray-900 focus:ring-2 focus:ring-brand-500 shadow-sm">
                        </div>
                    </div>
                </div>

                {{-- Grid: Imagem de Capa e Data de Publicação --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Upload da Imagem --}}
                    <div>
                        <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block tracking-widest">Imagem de Capa</label>
                        <input type="file" wire:model="image" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-brand-50 file:text-brand-700">
                        @error('image') <span class="text-red-500 text-[10px] font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Input de Data e Hora de Publicação --}}
                    <div>
                        <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block tracking-widest">Data de Publicação</label>
                        <input type="datetime-local" wire:model="created_at" class="w-full bg-gray-50 border-none rounded-xl p-3 font-bold text-gray-900 focus:ring-2 focus:ring-brand-500">
                        @error('created_at') <span class="text-red-500 text-[10px] font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Análise de produto: nota, prós, contras e veredito --}}
                <div class="bg-amber-50/40 p-8 rounded-[2rem] border border-amber-100/80 space-y-4">
                    <div>
                        <h3 class="text-[10px] font-black uppercase text-amber-700 tracking-widest">Análise de produto</h3>
                        <p class="text-[10px] text-gray-500 font-medium mt-1 normal-case">Preencha quando a matéria for uma análise (categoria de análises de produtos). Matérias comuns podem deixar em branco.</p>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block tracking-widest">Nota (0 a 5)</label>
                        <input type="number" step="0.1" min="0" max="5" wire:model="rating" class="w-full md:w-40 bg-white border-none rounded-xl p-3.5 font-mono font-bold text-gray-900 focus:ring-2 focus:ring-brand-500 shadow-sm">
                        @error('rating') <span class="text-red-500 text-[10px] font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] font-black uppercase text-green-700 mb-2 block tracking-widest">Prós</label>
                            <textarea wire:model="pros" rows="4" placeholder="Um ponto por linha" class="w-full bg-white border-none rounded-xl p-4 text-sm font-medium text-gray-900 focus:ring-2 focus:ring-green-500 shadow-sm"></textarea>
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase text-red-600 mb-2 block tracking-widest">Contras</label>
                            <textarea wire:model="cons" rows="4" placeholder="Um ponto por linha" class="w-full bg-white border-none rounded-xl p-4 text-sm font-medium text-gray-900 focus:ring-2 focus:ring-red-500 shadow-sm"></textarea>
                        </div>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block tracking-widest">Veredito</label>
                        <textarea wire:model="verdict" rows="2" placeholder="Resumo final da análise" class="w-full bg-white border-none rounded-xl p-4 text-sm font-bold italic text-gray-900 focus:ring-2 focus:ring-brand-500 shadow-sm"></textarea>
                    </div>
                </div>

                <div class="flex items-center gap-3 bg-brand-50/50 p-4 rounded-2xl border border-brand-100 mb-6">
                    <input type="checkbox" wire:model="is_featured" id="is_featured" class="w-5 h-5 text-brand-600 rounded-lg border-gray-300 focus:ring-brand-500">
                    <label for="is_featured" class="text-[10px] font-black uppercase text-brand-900 tracking-widest cursor-pointer">
                        Destacar esta notícia no topo do blog
                    </label>
                </div>

                <div class="flex items-center gap-3 bg-amber-50/60 p-4 rounded-2xl border border-amber-100 mb-6">
                    <input type="checkbox" wire:model="is_premium" id="is_premium" class="w-5 h-5 text-amber-600 rounded-lg border-gray-300 focus:ring-amber-500">
                    <label for="is_premium" class="text-[10px] font-black uppercase text-amber-900 tracking-widest cursor-pointer">
                        Conteúdo exclusivo (somente usuários cadastrados)
                    </label>
                </div>

                <button type="submit" class="w-full bg-gray-900 text-white p-5 rounded-2xl font-black uppercase tracking-widest hover:bg-brand-600 transition shadow-lg">
                    Salvar Postagem
                </button>
            </form>
        </div>
    @else
        {{-- Listagem (Tabela Master) --}}
        <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden text-xs">
            <table class="w-full text-left">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="py-4 px-6 font-black text-gray-400 uppercase tracking-widest">Título</th>
                        <th class="py-4 px-6 font-black text-gray-400 uppercase tracking-widest">Categorias Vinculadas</th>
                        <th class="py-4 px-6 font-black text-gray-400 uppercase tracking-widest">Data de Pub.</th>
                        <th class="py-4 px-6 font-black text-gray-400 uppercase tracking-widest text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($posts as $post)
                        <tr class="hover:bg-gray-50/50 transition font-bold">
                            <td class="py-6 px-6 text-gray-900 uppercase max-w-[300px] truncate">{{ $post->title }}</td>
                            <td class="py-6 px-6">
                                <div class="flex flex-wrap gap-1.5">
                                    @forelse($post->blogCategories as $cat)
                                        <span class="bg-brand-50 text-brand-600 px-2.5 py-1 rounded-full text-[9px] uppercase tracking-tight font-black">
                                            {{ $cat->name }}
                                        </span>
                                    @empty
                                        <span class="bg-gray-100 text-gray-400 px-2.5 py-1 rounded-full text-[9px] uppercase tracking-tight">
                                            Sem Categoria
                                        </span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="py-6 px-6 text-gray-500 font-mono">
                                {{ $post->created_at ? $post->created_at->format('d/m/Y H:i') : '---' }}
                            </td>
                            <td class="py-6 px-6 text-right space-x-3 text-sm">
                                <button wire:click="edit({{ $post->id }})" class="text-brand-600 uppercase font-black">Editar</button>
                                <button onclick="confirm('Tem certeza?') || event.stopImmediatePropagation()" wire:click="delete({{ $post->id }})" class="text-red-400 uppercase font-black">Excluir</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-12 text-center text-gray-400 font-black uppercase tracking-widest">
                                Nenhuma postagem encontrada para esta busca.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $posts->links() }}</div>
    @endif

    {{-- SCRIPTS E CSS FORA DO @IF PARA GARANTIR CARREGAMENTO --}}
    <link rel="stylesheet" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
    <script src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>

    <script>
        document.addEventListener("trix-change", function(e) {
            @this.set('content', e.target.innerHTML);
        });

        window.addEventListener('set-post-content', event => {
            const editor = document.querySelector("trix-editor");
            if (editor && editor.editor) {
                editor.editor.loadHTML(event.detail.content);
            }
        });
    </script>

    <style>
        trix-toolbar .trix-button-row { border-bottom: 1px solid #f3f4f6; padding-bottom: 5px; margin-bottom: 10px; }
        trix-editor { border: 1px solid #f3f4f6 !important; }
    </style>
</div>
