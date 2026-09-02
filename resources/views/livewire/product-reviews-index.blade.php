{{-- TEXTO BLINDADO: Apenas uma DIV mãe global envolvendo todo o componente Livewire --}}
<div class="bg-white py-16">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">

        {{-- Título da Seção alinhado ao padrão editorial --}}
        <div class="max-w-2xl mb-12">
            <h1 class="text-4xl font-black text-gray-900 uppercase tracking-tight italic">
                Reviews <span class="text-brand-500">Técnicos</span>
            </h1>
            <p class="mt-4 text-base text-gray-500 font-medium">Avaliações imparciais de equipamentos, máquinas e insumos para o seu negócio pet.</p>
        </div>

        {{-- Grid de Artigos / Análises --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
            @forelse($reviews as $review)
                <div class="bg-white rounded-[2.5rem] border border-gray-100 overflow-hidden flex flex-col shadow-sm hover:shadow-xl transition group">

                    {{-- Imagem do Produto --}}
                    <div class="relative w-full aspect-[16/10] bg-gray-50 flex items-center justify-center p-8 border-b border-gray-50/50">
                        @if($review->image)
                            <img src="{{ asset('storage/'.$review->image) }}" class="max-h-full object-contain group-hover:scale-105 transition duration-500" alt="{{ $review->title }}">
                        @else
                            <div class="text-gray-300 font-black italic text-xs tracking-widest">SEM FOTO</div>
                        @endif

                        {{-- Badge de Nota Técnica --}}
                        @if($review->rating !== null)
                            <div class="absolute top-4 right-4 z-10">
                                <span class="bg-white/95 backdrop-blur-sm px-3 py-1.5 rounded-xl font-black text-brand-500 text-[10px] shadow-sm border border-brand-50 flex items-center gap-1">
                                    ★ {{ number_format($review->rating, 1) }}
                                </span>
                            </div>
                        @endif
                    </div>

                    {{-- Bloco de Conteúdos e Ações --}}
                    <div class="p-6 flex-1 flex flex-col justify-between">
                        <div>
                            <span class="text-[9px] font-black text-brand-500 uppercase tracking-[0.3em] mb-2 block">
                                {{ $review->category }}
                            </span>

                            <h3 class="text-xl font-black text-gray-900 uppercase leading-tight mb-3 group-hover:text-brand-500 transition line-clamp-2 italic">
                                <a href="{{ route('reviews.show', $review->slug) }}">{{ $review->title }}</a>
                            </h3>

                            <p class="text-gray-500 text-sm line-clamp-3 mb-6 font-medium italic">
                                @if($review->verdict)
                                    "{{ $review->verdict }}"
                                @else
                                    {{ \Illuminate\Support\Str::limit(strip_tags($review->content), 140) }}
                                @endif
                            </p>
                        </div>

                        <div class="mt-auto pt-2">
                            <a href="{{ route('reviews.show', $review->slug) }}" class="block w-full text-center py-4 bg-gray-900 text-white rounded-2xl font-black uppercase text-[10px] tracking-widest hover:bg-brand-500 transition shadow-md shadow-gray-200">
                                Ler Review Completo →
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-20 text-center border-2 border-dashed border-gray-100 rounded-[2rem]">
                    <p class="text-gray-400 font-bold uppercase tracking-widest text-xs">Nenhum review técnico publicado até o momento.</p>
                </div>
            @endforelse
        </div>

        {{-- Zona de Paginação Dinâmica Inteligente --}}
        @if($reviews && method_exists($reviews, 'hasPages') && $reviews->hasPages())
            <div class="mt-16 border-t border-gray-100 pt-10">
                {{ $reviews->links() }}
            </div>
        @endif

    </div>
</div>
