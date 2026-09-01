<x-app-layout>
    <div class="bg-gray-50 min-h-screen py-12">
        <div class="max-w-4xl mx-auto px-6">
            <a href="{{ route('breeds.index') }}" class="text-[10px] font-black uppercase tracking-widest text-brand-500 hover:underline">← Voltar ao guia de raças</a>

            <div class="bg-white border border-gray-100 rounded-[2.5rem] overflow-hidden shadow-sm mt-4">
                @if($breed->image)
                    <div class="aspect-[21/9] bg-gray-100 overflow-hidden">
                        <img src="{{ asset('storage/' . $breed->image) }}" class="w-full h-full object-cover">
                    </div>
                @endif

                <div class="p-8 md:p-10">
                    <span class="text-[9px] font-black uppercase tracking-wider bg-brand-50 text-brand-600 px-3 py-1.5 rounded-full">{{ $breed->species }}</span>
                    <h1 class="text-3xl font-black text-gray-900 uppercase italic tracking-tight mt-4">{{ $breed->name }}</h1>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6">
                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                            <p class="text-[9px] font-black uppercase text-gray-400 tracking-widest">Porte</p>
                            <p class="text-sm font-black text-gray-900 mt-1">{{ $breed->size ?: '—' }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                            <p class="text-[9px] font-black uppercase text-gray-400 tracking-widest">Origem</p>
                            <p class="text-sm font-black text-gray-900 mt-1">{{ $breed->origin ?: '—' }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                            <p class="text-[9px] font-black uppercase text-gray-400 tracking-widest">Temperamento</p>
                            <p class="text-sm font-black text-gray-900 mt-1">{{ $breed->temperament ?: '—' }}</p>
                        </div>
                    </div>

                    <div class="prose prose-sm max-w-none mt-8 text-gray-700 whitespace-pre-line leading-relaxed">{{ $breed->description }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
