<x-app-layout>
    <div class="bg-white min-h-screen py-20 px-6">
        <div class="max-w-4xl mx-auto">

            {{-- Categoria e Título --}}
            <span class="text-[10px] font-black text-brand-500 uppercase tracking-[0.3em] mb-4 block">
                {{ $review->category }}
            </span>
            <h1 class="text-4xl md:text-6xl font-black text-gray-900 uppercase italic mb-8 leading-tight">
                {{ $review->title }}
            </h1>

            {{-- Container da Imagem do Produto (Otimizado para não cortar criativos) --}}
            <div class="aspect-video w-full rounded-[3rem] overflow-hidden mb-12 shadow-2xl border border-gray-100 bg-gray-50/50 flex items-center justify-center p-8">
                @if($review->image)
                    <img src="{{ asset('storage/'.$review->image) }}" class="max-h-full object-contain transition-transform duration-500 hover:scale-102" alt="{{ $review->title }}">
                @else
                    <div class="text-gray-300 font-black italic text-sm tracking-widest">SEM FOTO DO EQUIPAMENTO</div>
                @endif
            </div>

            {{-- Grid de Prós e Contras Técnicos --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10 mb-12">
                {{-- Pontos Positivos --}}
                <div class="bg-green-50/60 p-8 rounded-[2rem] border border-green-100 flex flex-col justify-between">
                    <div>
                        <h3 class="font-black text-green-700 uppercase text-xs mb-4 tracking-widest flex items-center gap-1.5">
                            🟩 Pontos Positivos
                        </h3>
                        <div class="text-sm font-semibold text-green-800 whitespace-pre-line leading-relaxed normal-case">
                            {{ $review->pros }}
                        </div>
                    </div>
                </div>

                {{-- Pontos Negativos --}}
                <div class="bg-red-50/60 p-8 rounded-[2rem] border border-red-100 flex flex-col justify-between">
                    <div>
                        <h3 class="font-black text-red-700 uppercase text-xs mb-4 tracking-widest flex items-center gap-1.5">
                            🟥 Pontos Negativos
                        </h3>
                        <div class="text-sm font-semibold text-red-800 whitespace-pre-line leading-relaxed normal-case">
                            {{ $review->cons }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bloco de Conteúdo / Artigo Completo --}}
            <div class="prose prose-brand max-w-none mb-12">
                <h3 class="text-2xl font-black uppercase italic mb-6 text-gray-900 tracking-tight">Análise Detalhada</h3>
                <p class="text-gray-600 leading-loose text-lg font-medium normal-case">
                    {{ $review->content }}
                </p>
            </div>

            {{-- Bloco de Veredito Final Premium --}}
            <div class="bg-gray-950 p-10 rounded-[3rem] text-white border border-gray-900 shadow-xl relative overflow-hidden">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_bottom_right,rgba(244,63,94,0.04),transparent_60%)]"></div>

                <div class="relative z-10 flex justify-between items-center border-b border-gray-900 pb-5 mb-6">
                    <h3 class="text-xl font-black uppercase italic tracking-tight text-white">Veredito Final</h3>
                    <span class="text-3xl font-black text-amber-400 font-mono flex items-center gap-1">
                        <span class="text-xl">★</span> {{ number_format($review->rating, 1) }}
                    </span>
                </div>

                <p class="relative z-10 text-gray-300 italic text-lg leading-relaxed font-medium">
                    "{{ $review->verdict }}"
                </p>
            </div>

        </div>
    </div>
</x-app-layout>
