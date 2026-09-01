<x-app-layout>
    <div class="bg-white py-16 px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">

            <header class="mb-12">
                <nav class="flex text-[10px] font-black uppercase tracking-widest text-indigo-600 mb-8">
                    <a href="{{ route('home') }}" class="hover:text-indigo-800">Início</a>
                    <span class="mx-2 text-gray-300">/</span>
                    <span class="text-gray-400">Análise de Produtos</span>
                </nav>

                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                    <div>
                        <span class="bg-indigo-50 text-indigo-600 px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest">{{ $product['category'] }}</span>
                        <h1 class="text-4xl md:text-5xl font-black text-gray-900 mt-4 uppercase">{{ $product['name'] }}</h1>
                    </div>
                    <div class="flex items-center gap-2 bg-yellow-50 px-4 py-2 rounded-2xl">
                        <span class="text-yellow-600 font-black text-xl">{{ $product['rating'] }}</span>
                        <span class="text-yellow-400">★★★★★</span>
                    </div>
                </div>
            </header>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                <div class="lg:col-span-2">
                    <div class="aspect-square md:aspect-video bg-gray-50 rounded-[3rem] border border-gray-100 mb-10 flex items-center justify-center">
                        <span class="text-gray-300 font-black uppercase tracking-widest">Foto do Produto</span>
                    </div>

                    <div class="prose prose-lg prose-indigo text-gray-600 font-medium leading-relaxed">
                        <h3 class="text-2xl font-black text-gray-900 mb-4">Avaliação Geral</h3>
                        <p>Nesta análise, testamos o desempenho do {{ $product['name'] }} em um ambiente de banho e tosa com fluxo real de 15 cães por dia. O grande destaque fica para o controle térmico...</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 my-10">
                            <div class="bg-green-50 p-6 rounded-[2rem]">
                                <p class="font-black text-green-700 uppercase text-xs mb-4">Pontos Fortes</p>
                                <ul class="space-y-2 list-none p-0 text-sm text-green-800">
                                    @foreach($product['pros'] as $pro)
                                        <li class="flex items-center gap-2">✓ {{ $pro }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="bg-red-50 p-6 rounded-[2rem]">
                                <p class="font-black text-red-700 uppercase text-xs mb-4">Pontos Fracos</p>
                                <ul class="space-y-2 list-none p-0 text-sm text-red-800">
                                    @foreach($product['cons'] as $con)
                                        <li class="flex items-center gap-2">✕ {{ $con }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <div class="sticky top-32 space-y-6">
                        <div class="bg-gray-50 rounded-[2.5rem] p-8 border border-gray-100">
                            <h4 class="font-black text-gray-900 uppercase text-xs mb-6 tracking-widest">Ficha Técnica</h4>
                            <div class="space-y-4">
                                @foreach($product['specs'] as $label => $value)
                                    <div class="flex justify-between border-b border-gray-200 pb-2">
                                        <span class="text-xs text-gray-400 font-bold uppercase">{{ $label }}</span>
                                        <span class="text-xs text-gray-900 font-black">{{ $value }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="bg-indigo-900 rounded-[2.5rem] p-8 text-white shadow-xl shadow-indigo-100">
                            <h4 class="font-black uppercase text-xs mb-4 tracking-widest text-indigo-300">Veredito Pet Business</h4>
                            <p class="text-sm font-medium leading-relaxed mb-6">{{ $product['verdict'] }}</p>
                            <a href="{{ route('suppliers.index') }}" class="block w-full py-4 bg-white text-indigo-900 text-center rounded-2xl font-black uppercase text-[10px] tracking-widest hover:bg-indigo-50 transition">
                                Ver Fornecedores deste Produto
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
