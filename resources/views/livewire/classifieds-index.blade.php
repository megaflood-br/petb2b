{{-- ELEMENTO RAIZ ÚNICO PARA PREVENIR ERROS DO LIVEWIRE --}}
<div class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Barra Superior de Filtros Dinâmicos --}}
        <div class="flex flex-col md:flex-row gap-4 w-full md:w-auto flex-wrap justify-end">
            <input type="text" wire:model.live="search" placeholder="O que você procura?"
                   class="border-gray-100 bg-white rounded-2xl py-3 px-6 font-bold text-sm shadow-sm focus:ring-brand-500 focus:border-brand-500 w-full md:w-64">

            <select wire:model.live="state" class="border-gray-100 bg-white rounded-2xl py-3 px-6 font-bold text-sm shadow-sm focus:ring-brand-500 focus:border-brand-500">
                <option value="">Todos os Estados</option>
                @foreach($availableLocations->pluck('state')->unique() as $st)
                    <option value="{{ $st }}">{{ $st }}</option>
                @endforeach
            </select>

            <select wire:model.live="city" class="border-gray-100 bg-white rounded-2xl py-3 px-6 font-bold text-sm shadow-sm focus:ring-brand-500 focus:border-brand-500">
                <option value="">Todas as Cidades</option>
                @foreach($availableLocations->when($state, fn($c) => $c->where('state', $state))->pluck('city')->unique() as $c)
                    <option value="{{ $c }}">{{ $c }}</option>
                @endforeach
            </select>

            <select wire:model.live="condition" class="border-gray-100 bg-white rounded-2xl py-3 px-6 font-bold text-sm shadow-sm focus:ring-brand-500 focus:border-brand-500">
                <option value="">Condição</option>
                <option value="Novo">Novo</option>
                <option value="Usado">Usado</option>
            </select>
        </div>

        {{-- Grid de Exibição dos Anúncios classificados --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 mt-5">
            @forelse($ads as $ad)
                <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm hover:shadow-xl transition-all group overflow-hidden flex flex-col h-full">

                    {{-- Foto do Classificado (Otimizada em Aspect Square) --}}
                    <div class="aspect-square bg-white overflow-hidden relative flex items-center justify-center p-4 border-b border-gray-50/50">
                        @if($ad->image)
                            <img src="{{ asset('storage/' . $ad->image) }}"
                                 alt="{{ $ad->title }}"
                                 class="max-w-full max-h-full object-contain transition duration-500 group-hover:scale-105">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-200 font-black italic text-xs tracking-widest">SEM FOTO</div>
                        @endif

                        <div class="absolute top-4 left-4">
                            <span class="bg-white/90 backdrop-blur px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest {{ $ad->condition == 'Novo' ? 'text-green-600' : 'text-orange-600' }} shadow-sm border border-gray-50/60">
                                {{ $ad->condition }}
                            </span>
                        </div>
                    </div>

                    {{-- Bloco de Textos e Preços --}}
                    <div class="p-6 flex flex-col flex-1 justify-between">
                        <div class="mb-4">
                            <h3 class="font-black text-gray-900 uppercase tracking-tight line-clamp-2 leading-tight h-10 group-hover:text-brand-500 transition-colors">
                                <a href="{{ route('classifieds.show', $ad->slug) }}">{{ $ad->title }}</a>
                            </h3>
                            <p class="text-[10px] font-black text-brand-500 uppercase mt-3 tracking-wider flex items-center gap-1">
                                📍 {{ $ad->supplier->name ?? 'Anunciante' }} - {{ $ad->supplier->city ?? '' }}/{{ $ad->supplier->state ?? '' }}
                            </p>
                        </div>

                        <div class="mt-auto pt-2">
                            <p class="text-2xl font-black text-gray-900 mb-4 font-mono">R$ {{ number_format($ad->price, 2, ',', '.') }}</p>
                            <a href="{{ route('classifieds.show', $ad->slug) }}" class="block w-full text-center py-4 bg-gray-900 text-white rounded-2xl font-black uppercase text-[10px] tracking-widest hover:bg-brand-500 transition shadow-md">
                                Ver Detalhes
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-20 text-center text-gray-400 border-2 border-dashed border-gray-200 rounded-[2.5rem] font-black uppercase tracking-widest text-xs p-8 bg-white">
                    Nenhum anúncio encontrado para esta busca.
                </div>
            @endforelse
        </div>

        {{-- Paginação Dinâmica Inteligente --}}
        @if($ads && method_exists($ads, 'hasPages') && $ads->hasPages())
            <div class="mt-12 border-t border-gray-100 pt-8">
                {{ $ads->links() }}
            </div>
        @endif
    </div>
</div> 
