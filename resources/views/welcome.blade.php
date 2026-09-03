<x-app-layout>
    <div class="bg-white">
        {{-- SEÇÃO 1: HERO, BUSCA & MATÉRIA DESTAQUE SUPERIOR (SLIDER DINÂMICO) --}}
        <div class="bg-brand-500 py-20 px-6 lg:px-8">
            <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

                {{-- Lado Esquerdo: Chamada e Busca --}}
                <div class="lg:col-span-6 text-center lg:text-left">
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-white uppercase tracking-tighter italic mb-6 leading-[0.95]">
                        Conectando o Mercado <br><span class="text-brand-300">Pet Brasileiro</span>
                    </h1>
                    <p class="text-brand-200 font-medium text-base md:text-lg mb-8 uppercase tracking-widest">
                        Busque fornecedores, produtos e notícias em um só lugar.
                    </p>

                    <form action="{{ route('general.search') }}" method="GET" class="relative group max-w-2xl mx-auto lg:mx-0">
                        <input type="text" name="search" placeholder="O que seu negócio precisa hoje?"
                            class="w-full bg-white rounded-full py-5 px-8 text-base font-bold shadow-2xl focus:ring-4 focus:ring-brand-500/50 border-none transition-all outline-none"
                            value="{{ request('search') }}">

                        <button type="submit"
                            class="absolute right-2.5 top-2.5 bottom-2.5 bg-brand-500 text-white px-8 rounded-full font-black uppercase text-xs tracking-widest hover:bg-brand-600 transition shadow-lg shadow-brand-900/20">
                            Buscar
                        </button>
                    </form>

                    <a href="{{ route('advertise') }}" class="inline-block mt-6 text-brand-300 font-black uppercase text-[10px] tracking-[0.3em] hover:text-white transition group">
                        Interessado em anunciar sua marca? <span class="border-b-2 border-brand-400 group-hover:border-white">Clique aqui</span>
                    </a>
                </div>
{{-- Lado Direito: Carrossel de Matérias em Super Destaque (CORRIGIDO TAMANHO E CORTE DE TEXTO) --}}
<div class="lg:col-span-6">
    @php
        $featuredPosts = $latestPosts->where('is_featured', true);
    @endphp

    @if($featuredPosts->count() > 0)
        <div x-data="{
                activeSlide: 0,
                slidesCount: {{ $featuredPosts->count() }},
                autoSlide() {
                    setInterval(() => {
                        this.activeSlide = (this.activeSlide + 1) % this.slidesCount;
                    }, 5000);
                }
             }"
             x-init="autoSlide()"
             class="relative w-full pb-12"> {{-- Padding extra embaixo para acomodar os indicadores com folga --}}

            {{-- Aumentado a altura do container pai para evitar cortes em títulos de 3 linhas --}}
            <div class="relative h-[530px] sm:h-[560px] w-full">
                @foreach($featuredPosts->values() as $index => $featured)
                    @php
                        $featuredCatSlug = $featured->blogCategories->first()->slug ?? 'geral';
                    @endphp

                    <div x-show="activeSlide === {{ $index }}"
                         x-transition:enter="transition ease-out duration-500"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-300"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute inset-0 bg-white/10 backdrop-blur-md p-6 rounded-[3rem] border border-white/10 shadow-2xl group flex flex-col justify-between"
                         style="display: none;">

                        <a href="{{ route('blog.show', ['prefixCategory' => $featuredCatSlug, 'slug' => $featured->slug]) }}" class="flex flex-col h-full justify-between">

                            {{-- Container da Imagem --}}
                            <div class="aspect-[16/9] sm:aspect-[16/10] rounded-[2rem] overflow-hidden mb-4 relative shadow-md bg-brand-600 shrink-0">
                                @if($featured->image)
                                    <img src="{{ asset('storage/' . $featured->image) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                                @else
                                    <div class="w-full h-full bg-brand-600 flex items-center justify-center text-white/20 font-black italic text-2xl">NP</div>
                                @endif
                                <div class="absolute top-4 left-4">
                                    <span class="bg-amber-400 text-black text-[9px] font-black px-3 py-1 rounded-full uppercase tracking-widest shadow-md flex items-center gap-1">
                                        ⚡ Super Destaque
                                    </span>
                                </div>
                            </div>

                            {{-- Bloco de Textos Dinâmicos com Scroll Interno Invisível se estourar --}}
                            <div class="flex-1 flex flex-col justify-start overflow-hidden pr-1">
                                <span class="text-[10px] font-black uppercase text-brand-300 tracking-widest block mb-1">
                                    {{ $featured->blogCategories->first()->name ?? 'Geral' }}
                                </span>

                                <h3 class="text-xl sm:text-2xl md:text-3xl font-black text-white uppercase italic leading-tight group-hover:text-brand-300 transition-colors line-clamp-2 mb-2">
                                    {{ $featured->title }}
                                </h3>

                                {{-- Descrição inteligente: Garante visualização das duas linhas sem quebrar o layout --}}
                                <div class="text-brand-100/80 text-xs sm:text-sm font-medium line-clamp-3 overflow-y-auto max-h-[60px] scrollbar-none">
                                    {{ Str::limit(strip_tags($featured->content), 160) }}
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>

            {{-- Indicadores reposicionados fora do limite absoluto do carrossel --}}
            @if($featuredPosts->count() > 1)
                <div class="absolute bottom-2 left-0 right-0 flex justify-center gap-2 z-20">
                    @foreach($featuredPosts->values() as $index => $f)
                        <button @click="activeSlide = {{ $index }}"
                                :class="activeSlide === {{ $index }} ? 'bg-white w-8' : 'bg-white/40 w-2'"
                                class="h-2 rounded-full transition-all duration-300 outline-none focus:outline-none"></button>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</div>

