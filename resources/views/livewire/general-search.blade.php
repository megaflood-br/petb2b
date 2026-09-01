<div class="bg-gray-50 min-h-screen py-16">
    <div class="max-w-4xl mx-auto px-6">

        {{-- Barra de Busca Interna --}}
        <div class="mb-12">
            <div class="relative max-w-2xl mx-auto">
                <input wire:model.live="search" type="text" placeholder="O que seu negócio precisa hoje?"
                    class="w-full bg-white rounded-full py-5 px-8 text-base font-bold shadow-sm border border-gray-100 focus:ring-4 focus:ring-brand-500/10 transition-all outline-none">
            </div>
            @if($term)
                <p class="text-center text-gray-400 text-xs font-bold uppercase tracking-widest mt-4">
                    Resultados para: <span class="text-brand-500 italic">"{{ $term }}"</span>
                </p>
            @endif
        </div>

        <div class="space-y-12">
            {{-- 1. SEÇÃO DE FORNECEDORES --}}
            @if($suppliers->count() > 0)
                <section>
                    <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6 border-b pb-2">Fornecedores Encontrados</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($suppliers as $supplier)
                            <div class="bg-white border border-gray-100 p-6 rounded-[2.5rem] shadow-sm flex flex-col justify-between transition hover:shadow-md">
                                <div>
                                    <h4 class="font-black text-gray-900 uppercase tracking-tight text-base line-clamp-1">{{ $supplier->name }}</h4>
                                    <p class="text-[9px] font-black text-brand-500 mb-2 uppercase tracking-wider">{{ str_replace('-', ' ', $supplier->category) }}</p>
                                    <p class="text-xs text-gray-500 font-medium line-clamp-2 mb-4">{{ $supplier->description }}</p>
                                </div>
                                <a href="{{ route('suppliers.show', $supplier->slug) }}" class="block w-full text-center py-3 bg-gray-50 hover:bg-brand-500 hover:text-white rounded-xl font-black uppercase text-[9px] tracking-widest transition">
                                    Ver Perfil
                                </a>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- 2. SEÇÃO DE EVENTOS --}}
            @if($events->count() > 0)
                <section>
                    <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6 border-b pb-2">Agenda de Eventos</h2>
                    <div class="space-y-4">
                        @foreach($events as $event)
                            <a href="{{ route('events.index', ['slug' => $event->slug]) }}" class="flex items-center gap-6 p-5 bg-white rounded-[2rem] border border-gray-100 hover:shadow-md transition group">
                                <div class="w-16 h-16 bg-brand-50 rounded-xl overflow-hidden shrink-0 flex flex-col items-center justify-center text-brand-600 font-black">
                                    <span class="text-[8px] uppercase leading-none mb-0.5">{{ $event->start_date->translatedFormat('M') }}</span>
                                    <span class="text-lg leading-none">{{ $event->start_date->format('d') }}</span>
                                </div>
                                <div>
                                    <h4 class="font-black text-gray-900 uppercase text-sm leading-tight mb-0.5 group-hover:text-brand-500 transition-colors">{{ $event->title }}</h4>
                                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">{{ $event->city }}/{{ $event->state }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- 3. SEÇÃO DE CLASSIFICADOS --}}
            @if($classifieds->count() > 0)
                <section>
                    <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6 border-b pb-2">Classificados & Oportunidades</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($classifieds as $ad)
                            <div class="bg-white border border-gray-100 p-6 rounded-[2.5rem] shadow-sm flex items-center justify-between gap-4 transition hover:shadow-md">
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-black text-gray-900 uppercase tracking-tight text-sm line-clamp-1">{{ $ad->title }}</h4>
                                    <p class="text-sm font-mono font-black text-gray-900 mt-1">R$ {{ number_format($ad->price, 2, ',', '.') }}</p>
                                </div>
                                <a href="{{ route('classifieds.show', $ad->slug) }}" class="bg-gray-950 text-white hover:bg-brand-500 px-4 py-2.5 rounded-xl font-black uppercase text-[9px] tracking-widest transition shrink-0">
                                    Ver Item
                                </a>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- 4. SEÇÃO DE MATÉRIAS (CORRIGIDA) --}}
            @if($posts->count() > 0)
                <section>
                    <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6 border-b pb-2">Matérias & Notícias</h2>
                    <div class="space-y-4">
                        @foreach($posts as $post)
                            @php
                                // Captura o slug da categoria vinculada ou define 'geral' como fallback seguro
                                $postCatSlug = $post->blogCategories->first()->slug ?? 'geral';
                            @endphp

                            {{-- CORREÇÃO CRÍTICA: Passando explicitamente o array associativo com prefixCategory e slug --}}
                            <a href="{{ route('blog.show', ['prefixCategory' => $postCatSlug, 'slug' => $post->slug]) }}" class="flex items-center gap-6 p-6 bg-white rounded-[2rem] border border-gray-100 hover:shadow-xl transition group block">
                                <div class="w-32 h-20 bg-gray-100 rounded-2xl overflow-hidden shrink-0 relative">
                                    @if($post->image)
                                        <img src="{{ asset('storage/' . $post->image) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-brand-50 text-brand-300 font-black italic text-xs">NP</div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <span class="text-[8px] font-black text-brand-500 uppercase tracking-widest block mb-1">
                                        {{ $post->blogCategories->first()->name ?? 'Geral' }}
                                    </span>
                                    <h4 class="font-black text-gray-900 uppercase text-sm leading-tight group-hover:text-brand-500 transition line-clamp-2 italic">{{ $post->title }}</h4>
                                    <p class="text-[9px] text-gray-400 font-bold mt-1 uppercase">{{ $post->created_at->format('d/m/Y') }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Sem resultados em nenhuma categoria --}}
            @if($suppliers->isEmpty() && $events->isEmpty() && $classifieds->isEmpty() && $posts->isEmpty())
                <div class="text-center py-20 bg-white rounded-[3rem] border-2 border-dashed border-gray-100">
                    <p class="text-gray-400 font-black uppercase text-xs tracking-widest italic">Nenhum resultado encontrado para o termo pesquisado.</p>
                </div>
            @endif
        </div>

    </div>
</div>
