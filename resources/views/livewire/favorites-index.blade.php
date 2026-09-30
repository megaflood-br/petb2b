<div class="bg-gray-50 min-h-screen py-16">
    <div class="max-w-6xl mx-auto px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-12">
            <div>
                <p class="text-[10px] font-black uppercase text-brand-500 tracking-[0.25em] mb-1">Sua coleção</p>
                <h1 class="text-4xl font-black text-gray-900 uppercase italic tracking-tight">
                    @if($folder)
                        {{ $folders[$folder]['icon'] ?? '' }} {{ $folders[$folder]['label'] ?? 'Favoritos' }}
                    @else
                        Meus <span class="text-brand-500">Favoritos</span>
                    @endif
                </h1>
                <p class="mt-3 text-base text-gray-500 font-medium">
                    @if($folder)
                        Itens salvos nesta pasta.
                    @else
                        Matérias, classificados, fornecedores e outros conteúdos separados por pasta.
                    @endif
                </p>
            </div>
            @if($folder)
                <button type="button" wire:click="closeFolder" class="bg-white border border-gray-100 text-gray-700 hover:text-brand-500 px-6 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition shadow-sm">
                    ← Todas as pastas
                </button>
            @endif
        </div>

        @if($folder === '')
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($folders as $key => $meta)
                    @php $total = (int) ($counts[$key] ?? 0); @endphp
                    <button type="button"
                            wire:click="openFolder('{{ $key }}')"
                            class="text-left bg-white border border-gray-100 rounded-[2.5rem] p-8 shadow-sm hover:shadow-xl hover:-translate-y-0.5 transition group">
                        <div class="w-14 h-14 rounded-2xl bg-brand-50 text-2xl flex items-center justify-center mb-5 group-hover:bg-brand-500/10">
                            {{ $meta['icon'] }}
                        </div>
                        <h2 class="text-xl font-black text-gray-900 uppercase italic tracking-tight group-hover:text-brand-500 transition">{{ $meta['label'] }}</h2>
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mt-2">
                            {{ $total }} {{ $total === 1 ? 'item' : 'itens' }}
                        </p>
                    </button>
                @endforeach
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($items as $favorite)
                    @php
                        $item = $favorite->favoritable;
                        $title = \App\Support\FavoriteCatalog::title($item);
                        $url = \App\Support\FavoriteCatalog::url($item);
                        $image = \App\Support\FavoriteCatalog::imageUrl($item);
                    @endphp
                    <div class="bg-white border border-gray-100 rounded-[2.5rem] overflow-hidden shadow-sm hover:shadow-xl transition flex flex-col">
                        <div class="relative aspect-[16/10] bg-gray-100">
                            @if($image)
                                <img src="{{ $image }}" alt="{{ $title }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-brand-300 font-black italic text-2xl">{{ \Illuminate\Support\Str::substr($title, 0, 1) }}</div>
                            @endif
                            <button type="button"
                                    wire:click="remove({{ $favorite->id }})"
                                    class="absolute top-3 right-3 z-20 inline-flex items-center justify-center w-10 h-10 rounded-full bg-brand-500 text-white shadow-sm"
                                    aria-label="Remover dos favoritos">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" /></svg>
                            </button>
                        </div>
                        <div class="p-6 flex-1 flex flex-col">
                            <h3 class="font-black text-gray-900 uppercase italic leading-tight line-clamp-2">{{ $title }}</h3>
                            @if($url)
                                <a href="{{ $url }}" class="mt-auto pt-6 block text-center py-3 bg-gray-900 text-white rounded-2xl font-black uppercase text-[10px] tracking-widest hover:bg-brand-500 transition">
                                    Abrir
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-20 text-center bg-white rounded-[2.5rem] border-2 border-dashed border-gray-100">
                        <p class="text-gray-400 font-black uppercase text-xs tracking-widest">Nenhum favorito nesta pasta ainda.</p>
                    </div>
                @endforelse
            </div>
        @endif
    </div>
</div>
