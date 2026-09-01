<x-app-layout>
    <div class="bg-white py-16 px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">

            <nav class="flex text-[10px] font-black uppercase tracking-widest text-indigo-600 mb-8">
                <a href="{{ route('home') }}" class="hover:text-indigo-800">Início</a>
                <span class="mx-2 text-gray-300">/</span>
                <span class="text-gray-400">Agenda de Eventos</span>
            </nav>

            <header class="mb-12">
                <h1 class="text-4xl md:text-5xl font-black text-gray-900 leading-tight tracking-tight mb-8 uppercase">
                    {{ $event['title'] }}
                </h1>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border-y border-gray-100 py-8">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Data</p>
                            <p class="font-bold text-gray-900">{{ $event['date'] }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Local</p>
                            <p class="font-bold text-gray-900">{{ $event['location'] }}</p>
                        </div>
                    </div>
                </div>
            </header>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                <div class="lg:col-span-2">
                    <div class="aspect-video bg-gray-100 rounded-[3rem] overflow-hidden mb-10 shadow-sm border border-gray-100 flex items-center justify-center">
                        <span class="text-gray-300 font-bold uppercase tracking-widest text-xs">Imagem do Evento</span>
                    </div>

                    <div class="prose prose-lg prose-indigo text-gray-600 font-medium leading-relaxed">
                        <h3 class="text-2xl font-black text-gray-900 mb-4">Sobre o Evento</h3>
                        <p>{{ $event['description'] }}</p>
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <div class="sticky top-32 space-y-6">
                        <div class="p-8 bg-gray-900 rounded-[2.5rem] text-white shadow-xl">
                            <h4 class="font-bold mb-4">Como participar?</h4>
                            <p class="text-sm text-gray-400 mb-6 font-medium">Para credenciamento e ingressos, visite o site oficial.</p>
                            <a href="#" target="_blank" class="block w-full py-4 bg-indigo-600 hover:bg-indigo-700 text-center rounded-2xl font-black uppercase text-[10px] tracking-widest transition">
                                Visitar Site Oficial
                            </a>
                        </div>

                        <div class="p-8 bg-indigo-50 rounded-[2.5rem] border border-indigo-100 text-center">
                            <p class="text-[10px] font-black text-indigo-600 uppercase mb-4 tracking-widest">Endereço</p>
                            <p class="text-sm font-bold text-gray-900 mb-6 leading-relaxed">{{ $event['address'] }}</p>
                            <a href="#" class="text-[10px] font-black text-indigo-600 underline uppercase tracking-widest">Ver no Google Maps</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
