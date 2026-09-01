<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- SIMULAÇÃO YOAST PERFEITA --}}
        {!! SEO::generate() !!}

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,800,900&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased bg-gray-50 text-gray-900 pb-16 sm:pb-0" x-data="{ mobileMenuOpen: false }">
        {{-- Adicionado um padding-bottom dinâmico no body acima apenas no mobile para o banner fixo não cobrir o rodapé real do site --}}

        {{-- OVERLAY MOBILE --}}
        <div x-show="mobileMenuOpen"
             x-transition:opacity ease-linear duration-300
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="mobileMenuOpen = false"
             class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-[60] sm:hidden"
             style="display: none;">
        </div>

        {{-- MENU LATERAL DESLIZANTE MOBILE --}}
        <div x-show="mobileMenuOpen"
             x-transition:enter="transition ease-in-out duration-300 transform"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in-out duration-300 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
             class="fixed inset-y-0 left-0 w-full max-w-xs bg-white shadow-2xl z-[70] sm:hidden flex flex-col"
             style="display: none;">

            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <img src="https://rnpet.com.br/wp-content/uploads/2025/11/logo-rnpet-2016.png" alt="Logo" class="h-8 w-auto">
                <button @click="mobileMenuOpen = false" class="text-gray-400">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="flex-1 px-6 py-8 overflow-y-auto">
                <nav class="space-y-6">
                    <a href="{{ route('home') }}" class="block text-lg font-black uppercase italic {{ request()->routeIs('home') ? 'text-brand-500' : 'text-gray-900' }}">Home</a>
                    <a href="{{ route('suppliers.index') }}" class="block text-lg font-black uppercase italic text-gray-900 hover:text-brand-500 transition">Guia</a>

                    {{-- SUB-MENU MOBILE --}}
                    <div x-data="{ subOpen: false }" class="space-y-2">
                        <button @click="subOpen = !subOpen" class="w-full flex items-center justify-between text-lg font-black uppercase italic text-gray-900 outline-none">
                            <span>Notícias</span>
                            <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': subOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <div x-show="subOpen" x-collapse class="pl-4 border-l-2 border-brand-100 space-y-3 pt-2 max-h-[300px] overflow-y-auto" style="display: none;">
                            <a href="{{ route('blog.index') }}" class="block text-xs font-black text-brand-500 uppercase">✨ Ver Todos os Artigos</a>
                            @foreach($blogCategories as $cat)
                                <a href="{{ route('blog.category', $cat->slug) }}" class="block text-xs font-bold text-gray-600 uppercase hover:text-brand-500">
                                    📖 {{ $cat->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <a href="{{ route('classifieds.index') }}" class="block text-lg font-black uppercase italic text-gray-900 hover:text-brand-500 transition">Classificados</a>
                    <a href="{{ route('jobs.index') }}" class="block text-lg font-black uppercase italic text-gray-900 hover:text-brand-500 transition">Vagas</a>
                    <a href="{{ route('breeds.index') }}" class="block text-lg font-black uppercase italic text-gray-900 hover:text-brand-500 transition">Raças</a>
                    <a href="{{ route('reviews.index') }}" class="block text-lg font-black uppercase italic text-gray-900 hover:text-brand-500 transition">Análises</a>
                    <a href="{{ route('magazines.index') }}" class="block text-lg font-black uppercase italic text-gray-900 hover:text-brand-500 transition">Revistas</a>
                    <a href="{{ route('events.index') }}" class="block text-lg font-black uppercase italic text-gray-900 hover:text-brand-500 transition">Feiras Pet</a>
                </nav>
            </div>
        </div>

        {{-- CORPO FIXO DO SITE --}}
        <div class="min-h-screen flex flex-col">

            {{-- CABEÇALHO / NAVBAR DESKTOP --}}
            <nav class="bg-white border-b border-gray-100 shadow-sm sticky top-0 z-50">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-20">
                        <div class="flex items-center">
                            <a href="{{ route('home') }}" class="flex-shrink-0 flex items-center transition hover:opacity-80">
                                <img src="https://rnpet.com.br/wp-content/uploads/2025/11/logo-rnpet-2016.png" alt="Revista Negócios Pet" class="h-12 w-auto object-contain">
                            </a>

                            {{-- MENUS DESKTOP --}}
                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex items-center">
                                <a href="{{ route('suppliers.index') }}" class="inline-flex items-center h-20 px-1 border-b-2 {{ request()->routeIs('suppliers.*') ? 'border-brand-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-bold transition duration-150 ease-in-out">Guia</a>

                                {{-- MEGA MENU NOTÍCIAS REESTRUTURADO --}}
                                <div x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" class="relative inline-block h-20">
                                    <button type="button" class="inline-flex items-center h-20 px-1 border-b-2 gap-1 {{ request()->routeIs('blog.*') ? 'border-brand-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-bold transition duration-150 ease-in-out focus:outline-none">
                                        <span>Notícias</span>
                                        <svg class="w-3 h-3 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>

                                    {{-- PAINEL DO MEGA MENU DESKTOP --}}
                                    <div x-show="open"
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 translate-y-2"
                                         x-transition:enter-end="opacity-100 translate-y-0"
                                         x-transition:leave="transition ease-in duration-150"
                                         x-transition:leave-start="opacity-100 translate-y-0"
                                         x-transition:leave-end="opacity-0 translate-y-2"
                                         class="absolute left-0 lg:-left-36 mt-0 w-[720px] bg-white rounded-[2.5rem] border border-gray-100 shadow-2xl p-6 z-50"
                                         style="display: none;">

                                        <div class="flex flex-col gap-3">
                                            <div class="flex justify-between items-center border-b border-gray-50 pb-2 mb-1">
                                                <p class="text-[10px] font-black uppercase text-brand-500 tracking-widest">Categorias Editoriais</p>
                                                <a href="{{ route('blog.index') }}" class="text-[11px] font-black text-gray-900 uppercase hover:text-brand-500 transition">✨ Ver Todos os Artigos</a>
                                            </div>

                                            <div class="grid grid-cols-3 gap-x-6 gap-y-2 max-h-[340px] overflow-y-auto pr-1 custom-mega-scrollbar">
                                                @foreach($blogCategories as $cat)
                                                    <a href="{{ route('blog.category', $cat->slug) }}"
                                                       class="text-[11px] font-bold text-gray-600 uppercase hover:text-brand-500 hover:bg-brand-50/60 px-2 py-1.5 rounded-lg transition flex items-center gap-1.5 truncate"
                                                       title="{{ $cat->name }}">
                                                        <span class="text-brand-400 font-mono text-[10px]">📖</span>
                                                        {{ $cat->name }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <a href="{{ route('classifieds.index') }}" class="inline-flex items-center h-20 px-1 border-b-2 {{ request()->routeIs('classifieds.*') ? 'border-brand-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-bold transition duration-150 ease-in-out">Classificados</a>
                                <a href="{{ route('jobs.index') }}" class="inline-flex items-center h-20 px-1 border-b-2 {{ request()->routeIs('jobs.*') ? 'border-brand-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-bold transition duration-150 ease-in-out">Vagas</a>
                                <a href="{{ route('reviews.index') }}" class="inline-flex items-center h-20 px-1 border-b-2 {{ request()->routeIs('reviews.*') ? 'border-brand-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-bold transition duration-150 ease-in-out">Análises</a>
                                <a href="{{ route('magazines.index') }}" class="inline-flex items-center h-20 px-1 border-b-2 {{ request()->routeIs('magazines.*') ? 'border-brand-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-bold transition duration-150 ease-in-out">Revistas</a>
                                <a href="{{ route('kennels.index') }}" class="inline-flex items-center h-20 px-1 border-b-2 {{ request()->routeIs('kennels.*') ? 'border-brand-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-bold transition duration-150 ease-in-out">Canis</a>
                                <a href="{{ route('breeds.index') }}" class="inline-flex items-center h-20 px-1 border-b-2 {{ request()->routeIs('breeds.*') ? 'border-brand-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-bold transition duration-150 ease-in-out">Raças</a>
                                <a href="{{ route('events.index') }}" class="inline-flex items-center h-20 px-1 border-b-2 {{ request()->routeIs('events.*') ? 'border-brand-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-bold transition duration-150 ease-in-out">Feiras Pet</a>
                            </div>
                        </div>

                        {{-- BOTÕES DE AUTENTICAÇÃO --}}
                        <div class="flex items-center">
                            <div class="hidden sm:flex sm:items-center sm:ml-6">
                                @auth
                                    <div class="flex items-center space-x-4">
                                        <a href="{{ route('dashboard') }}" class="text-sm text-gray-700 hover:text-brand-500 font-bold transition">Dashboard</a>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-bold transition">Sair</button>
                                        </form>
                                    </div>
                                @else
                                    <div class="flex items-center space-x-4">
                                        <a href="{{ route('login') }}" class="text-sm text-gray-700 hover:text-brand-500 font-bold transition">Entrar</a>
                                        <a href="{{ route('register.select') }}" class="bg-brand-500 text-white px-6 py-3 rounded-xl font-black uppercase text-xs tracking-widest hover:bg-brand-600 transition shadow-sm">
                                            Anuncie ou Cadastre-se
                                        </a>
                                    </div>
                                @endauth
                            </div>

                            <div class="flex items-center sm:hidden">
                                <button @click="mobileMenuOpen = true" class="inline-flex items-center justify-center p-2 rounded-xl text-gray-400 hover:bg-gray-100 transition">
                                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            @isset($header)
                <header class="bg-white shadow-sm border-b border-gray-100">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="flex-grow">
                {{ $slot }}
            </main>

            @include('layouts.footer')
        </div>

        {{-- ================================================================= --}}
        {{-- NOVO: BLOCO DO BANNER INFERIOR FIXO EXCLUSIVO PARA CELULAR (STICKY ANCHOR) --}}
        {{-- ================================================================= --}}
        @php
            $stickyMobileAd = \App\Models\Advertisement::where('is_active', true)
                ->where('position', 'banner_mobile_footer')
                ->inRandomOrder()
                ->first();

            if ($stickyMobileAd) {
                $stickyMobileAd->trackImpression();
                $stickyMobileAd->refresh();
            }
        @endphp

        @if($stickyMobileAd)
            <div x-data="{ showStickyAd: true }"
                 x-show="showStickyAd"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="translate-y-full"
                 x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-y-0"
                 x-transition:leave-end="translate-y-full"
                 class="fixed bottom-0 left-0 right-0 z-[55] sm:hidden bg-white/95 backdrop-blur border-t border-gray-100 shadow-[0_-8px_30px_rgb(0,0,0,0.08)] p-2 flex flex-col items-center justify-center"
                 style="display: none;">

                {{-- Botão Discreto de Fechar X --}}
                <button @click="showStickyAd = false"
                        class="absolute -top-7 right-3 bg-white/90 backdrop-blur text-gray-500 hover:text-gray-800 border border-gray-100 rounded-t-xl px-3 py-1 text-[10px] font-black uppercase tracking-widest transition shadow-sm focus:outline-none">
                    ✕ Fechar
                </button>

                {{-- Espaço do Anúncio Responsivo Mobile --}}
                <div class="w-full max-w-[400px] aspect-[320/50] rounded-xl overflow-hidden bg-gray-50 border border-gray-100">
                    <a href="{{ route('ads.redirect', $stickyMobileAd->id) }}" target="_blank" rel="noopener noreferrer" class="block w-full h-full">
                        <img src="{{ asset('storage/' . $stickyMobileAd->image_path) }}"
                             alt="{{ $stickyMobileAd->title }}"
                             class="w-full h-full object-cover"
                             title="Patrocinado: {{ $stickyMobileAd->title }}">
                    </a>
                </div>
            </div>
        @endif

        @livewireScripts
    </body>
</html>

<style>
    .custom-mega-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-mega-scrollbar::-webkit-scrollbar-track { background: #f9fafb; border-radius: 10px; }
    .custom-mega-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }
    .custom-mega-scrollbar::-webkit-scrollbar-thumb:hover { background: #d1d5db; }
</style>
