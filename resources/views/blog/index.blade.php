<x-app-layout>
    <div class="bg-white py-16">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">

            {{-- Título da Seção --}}
            <div class="max-w-2xl mb-12">
                <h1 class="text-4xl font-black text-gray-900 uppercase tracking-tight italic">
                    Conteúdo <span class="text-brand-500">Estratégico</span>
                </h1>
                <p class="mt-4 text-base text-gray-500 font-medium">Análises técnicas, tendências de mercado e novidades do setor pet brasileiro.</p>
            </div>

            {{-- BARRA DE FILTROS OTIMIZADA: Layout moderno em linha única com dropdown dinâmico --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-12 border-b border-gray-100 pb-6 text-xs font-black uppercase tracking-widest" x-data="{ openMore: false }">

                {{-- Lado Esquerdo: Filtros Principais e Rápidos --}}
                <div class="flex flex-wrap items-center gap-2">
                    {{-- Botão Ver Tudo --}}
                    <a href="{{ route('blog.index') }}"
                       class="px-5 py-3 rounded-xl transition duration-200 {{ !request()->segment(2) ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/20' : 'bg-gray-50 text-gray-600 hover:bg-gray-100' }}">
                        ✨ Ver Tudo
                    </a>

                    {{-- Exibe em destaque apenas as 5 primeiras editorias editoriais legítimas --}}
                    @foreach($blogCategories->take(5) as $cat)
                        <a href="{{ route('blog.category', $cat->slug) }}"
                           class="px-5 py-3 rounded-xl transition duration-200 {{ request()->segment(2) == $cat->slug ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/20' : 'bg-gray-50 text-gray-600 hover:bg-gray-100' }}">
                            {{ $cat->name }}
                        </a>
                    @endforeach
                </div>

                {{-- Lado Direito: Dropdown de "Mais Assuntos" se houver mais de 5 categorias --}}
                @if($blogCategories->count() > 5)
                    <div class="relative inline-block text-left shrink-0">
                        <button @click="openMore = !openMore" @click.away="openMore = false"
                                class="inline-flex items-center gap-2 px-5 py-3 bg-gray-50 hover:bg-gray-100 text-gray-700 rounded-xl transition duration-200 focus:outline-none">
                            <span>Mais Assuntos</span>
                            <svg class="w-3 h-3 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': openMore }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        {{-- Lista suspensa flutuante com scroll inteligente --}}
                        <div x-show="openMore"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                             x-transition:leave-end="opacity-0 scale-95 translate-y-1"
                             class="absolute right-0 mt-2 w-64 rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 border border-gray-100 z-30 max-h-[260px] overflow-y-auto custom-filter-scrollbar py-2"
                             style="display: none;">

                            @foreach($blogCategories->skip(5) as $cat)
                                <a href="{{ route('blog.category', $cat->slug) }}"
                                   class="block px-4 py-2.5 text-[11px] font-bold text-gray-600 hover:bg-brand-50 hover:text-brand-500 transition-colors {{ request()->segment(2) == $cat->slug ? 'text-brand-500 bg-brand-50/40' : '' }}">
                                    • {{ $cat->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Grid de Artigos --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">
                @forelse ($posts as $post)
                    @php
                        $postCatSlug = $post->blogCategories->first()->slug ?? 'geral';
                    @endphp

                    <article class="flex flex-col group cursor-pointer justify-between">
                        <div>
                            {{-- Container da Imagem de Capa --}}
                            <a href="{{ route('blog.show', ['prefixCategory' => $postCatSlug, 'slug' => $post->slug]) }}" class="relative w-full aspect-[16/9] mb-6 overflow-hidden rounded-[2.5rem] bg-gray-100 shadow-sm block border border-gray-100">
                                @if($post->image)
                                    <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                @else
                                    <div class="w-full h-full bg-slate-200 group-hover:scale-105 transition-transform duration-500 flex items-center justify-center text-brand-300 font-black italic">
                                        RN PET
                                    </div>
                                @endif

                                {{-- Selos / Badges por Cima da Imagem --}}
                                <div class="absolute top-4 left-4 flex gap-2">
                                    @if($post->is_featured)
                                        <span class="bg-amber-400 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest text-black shadow-sm flex items-center gap-1">
                                            ⚡ Destaque
                                        </span>
                                    @endif

                                    @if($post->blogCategories->first())
                                        <span class="bg-white/90 backdrop-blur px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest text-brand-500 shadow-sm">
                                            {{ $post->blogCategories->first()->name }}
                                        </span>
                                    @endif
                                </div>
                            </a>

                            {{-- Detalhes e Textos do Post --}}
                            <div class="px-2">
                                <div class="flex items-center gap-2 text-xs text-gray-400 font-bold mb-3 uppercase tracking-tighter">
                                    <span>{{ $post->created_at->format('d M, Y') }}</span>
                                    <span class="text-brand-300">•</span>

                                    @php
                                        $wordCount = str_word_count(strip_tags($post->content));
                                        $readDuration = ceil($wordCount / 200);
                                        $readDuration = $readDuration < 1 ? 1 : $readDuration;
                                    @endphp
                                    <span>{{ $readDuration }} min de leitura</span>
                                </div>

                                <h2 class="text-xl font-bold text-gray-900 group-hover:text-brand-500 transition leading-snug">
                                    <a href="{{ route('blog.show', ['prefixCategory' => $postCatSlug, 'slug' => $post->slug]) }}">
                                        {{ $post->title }}
                                    </a>
                                </h2>

                                {{-- CORREGIDO DEFINITIVO: Substituído Hex::limit por Str::limit para matar o erro 500 --}}
                                <p class="mt-4 text-gray-500 text-sm leading-relaxed line-clamp-3 font-medium">
                                    {{ \Illuminate\Support\Str::limit(strip_tags($post->content), 150) }}
                                </p>
                            </div>
                        </div>

                        <a href="{{ route('blog.show', ['prefixCategory' => $postCatSlug, 'slug' => $post->slug]) }}" class="sr-only">Ler post completo</a>
                    </article>
                @empty
                    <div class="col-span-full py-20 text-center border-2 border-dashed border-gray-100 rounded-[2rem]">
                        <p class="text-gray-400 font-bold uppercase tracking-widest">Nenhum artigo encontrado nesta categoria.</p>
                    </div>
                @endforelse
            </div>

            {{-- Zona de Anúncio --}}
            @php
                $bannerBlog = \App\Models\Advertisement::where('is_active', true)
                    ->where('position', 'meio_blog')
                    ->inRandomOrder()
                    ->first();

                if ($bannerBlog) {
                    $bannerBlog->trackImpression();
                    $bannerBlog->refresh();
                }
            @endphp

            @if($bannerBlog)
                <div class="w-full my-12">
                    <div class="bg-white rounded-[2rem] overflow-hidden border border-gray-100 shadow-sm transition hover:shadow-md">
                        <a href="{{ route('ads.redirect', $bannerBlog->id) }}" target="_blank" rel="noopener noreferrer" class="block w-full">
                            <img src="{{ asset('storage/' . $bannerBlog->image_path) }}"
                                 alt="{{ $bannerBlog->title }}"
                                 class="w-full h-auto max-h-[200px] object-cover mx-auto block"
                                 title="Patrocinado: {{ $bannerBlog->title }}">
                        </a>
                    </div>
                </div>
            @endif

            {{-- Paginação Dinâmica --}}
            @if($posts->hasPages())
                <div class="mt-16 border-t border-gray-100 pt-10">
                    {{ $posts->links() }}
                </div>
            @endif

        </div>
    </div>
</x-app-layout>

<style>
    .custom-filter-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-filter-scrollbar::-webkit-scrollbar-track { background: #f9fafb; border-radius: 4px; }
    .custom-filter-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 4px; }
    .custom-filter-scrollbar::-webkit-scrollbar-thumb:hover { background: #d1d5db; }
</style>
