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

            <livewire:blog-post-grid :category="$categorySlug" />

            <x-ad-space position="meio_blog" />

        </div>
    </div>
</x-app-layout>

<style>
    .custom-filter-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-filter-scrollbar::-webkit-scrollbar-track { background: #f9fafb; border-radius: 4px; }
    .custom-filter-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 4px; }
    .custom-filter-scrollbar::-webkit-scrollbar-thumb:hover { background: #d1d5db; }
</style>