{{-- Estilo complementar embutido para esconder as barras de rolagem no Bloco de Texto --}}
<style>
    .scrollbar-none::-webkit-scrollbar { display: none; }
    .scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }
</style>

            </div>
        </div>

        {{-- BLOCO DO BANNER DE PUBLICIDADE ROTATIVA --}}
        @if(isset($bannerHome) && $bannerHome)
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 my-8">
                <div class="w-full bg-gray-50 rounded-2xl overflow-hidden border border-gray-100 shadow-sm transition hover:shadow-md">
                    <a href="{{ route('ads.redirect', $bannerHome->id) }}" target="_blank" rel="noopener noreferrer" class="block w-full h-full">
                        <img src="{{ asset('storage/' . $bannerHome->image_path) }}"
                            alt="{{ $bannerHome->title }}"
                            class="w-full h-auto max-h-[160px] object-cover mx-auto block"
                            title="Patrocinado: {{ $bannerHome->title }}">
                    </a>
                </div>
            </div>
        @endif

        {{-- SEÇÃO 2: GRID DE MATÉRIAS - DESTAQUES DA EDIÇÃO --}}
        <section class="py-24 bg-white">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-12 gap-4">
                    <div>
                        <p class="text-[10px] font-black uppercase text-brand-500 tracking-[0.25em] mb-1">Conteúdo Exclusivo</p>
                        <h2 class="text-3xl md:text-4xl font-black text-gray-900 uppercase italic tracking-tight">
                            Destaques da <span class="text-brand-500">Edição</span>
                        </h2>
                    </div>
                    <a href="{{ route('blog.index') }}" class="bg-brand-50 text-brand-600 hover:bg-brand-500 hover:text-white px-6 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition shadow-sm">
                        Acessar Blog Completo →
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                    @forelse($latestPosts as $post)
                        @php
                            $postCatSlug = $post->blogCategories->first()->slug ?? 'geral';
                        @endphp
                        <article class="flex flex-col group">
                            <a href="{{ route('blog.show', ['prefixCategory' => $postCatSlug, 'slug' => $post->slug]) }}" class="relative w-full aspect-[16/9] mb-5 overflow-hidden rounded-[2.5rem] bg-gray-100 shadow-sm border border-gray-100 block">
                                @if($post->image)
                                    <img src="{{ asset('storage/' . $post->image) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                @else
                                    <div class="w-full h-full bg-slate-200 flex items-center justify-center text-gray-400 font-bold text-xs">Sem Imagem</div>
                                @endif
                                <div class="absolute top-4 left-4 z-10">
                                    <span class="bg-white/95 backdrop-blur px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest text-brand-600 shadow-sm">
                                        {{ $post->blogCategories->first()->name ?? 'Geral' }}
                                    </span>
                                </div>
                            </a>
                            <div class="px-2">
                                <div class="flex items-center gap-2 text-[10px] text-gray-400 font-black mb-2 uppercase">
                                    <span>{{ $post->created_at->format('d \d\e M') }}</span>
                                    <span>•</span>
                                    <span class="text-brand-500">Revista NP</span>
                                </div>
                                <h3 class="text-lg font-black text-gray-900 group-hover:text-brand-500 transition line-clamp-2 leading-snug uppercase italic">
                                    <a href="{{ route('blog.show', ['prefixCategory' => $postCatSlug, 'slug' => $post->slug]) }}">{{ $post->title }}</a>
                                </h3>
                            </div>
                        </article>
                    @empty
                        <div class="col-span-full py-12 text-center border-2 border-dashed border-gray-100 rounded-[2rem]">
                            <p class="text-gray-400 text-xs font-bold uppercase tracking-widest">Nenhuma matéria encontrada no momento.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- SEÇÃO 3: REVISTA DIGITAL --}}
        @if($latestMagazine)
        <section class="py-20 bg-gray-50 border-t border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="bg-brand-500 rounded-[3.5rem] p-12 md:p-20 relative overflow-hidden shadow-2xl shadow-brand-200">
                    <div class="flex flex-col md:flex-row items-center gap-16 relative z-10">
                        <div class="w-64 md:w-80 flex-shrink-0 relative group">
                            <div class="absolute -inset-4 bg-white/10 rounded-[2.5rem] blur-2xl group-hover:bg-brand-500/20 transition duration-700"></div>
                            <img src="{{ asset('storage/' . $latestMagazine->cover_path) }}"
                                class="rounded-2xl w-full shadow-[20px_20px_60px_rgba(0,0,0,0.5)] rotate-2 group-hover:rotate-0 transition-transform duration-700 relative z-10 border border-white/10">
                        </div>

                        <div class="flex-1 text-center md:text-left text-white">
                            <span class="bg-brand-600 text-[10px] font-black uppercase px-4 py-1.5 rounded-full tracking-widest inline-block mb-6">
                                Conteúdo Exclusivo
                            </span>
                            <h2 class="text-5xl md:text-6xl font-black uppercase italic tracking-tighter leading-[0.85] mb-6">
                                Revista Digital <br>
                                <span class="text-brand-300">{{ $latestMagazine->issue_period }}</span>
                            </h2>
                            <p class="text-brand-100/70 font-bold italic text-base mb-10 max-w-md">
                                Acesse agora a edição completa com as maiores tendências do setor pet brasileiro.
                            </p>
                            <a href="{{ route('magazines.show', $latestMagazine->slug) }}"
                               class="inline-block bg-white text-brand-500 px-12 py-5 rounded-2xl font-black uppercase text-xs tracking-widest hover:bg-gray-900 hover:text-white transition shadow-xl">
                                Abrir e Folhear Revista
                            </a>
                        </div>
                    </div>
                    <div class="absolute -top-24 -right-24 w-96 h-96 bg-brand-800 rounded-full blur-[120px] opacity-40"></div>
                </div>
            </div>
        </section>
        @endif

        {{-- SEÇÃO NOVA: TUDO SOBRE RAÇAS --}}
        @php
            $racePosts = \App\Models\Post::where('is_active', true)
                ->whereHas('blogCategories', function($q) {
                    $q->where('slug', 'racas');
                })
                ->latest()
                ->take(3)
                ->get();
        @endphp
        @if($racePosts->count() > 0)
            <section class="py-24 bg-gray-50 border-b border-gray-100">
                <div class="max-w-7xl mx-auto px-6 lg:px-8">
                    <div class="mb-12">
                        <p class="text-[10px] font-black uppercase text-brand-500 tracking-[0.25em] mb-1">Guia de Espécies</p>
                        <h2 class="text-3xl md:text-4xl font-black text-gray-900 uppercase italic tracking-tight">
                            Tudo sobre <span class="text-brand-500">Raças</span>
                        </h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                        @foreach($racePosts as $racePost)
                            <article class="flex flex-col group bg-white p-5 rounded-[2.5rem] border border-gray-100 shadow-sm hover:shadow-md transition">
                                <a href="{{ route('blog.show', ['prefixCategory' => 'racas', 'slug' => $racePost->slug]) }}" class="relative w-full aspect-[16/10] mb-5 overflow-hidden rounded-[2rem] bg-gray-100 block">
                                    @if($racePost->image)
                                        <img src="{{ asset('storage/' . $racePost->image) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                    @else
                                        <div class="w-full h-full bg-slate-200 flex items-center justify-center text-gray-400 font-bold text-xs">Sem Imagem</div>
                                    @endif
                                </a>
                                <div class="px-2">
                                    <h3 class="text-lg font-black text-gray-900 group-hover:text-brand-500 transition line-clamp-2 leading-snug uppercase italic">
                                        <a href="{{ route('blog.show', ['prefixCategory' => 'racas', 'slug' => $racePost->slug]) }}">{{ $racePost->title }}</a>
                                    </h3>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- SEÇÃO 4: FORNECEDORES EM DESTAQUE --}}
        <section class="py-24 bg-white">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <h2 class="text-2xl font-black text-gray-900 mb-10 uppercase tracking-tight">Fornecedores em Destaque</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    @foreach($featuredSuppliers as $supplier)
                        <div class="bg-white rounded-[2.5rem] p-8 border border-gray-100 shadow-sm hover:shadow-xl transition group">
                            <div class="w-20 h-20 bg-gray-50 rounded-2xl mb-6 flex items-center justify-center overflow-hidden border border-gray-100">
                                @if($supplier->logo)
                                    <img src="{{ asset('storage/' . $supplier->logo) }}" alt="{{ $supplier->name }}" class="w-full h-full object-contain">
                                @else
                                    <span class="text-gray-200 font-black italic text-xl">PBP</span>
                                @endif
                            </div>
                            <h3 class="text-xl font-black text-gray-900 uppercase tracking-tighter mb-2">{{ $supplier->name }}</h3>
                            <p class="text-gray-500 text-sm font-medium line-clamp-2 mb-6">{{ $supplier->description }}</p>
                            <a href="{{ route('suppliers.show', $supplier->slug) }}" class="inline-flex items-center text-[10px] font-black uppercase tracking-widest text-brand-600 group-hover:gap-2 transition-all">
                                Ver Perfil Completo <span>→</span>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- SEÇÃO 5: CTA INDÚSTRIA --}}
        <section class="py-20 bg-brand-50">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="bg-white rounded-[3rem] p-8 md:p-16 shadow-xl shadow-brand-100 border border-brand-100 flex flex-col md:flex-row items-center justify-between gap-12">
                    <div class="max-w-xl text-center md:text-left">
                        <h2 class="text-3xl font-black text-gray-900 uppercase tracking-tight mb-4">
                            Sua indústria em <span class="text-brand-500">Destaque</span>
                        </h2>
                        <p class="text-gray-500 font-medium leading-relaxed">
                            Apareça para mais de 50 mil lojistas e decisores do mercado pet todos os meses.
                        </p>
                    </div>
                    <div class="flex-shrink-0">
                        <a href="{{ route('advertise') }}" class="inline-block bg-brand-500 text-white px-10 py-5 rounded-2xl font-black uppercase text-xs tracking-widest hover:bg-brand-700 transition shadow-lg shadow-brand-200">
                            Conhecer Opções de Anúncio
                        </a>
                    </div>
                </div>
            </div>
        </section>

        {{-- SEÇÃO 6: OPORTUNIDADES (CLASSIFICADOS) --}}
        @if($featuredClassifieds->count() > 0)
            <section class="py-24 bg-white border-t border-b border-gray-100">
                <div class="max-w-7xl mx-auto px-6 lg:px-8">
                    <div class="flex justify-between items-end mb-12">
                        <div>
                            <h2 class="text-3xl font-black text-gray-900 uppercase tracking-tight">Oportunidades</h2>
                            <p class="mt-2 text-gray-500 font-medium">Equipamentos usados e seminovos com preços especiais.</p>
                        </div>
                        <a href="{{ route('classifieds.index') }}" class="text-xs font-black text-brand-500 uppercase tracking-[0.2em] hover:text-brand-800 transition">
                            Ver todos os anúncios →
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        @foreach($featuredClassifieds as $ad)
                            <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden group">
                                <div class="aspect-square bg-white relative overflow-hidden flex items-center justify-center p-4">
                                    @if($ad->image)
                                        <img src="{{ asset('storage/' . $ad->image) }}" alt="{{ $ad->title }}" class="max-w-full max-h-full object-contain transition duration-500 group-hover:scale-105">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-200 font-black italic">SEM FOTO</div>
                                    @endif
                                    <div class="absolute top-4 left-4">
                                        <span class="bg-brand-500 text-white px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest shadow-sm">{{ $ad->condition }}</span>
                                    </div>
                                </div>
                                <div class="p-6">
                                    <h3 class="font-black text-gray-900 uppercase tracking-tight line-clamp-1">{{ $ad->title }}</h3>
                                    <div class="mt-4 flex items-center justify-between">
                                        <span class="text-xl font-black text-gray-900">R$ {{ number_format($ad->price, 2, ',', '.') }}</span>
                                        <a href="{{ route('classifieds.show', $ad->slug) }}" class="text-[10px] font-black uppercase text-brand-500 tracking-widest hover:underline">Saiba Mais</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- SEÇÃO 7: ANÁLISES DE PRODUTOS --}}
        @if($featuredReviews->count() > 0)
            <section class="py-24 px-6 max-w-7xl mx-auto">
                <div class="flex justify-between items-end mb-10">
                    <h2 class="text-2xl font-black text-gray-900 uppercase italic leading-none">
                        Análises de <span class="text-brand-500">Produtos</span>
                    </h2>
                    <a href="{{ route('reviews.index') }}" class="text-[10px] font-black uppercase text-brand-600 tracking-widest hover:underline">Ver Todas</a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    @foreach($featuredReviews as $review)
                        <a href="{{ route('reviews.show', $review->slug) }}" class="flex flex-col group">
                            <div class="relative w-full aspect-[16/9] bg-gray-100 rounded-[2.5rem] mb-6 overflow-hidden border border-gray-100">
                                @if($review->image)
                                    <img src="{{ asset('storage/' . $review->image) }}" class="w-full h-full object-cover transition duration-500 group-hover:scale-105" alt="{{ $review->title }}">
                                @endif
                            </div>
                            <div class="flex flex-col mt-2">
                                <span class="text-[9px] font-black text-brand-500 uppercase tracking-[0.3em] mb-2">{{ $review->category }}</span>
                                <h3 class="text-xl font-black text-gray-900 uppercase leading-tight group-hover:text-brand-500 transition mb-3">{{ $review->title }}</h3>
                                <p class="text-gray-500 text-sm line-clamp-2 font-medium">{{ \Illuminate\Support\Str::limit(strip_tags($review->content), 140) }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- SEÇÃO 8: AGENDA LOCAL COM EXIBIÇÃO DE IMAGENS --}}
        <div class="py-24 bg-gray-50 border-t border-gray-100">
            <div class="max-w-3xl mx-auto px-6 lg:px-8">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-2xl font-black text-gray-900 uppercase tracking-tight">Agenda de Eventos</h2>
                    <a href="{{ route('events.index') }}" class="text-xs font-black text-brand-500 uppercase tracking-widest">Ver Todos →</a>
                </div>
                <div class="space-y-6">
                    @forelse($upcomingEvents as $event)
                        <a href="{{ route('events.index', ['slug' => $event->slug]) }}" class="flex items-center gap-6 p-6 bg-white rounded-[2rem] border border-gray-100 hover:shadow-xl transition-all group">
                            <div class="w-20 h-20 bg-gray-100 rounded-2xl overflow-hidden shrink-0 relative border border-gray-50">
                                @if($event->image)
                                    <img src="{{ asset('storage/' . $event->image) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                @else
                                    <div class="w-full h-full bg-brand-50 flex flex-col items-center justify-center text-brand-500 font-black">
                                        <span class="text-[9px] uppercase leading-none mb-0.5">{{ $event->start_date->translatedFormat('M') }}</span>
                                        <span class="text-base leading-none">{{ $event->start_date->format('d') }}</span>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <h4 class="font-black text-gray-900 uppercase text-sm leading-tight mb-1 group-hover:text-brand-500 transition-colors">{{ $event->title }}</h4>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ $event->city }}/{{ $event->state }} • {{ $event->start_date->format('d/m/Y') }}</p>
                            </div>
                        </a>
                    @empty
                        <p class="text-gray-400 text-xs font-bold uppercase italic text-center py-4">Nenhum evento próximo agendado.</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
