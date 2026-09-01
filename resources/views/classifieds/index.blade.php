<x-app-layout>
    <div class="bg-gray-50 min-h-screen py-12">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">

            <div class="flex flex-col md:flex-row justify-between items-end mb-12 gap-6">
                <div class="max-w-2xl">
                    <h1 class="text-3xl font-black text-gray-900 uppercase tracking-tight">
                        Classificados <span class="text-indigo-600">Equipamentos</span>
                    </h1>
                    <p class="mt-2 text-gray-500 font-medium">Maquinário usado e seminovo com procedência para o seu banho e tosa.</p>
                </div>
                <a href="#" class="bg-indigo-600 text-white px-8 py-4 rounded-2xl font-black uppercase text-xs tracking-widest hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">
                    Anunciar Equipamento
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-10">
                <input type="text" placeholder="O que você busca?" class="md:col-span-2 border-gray-200 rounded-2xl py-3 px-6 shadow-sm focus:ring-indigo-500">
                <select class="border-gray-200 rounded-2xl py-3 shadow-sm focus:ring-indigo-500">
                    <option>Todas as Categorias</option>
                    <option>Sopradores/Secadores</option>
                    <option>Mesas de Tosa</option>
                    <option>Banheiras</option>
                </select>
                <select class="border-gray-200 rounded-2xl py-3 shadow-sm focus:ring-indigo-500">
                    <option>Preço: Menor para Maior</option>
                    <option>Preço: Maior para Menor</option>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                @for ($i = 1; $i <= 8; $i++)
                <div class="bg-white rounded-[2.5rem] border border-gray-100 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 group">
                    <div class="aspect-square bg-gray-100 relative overflow-hidden">
                        <div class="w-full h-full bg-slate-200 group-hover:scale-110 transition-transform duration-500"></div>
                        <div class="absolute top-4 left-4">
                            <span class="bg-white/90 backdrop-blur px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest text-gray-600 shadow-sm">
                                Seminovo
                            </span>
                        </div>
                    </div>

                    <div class="p-6">
                        <p class="text-[10px] font-black text-indigo-500 uppercase tracking-widest mb-2">Sopradores</p>
                        <h3 class="font-bold text-gray-900 leading-tight mb-4 group-hover:text-indigo-600 transition">Soprador Plenitude 10.0 Silencioso</h3>

                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter">Preço</p>
                                <p class="text-xl font-black text-gray-900">R$ 850,00</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[9px] text-gray-400 font-bold uppercase">{{ $i % 2 == 0 ? 'SP' : 'RJ' }}</p>
                                <p class="text-[9px] text-gray-400 font-medium">Atibaia</p>
                            </div>
                        </div>

                        <a href="#" class="mt-6 block w-full py-3 bg-gray-50 rounded-xl text-center text-[10px] font-black uppercase tracking-widest text-gray-400 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                            Ver Detalhes
                        </a>
                    </div>
                </div>
                @endfor
            </div>

        </div>
    </div>
</x-app-layout>
