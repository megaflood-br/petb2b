<x-app-layout>
    <div class="bg-gray-50 min-h-screen py-20">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">

            {{-- Cabeçalho da Estante --}}
            <div class="text-center mb-20">
                <h1 class="text-4xl md:text-6xl font-black text-gray-900 uppercase italic tracking-tighter mb-4">
                    Banca <span class="text-brand-500">Digital</span>
                </h1>
                <p class="text-gray-500 font-bold uppercase text-[10px] tracking-[0.3em]">
                    Arquivo completo de todas as nossas edições mensais
                </p>
            </div>

            {{-- Grid da Estante --}}
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-x-8 gap-y-16">
                @foreach($magazines as $mag)
                    <div class="flex flex-col group">

                        {{-- O "Livro" na prateleira --}}
                        <div class="relative aspect-[3/4] mb-6 perspective-1000">
                            {{-- Efeito 3D Corrigido na Borda e Rotação --}}
                            <div class="w-full h-full rounded-r-xl shadow-[10px_10px_25px_rgba(0,0,0,0.2)] overflow-hidden transition-transform duration-500 group-hover:rotate-y-[-25deg] origin-left border-l-8 border-gray-900/10 relative">
                                <img src="{{ asset('storage/' . $mag->cover_path) }}"
                                     class="w-full h-full object-cover"
                                     alt="{{ $mag->title }}">

                                {{-- Brilho da Capa --}}
                                <div class="absolute inset-0 bg-gradient-to-tr from-white/10 to-transparent opacity-50"></div>
                            </div>

                            {{-- Base da Prateleira (Efeito Sombra Realista) --}}
                            <div class="absolute -bottom-4 left-0 right-0 h-4 bg-gray-300 rounded-full blur-xl opacity-40 group-hover:opacity-80 transition-opacity"></div>
                        </div>

                        {{-- Detalhes do Volume --}}
                        <div class="text-center">
                            <h3 class="text-sm font-black text-gray-900 uppercase tracking-tight mb-1 group-hover:text-brand-500 transition-colors line-clamp-1">
                                {{ $mag->title }}
                            </h3>
                            <p class="text-[9px] font-black text-brand-500/70 uppercase tracking-widest mb-4">
                                {{ $mag->issue_period }}
                            </p>

                            <a href="{{ route('magazines.show', $mag->slug) }}"
                               class="inline-block bg-gray-950 text-white px-8 py-3.5 rounded-2xl font-black uppercase text-[9px] tracking-widest hover:bg-brand-500 transition shadow-md">
                                Abrir Revista
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Caso não tenha revistas salvas no banco --}}
            @if($magazines->isEmpty())
                <div class="text-center py-20 bg-white rounded-[3rem] border-2 border-dashed border-gray-100">
                    <p class="text-gray-400 font-black uppercase text-xs tracking-widest italic">Aguardando as próximas edições...</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Estilos personalizados injetados para o efeito de Perspectiva 3D --}}
    <style>
        .perspective-1000 {
            perspective: 1000px;
        }
        .group-hover\:rotate-y-\[-25deg\]:hover, .group:hover .group-hover\:rotate-y-\[-25deg\] {
            transform: rotateY(-25deg);
        }
    </style>
</x-app-layout>
