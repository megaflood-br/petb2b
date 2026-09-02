<x-app-layout>
    <div class="bg-white">
        <article class="bg-white pb-12">
            {{-- Cabeçalho da Notícia --}}
            <header class="pt-16 pb-12 border-b border-gray-50">
                <div class="max-w-4xl mx-auto px-6">
                    <div class="flex items-center gap-3 mb-6">
                        {{-- CORRIGIDO: Badge adaptado para o relacionamento dinâmico plural N-N --}}
                        <span class="bg-brand-50 text-brand-700 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest">
                            {{ $post->blogCategories->first()->name ?? 'Geral' }}
                        </span>
                        <span class="text-gray-300">•</span>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-tighter">
                            {{ $post->created_at->format('d \d\e M, Y') }}
                        </span>
                        @if($post->is_premium)
                            <span class="text-gray-300">•</span>
                            <span class="bg-amber-100 text-amber-700 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest">★ Exclusivo</span>
                        @endif
                        @if($post->is_sponsored)
                            <span class="text-gray-300">•</span>
                            <span class="bg-blue-100 text-blue-700 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest">Patrocinado</span>
                        @endif
                    </div>

                    <h1 class="text-3xl md:text-4xl font-black text-gray-900 uppercase italic tracking-tighter leading-[0.95] mb-8">
                        {{ $post->title }}
                    </h1>

                    {{-- Detalhes da Redação --}}
                    <div class="flex items-center gap-4 border-t border-gray-100 pt-8">
                        <div class="w-12 h-12 bg-brand-500 rounded-2xl flex items-center justify-center text-white font-black italic shadow-lg shadow-brand-100">
                            NP
                        </div>
                        <div>
                            @if($post->is_sponsored && $post->supplier)
                                <p class="text-[10px] font-black text-blue-500 uppercase tracking-widest leading-none mb-1">Conteúdo Patrocinado por</p>
                                <p class="font-black text-gray-900 uppercase text-xs">{{ $post->supplier->name }}</p>
                            @else
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Redação</p>
                                <p class="font-black text-gray-900 uppercase text-xs">Negócios Pet</p>
                            @endif
                        </div>
                    </div>
                </div>
            </header>

            {{-- AD ESPAÇO: LOGO APÓS O HEADER --}}
            <div class="max-w-3xl mx-auto px-6 pt-10">
                <x-ad-space position="post_top" />
            </div>

            {{-- Imagem de Capa --}}
            @if($post->image)
                <div class="max-w-3xl mx-auto px-6 mt-12 mb-12">
                    <div class="aspect-video rounded-[2.5rem] overflow-hidden shadow-xl shadow-gray-100 bg-gray-100 border border-gray-50">
                        <img src="{{ asset('storage/' . $post->image) }}" class="w-full h-full object-cover">
                    </div>
                </div>
            @endif

            {{-- Conteúdo do Post --}}
            <div class="max-w-3xl mx-auto px-6">
                @include('partials.product-review-scorecard', ['review' => $post])

                @if($post->is_premium && ! auth()->check())
                    {{-- Prévia + paywall: conteúdo exclusivo para usuários cadastrados --}}
                    <div class="prose prose-lg prose-brand max-w-none text-xl text-gray-600 leading-relaxed">
                        {{ \Illuminate\Support\Str::limit(strip_tags($post->content), 450) }}
                    </div>

                    <div class="mt-6 bg-gray-950 text-white rounded-[2.5rem] p-10 text-center shadow-xl">
                        <p class="text-[10px] font-black uppercase tracking-[0.3em] text-brand-400 mb-2">Conteúdo Exclusivo</p>
                        <h3 class="text-2xl font-black uppercase italic tracking-tight mb-3">Continue lendo gratuitamente</h3>
                        <p class="text-sm text-gray-400 font-medium max-w-md mx-auto mb-6">Cadastre-se no portal para ler esta matéria completa e acessar todo o conteúdo exclusivo do mercado pet.</p>
                        <div class="flex flex-col sm:flex-row gap-3 justify-center">
                            <a href="{{ route('register') }}" class="bg-brand-500 hover:bg-brand-600 text-white px-8 py-4 rounded-xl font-black uppercase text-[11px] tracking-widest transition">Criar conta grátis</a>
                            <a href="{{ route('login') }}" class="bg-white/10 hover:bg-white/20 text-white px-8 py-4 rounded-xl font-black uppercase text-[11px] tracking-widest transition">Já sou cadastrado</a>
                        </div>
                    </div>
                @else
                    <div class="prose prose-lg prose-brand max-w-none text-xl text-gray-600 leading-relaxed">
                        {!! $post->content !!}
                    </div>
                @endif

                {{-- AD ESPAÇO: FINAL DO CONTEÚDO --}}
                <div class="mt-16">
                    <x-ad-space position="post_footer" />
                </div>

                {{-- Parte de Compartilhar --}}
                <div class="mt-20 pt-10 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-6">
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Gostou deste conteúdo?</p>
                        <h4 class="font-black text-gray-900 uppercase text-lg italic">Compartilhe com sua rede</h4>
                    </div>

                    <div class="flex gap-3">
                        {{-- WhatsApp --}}
                        @php $shareText = urlencode($post->title . ' - ' . url()->current()); @endphp
                        <a href="https://wa.me/?text={{ $shareText }}" target="_blank"
                           class="bg-green-500 text-white p-4 rounded-2xl hover:scale-105 transition shadow-lg shadow-green-100 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.335-1.662c1.72.94 3.675 1.439 5.662 1.439h.005c6.554 0 11.89-5.335 11.893-11.892a11.826 11.826 0 00-3.48-8.413Z"/></svg>
                        </a>
                        {{-- LinkedIn --}}
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ url()->current() }}" target="_blank"
                           class="bg-blue-600 text-white p-4 rounded-2xl hover:scale-105 transition shadow-lg shadow-blue-100 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.761 0 5-2.239 5-5v-14c0-2.761-2.239-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </article>

        {{-- SEÇÃO NOVOS CONTEÚDOS: RECOMENDAÇÕES ALEATÓRIAS --}}
        @if($relatedPosts->count() > 0)
            <div class="bg-gray-50 py-20 border-t border-gray-100">
                <div class="max-w-7xl mx-auto px-6 lg:px-8">
                    <div class="mb-12">
                        <p class="text-[10px] font-black uppercase text-brand-500 tracking-widest mb-1">Continua lendo</p>
                        <h3 class="text-2xl font-black text-gray-900 uppercase italic tracking-tight">Você também pode <span class="text-brand-500">gostar</span></h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        @foreach($relatedPosts as $related)
                            @php
                                // CORREÇÃO CRÍTICA: Resgata o slug da categoria vinculada para as sugestões de leitura
                                $relatedCatSlug = $related->blogCategories->first()->slug ?? 'geral';
                            @endphp
                            <article class="flex flex-col group">
                                {{-- Imagem com link dinâmico corrigido para dois parâmetros --}}
                                <a href="{{ route('blog.show', ['prefixCategory' => 'materias-' . $relatedCatSlug, 'slug' => $related->slug]) }}" class="relative w-full aspect-[16/9] mb-4 overflow-hidden rounded-[2rem] bg-white shadow-sm block border border-gray-100">
                                    @if($related->image)
                                        <img src="{{ asset('storage/' . $related->image) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                    @else
                                        <div class="w-full h-full bg-slate-200 group-hover:scale-105 transition-transform duration-500 flex items-center justify-center text-gray-400">
                                            <i class="fas fa-newspaper text-xl"></i>
                                        </div>
                                    @endif

                                    <div class="absolute top-4 left-4 z-10">
                                        <span class="bg-white/90 backdrop-blur px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest text-brand-600 shadow-sm">
                                            {{ $related->blogCategories->first()->name ?? 'Geral' }}
                                        </span>
                                    </div>
                                </a>

                                {{-- Detalhes --}}
                                <div class="px-2">
                                    <h4 class="text-base font-bold text-gray-900 group-hover:text-brand-500 transition line-clamp-2 leading-snug uppercase">
                                        <a href="{{ route('blog.show', ['prefixCategory' => 'materias-' . $relatedCatSlug, 'slug' => $related->slug]) }}">
                                            {{ $related->title }}
                                        </a>
                                    </h4>
                                    <p class="mt-2 text-xs text-gray-400 font-medium">{{ $related->created_at->format('d/m/Y') }}</p>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
