<div class="bg-gray-50 min-h-screen py-16">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">

        {{-- Cabeçalho da Página --}}
        <div class="max-w-2xl mb-12">
            <p class="text-[10px] font-black uppercase text-brand-500 tracking-[0.25em] mb-1">Criação Selecionada</p>
            <h1 class="text-4xl font-black text-gray-900 uppercase italic tracking-tight">
                Guia de <span class="text-brand-500">Canis & Criadores</span>
            </h1>
            <p class="mt-3 text-base text-gray-500 font-medium">Encontre criadores profissionais comprometidos com a saúde, pureza racial e bem-estar animal.</p>
        </div>

        {{-- Barra de Filtros Rápidos --}}
        <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm mb-12 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block tracking-widest">Filtrar por Nome</label>
                <input type="text" wire:model.live="searchName" placeholder="Digite o nome do canil..." class="w-full bg-gray-50 border-none rounded-xl p-4 font-bold text-gray-900 focus:ring-2 focus:ring-brand-500 text-xs">
            </div>
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block tracking-widest">Filtrar por Raça</label>
                <select wire:model.live="searchBreed" class="w-full bg-gray-50 border-none rounded-xl p-4 font-bold text-gray-900 focus:ring-2 focus:ring-brand-500 text-xs">
                    <option value="">Todas as Raças</option>
                    @foreach($availableBreeds as $breed)
                        <option value="{{ $breed }}">{{ $breed }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Grid de Canis --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($kennels as $kennel)
                <div class="bg-white rounded-[2.5rem] border border-gray-100 overflow-hidden shadow-sm hover:shadow-xl transition group relative flex flex-col justify-between">

                    {{-- Imagem de Capa do Canil --}}
                    <div class="w-full aspect-[16/7] bg-gray-100 relative overflow-hidden">
                        @if($kennel->cover_image)
                            <img src="{{ asset('storage/' . $kennel->cover_image) }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-brand-500/10 flex items-center justify-center"></div>
                        @endif

                        {{-- Selo VIP Criador Verificado --}}
                        @if($kennel->is_verified)
                            <div class="absolute top-4 left-4 z-10">
                                <span class="bg-amber-400 text-black font-black text-[8px] uppercase tracking-widest px-3 py-1 rounded-full shadow-sm">
                                    ★ Verificado
                                </span>
                            </div>
                        @endif
                    </div>

                    {{-- Conteúdo do Card --}}
                    <div class="p-8 flex-1 flex flex-col justify-between">
                        <div>
                            {{-- Logo Redonda Flutuante --}}
                            <div class="w-16 h-16 rounded-full bg-white border-2 border-white shadow-md overflow-hidden -mt-16 relative z-20 mb-4 flex items-center justify-center font-black italic text-brand-500">
                                @if($kennel->logo)
                                    <img src="{{ asset('storage/' . $kennel->logo) }}" class="w-full h-full object-cover">
                                @else
                                    {{ substr($kennel->name, 0, 2) }}
                                @endif
                            </div>

                            <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">{{ $kennel->city }} / {{ $kennel->state }}</span>
                            <h3 class="text-xl font-black text-gray-900 uppercase tracking-tight mt-1 mb-2 group-hover:text-brand-500 transition-colors">
                                {{ $kennel->name }}
                            </h3>

                            @if($kennel->affix)
                                <p class="text-[10px] font-bold text-brand-600 uppercase tracking-tighter mb-4">Afixo: {{ $kennel->affix }}</p>
                            @endif

                            <p class="text-gray-500 text-xs font-medium line-clamp-3 mb-6 leading-relaxed">
                                {{ $kennel->description ?? 'Nenhuma descrição detalhada informada.' }}
                            </p>
                        </div>

                        {{-- Tags de Raças que Cria --}}
                        <div class="border-t border-gray-50 pt-4 mt-4">
                            <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-2">Especialidades:</p>
                            <div class="flex flex-wrap gap-1.5">
                                {{-- Buscamos direto as raças vinculadas a esse canil --}}
                                @forelse(\App\Models\RelatedBreed::where('kennel_id', $kennel->id)->get() as $b)
                                    <span class="bg-brand-50 text-brand-700 px-2.5 py-1 rounded-lg text-[9px] font-bold uppercase">
                                        {{ $b->breed_name }}
                                    </span>
                                @empty
                                    <span class="text-gray-400 italic text-[10px]">Não informadas</span>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- Botão de Acesso ao Perfil --}}
                    <div class="px-8 pb-8">
                        <a href="{{ route('kennels.show', $kennel->slug) }}" class="w-full block text-center bg-gray-900 text-white py-4 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-brand-500 transition shadow-sm">
                            Ver Perfil & Ninhadas
                        </a>
                    </div>

                </div>
            @empty
                <div class="col-span-full py-20 text-center bg-white rounded-[3rem] border-2 border-dashed border-gray-100">
                    <p class="text-gray-400 font-bold uppercase tracking-widest text-xs">Nenhum canil encontrado com os filtros selecionados.</p>
                </div>
            @endforelse
        </div>

        {{-- Links de Paginação --}}
        <div class="mt-12">
            {{ $kennels->links() }}
        </div>

    </div>
</div>
