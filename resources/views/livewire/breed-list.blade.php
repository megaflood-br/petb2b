<div class="bg-gray-50 min-h-screen">

    <div class="bg-gray-950 text-white py-16">
        <div class="max-w-6xl mx-auto px-6">
            <p class="text-[10px] font-black uppercase text-brand-400 tracking-[0.3em] mb-2">Guia de Espécies e Raças</p>
            <h1 class="text-3xl md:text-5xl font-black uppercase italic tracking-tight">Conheça as <span class="text-brand-500">Raças</span></h1>
            <p class="mt-3 text-sm text-gray-400 font-medium max-w-2xl">Características, porte, temperamento e origem das principais raças de pets.</p>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-6 py-12">

        {{-- Filtros --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-10">
            <input type="text" wire:model.live.debounce.400ms="search" placeholder="Buscar raça..." class="bg-white border border-gray-100 rounded-xl p-4 text-sm focus:ring-2 focus:ring-brand-500">
            <select wire:model.live="species" class="bg-white border border-gray-100 rounded-xl p-4 text-sm focus:ring-2 focus:ring-brand-500">
                <option value="">Todas as espécies</option>
                @foreach($speciesList as $sp)
                    <option value="{{ $sp }}">{{ $sp }}</option>
                @endforeach
            </select>
        </div>

        {{-- Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($breeds as $breed)
                <a href="{{ route('breeds.show', $breed->slug) }}" class="block bg-white border border-gray-100 rounded-[2rem] overflow-hidden shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition group">
                    <div class="aspect-[4/3] bg-gray-100 overflow-hidden">
                        @if($breed->image)
                            <img src="{{ asset('storage/' . $breed->image) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-300 text-5xl font-black italic">{{ Str::substr($breed->name, 0, 1) }}</div>
                        @endif
                    </div>
                    <div class="p-5">
                        <span class="text-[9px] font-black uppercase tracking-wider bg-brand-50 text-brand-600 px-3 py-1 rounded-full">{{ $breed->species }}</span>
                        <h2 class="text-lg font-black text-gray-900 uppercase mt-2 group-hover:text-brand-500 transition">{{ $breed->name }}</h2>
                        <p class="text-xs text-gray-500 font-medium mt-1">{{ $breed->size ? 'Porte ' . $breed->size : '' }}{{ $breed->origin ? ' · ' . $breed->origin : '' }}</p>
                    </div>
                </a>
            @empty
                <div class="col-span-full py-20 text-center bg-white rounded-2xl border border-dashed border-gray-200">
                    <p class="text-gray-400 font-bold uppercase tracking-widest">Nenhuma raça encontrada.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-10">{{ $breeds->links() }}</div>
    </div>
</div>
