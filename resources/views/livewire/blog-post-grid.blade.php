<div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">
        @forelse ($posts as $post)
            @php
                $postCatSlug = $post->blogCategories->first()->slug ?? 'geral';
            @endphp

            <article class="flex flex-col group cursor-pointer justify-between">
                <div>
                    <a href="{{ route('blog.show', ['prefixCategory' => $postCatSlug, 'slug' => $post->slug]) }}" class="relative w-full aspect-[16/9] mb-6 overflow-hidden rounded-[2.5rem] bg-gray-100 shadow-sm block border border-gray-100">
                        @if($post->hasCover())
                            <img src="{{ $post->coverUrl() }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @else
                            <div class="w-full h-full bg-slate-200 group-hover:scale-105 transition-transform duration-500 flex items-center justify-center text-brand-300 font-black italic">
                                RN PET
                            </div>
                        @endif

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

    <x-infinite-scroll :paginator="$posts" class="mt-12" />
</div>
