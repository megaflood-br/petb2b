<div>
    <div class="bg-gray-50 min-h-screen py-12">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">

            {{-- Título da Seção Estilizado com a Cor do Portal --}}
            <div class="mb-12">
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight uppercase border-l-4 border-brand-500 pl-4 italic">
                    Guia de <span class="text-brand-500">Fornecedores</span>
                </h1>
            </div>

            {{-- Filtros Dinâmicos Arredondados --}}
            <div class="mb-10 bg-white p-6 rounded-[2.5rem] shadow-sm border border-gray-100 space-y-4">
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <input wire:model.live="search" type="text" placeholder="Buscar empresa ou product..."
                            class="w-full bg-gray-50 border-none rounded-xl py-3.5 px-6 text-sm font-bold shadow-inner focus:ring-4 focus:ring-brand-500/10 transition-all outline-none">
                    </div>
                    <div class="md:w-64">
                        <select wire:model.live="category" class="w-full bg-gray-50 border-none rounded-xl py-3.5 px-6 text-sm font-bold focus:ring-4 focus:ring-brand-500/10 transition-all outline-none">
                            <option value="">Todos os Segmentos</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->slug }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row gap-4 border-t border-gray-50 pt-4">
                    <div class="md:w-1/2">
                        <select wire:model.live="state" class="w-full bg-gray-50 border-none rounded-xl py-3.5 px-6 text-sm font-bold focus:ring-4 focus:ring-brand-500/10 transition-all outline-none">
                            <option value="">Todos os Estados (UF)</option>
                            @foreach($states as $uf) <option value="{{ $uf }}">{{ $uf }}</option> @endforeach
                        </select>
                    </div>
                    <div class="md:w-1/2">
                        <select wire:model.live="city" class="w-full bg-gray-50 border-none rounded-xl py-3.5 px-6 text-sm font-bold focus:ring-4 focus:ring-brand-500/10 transition-all outline-none" {{ empty($cities) ? 'disabled' : '' }}>
                            <option value="">Todas as Cidades</option>
                            @foreach($cities as $cidade) <option value="{{ $cidade }}">{{ $cidade }}</option> @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Layout de Duas Colunas --}}
            <div class="flex flex-col lg:flex-row gap-8 items-start">

                {{-- Coluna da Esquerda: Listagem de Fornecedores --}}
                <div class="w-full lg:flex-1">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @forelse($suppliers as $supplier)
                            {{-- Se for verificado, ganha uma borda sutil azul/celeste que destaca o card --}}
                            <div class="bg-white border p-8 rounded-[3rem] shadow-sm flex flex-col justify-between transition hover:shadow-xl group relative overflow-hidden {{ $supplier->is_approved ? 'border-sky-100 ring-1 ring-sky-50/50' : 'border-gray-100' }}">
                                <div>
                                    <div class="flex justify-between items-start mb-6">
                                        <div class="w-20 h-20 bg-gray-50 rounded-2xl flex items-center justify-center border border-gray-50 overflow-hidden shrink-0">
                                            @if($supplier->logo)
                                                <img src="{{ asset('storage/' . $supplier->logo) }}" alt="{{ $supplier->name }}" class="w-full h-full object-contain">
                                            @else
                                                <span class="text-brand-200 font-black italic text-xl">PBP</span>
                                            @endif
                                        </div>

                                        {{-- CORRIGIDO: Selo de Verificado Otimizado, Centralizado e Legível (Padrão Oficial) --}}
                                        @if($supplier->is_approved)
                                            <div class="w-9 h-9 bg-[#0095f6] text-white rounded-full shadow-md flex items-center justify-center shrink-0 border-4 border-sky-50" title="Fornecedor Verificado Oficial">
                                                <svg class="w-4 h-4 stroke-[3.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>
                                        @endif
                                    </div>

                                    <h4 class="font-black text-gray-900 uppercase tracking-tight text-lg line-clamp-1 group-hover:text-brand-500 transition-colors">
                                        {{ $supplier->name }}
                                    </h4>
                                    <p class="text-[10px] font-black text-brand-500 mb-4 uppercase tracking-widest">
                                        {{ str_replace('-', ' ', $supplier->category) }}
                                    </p>
                                    <p class="text-sm text-gray-500 font-medium line-clamp-2 mb-6 leading-relaxed">
                                        {{ $supplier->description ?? 'Nenhuma descrição complementar cadastrada para este fornecedor.' }}
                                    </p>
                                </div>

                                <a href="{{ route('suppliers.show', $supplier->slug) }}" class="block w-full text-center py-4 bg-gray-950 hover:bg-brand-500 text-white rounded-2xl font-black uppercase text-[10px] tracking-widest transition shadow-md">
                                    Ver Detalhes
                                </a>
                            </div>
                        @empty
                            <div class="col-span-1 md:col-span-2 py-20 text-center border-2 border-dashed border-gray-100 rounded-[3rem]">
                                <p class="text-gray-400 font-black uppercase tracking-widest text-xs">Nenhum fornecedor encontrado nesta busca.</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-12">
                        {{ $suppliers->links() }}
                    </div>
                </div>

                {{-- Sidebar de Anúncios Dinâmicos Premium --}}
                <aside class="w-full lg:w-80 shrink-0 lg:sticky lg:top-6">
                    <x-ad-space position="sidebar_guia" variant="sidebar" />
                </aside>

            </div>

        </div>
    </div>
</div>
