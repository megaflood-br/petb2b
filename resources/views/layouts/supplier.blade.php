<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Painel do Fornecedor - Revista Negócios Pet</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,800,900&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900">
    <div class="min-h-screen flex flex-col md:flex-row">

        {{-- Menu Lateral Administrativo do Fornecedor --}}
        <aside class="w-full md:w-64 bg-gray-950 text-white shrink-0 border-r border-gray-900 shadow-xl">
            <div class="p-8 pb-6">
                <a href="{{ route('home') }}" class="font-black text-xl tracking-tight uppercase italic block">
                    <span class="text-brand-500">Revista</span> Negócios Pet
                </a>
                <p class="text-[8px] font-black uppercase text-gray-500 tracking-[0.2em] mt-1">Ambiente B2B Corporativo</p>
            </div>

            <nav class="px-4 space-y-1.5 pb-10">

                {{-- Dashboard Link --}}
                <a href="{{ route('supplier.dashboard') }}"
                   class="block p-4 rounded-2xl font-bold text-xs uppercase tracking-wider transition {{ Route::currentRouteName() == 'supplier.dashboard' ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/20' : 'text-gray-400 hover:bg-gray-900 hover:text-white' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        <span>Dashboard</span>
                    </div>
                </a>

                {{-- Classificados Link --}}
                <a href="{{ route('supplier.classifieds') }}"
                   class="block p-4 rounded-2xl font-bold text-xs uppercase tracking-wider transition {{ Route::currentRouteName() == 'supplier.classifieds' ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/20' : 'text-gray-400 hover:bg-gray-900 hover:text-white' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                        </svg>
                        <span>Classificados</span>
                    </div>
                </a>

                {{-- CORREÇÃO/ADICIONADO: Link de Gestão de Anúncios e Créditos Rotativos --}}
                <a href="{{ route('supplier.ads') }}"
                   class="block p-4 rounded-2xl font-bold text-xs uppercase tracking-wider transition {{ Route::currentRouteName() == 'supplier.ads' ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/20' : 'text-gray-400 hover:bg-gray-900 hover:text-white' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.003 9.003 0 1020.945 13H11V3.055z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
                        </svg>
                        <span>Ads & Créditos</span>
                    </div>
                </a>

                {{-- Vagas de Emprego --}}
                <a href="{{ route('supplier.jobs') }}"
                   class="block p-4 rounded-2xl font-bold text-xs uppercase tracking-wider transition {{ Route::currentRouteName() == 'supplier.jobs' ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/20' : 'text-gray-400 hover:bg-gray-900 hover:text-white' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span>Vagas</span>
                    </div>
                </a>

                {{-- Matérias Patrocinadas --}}
                <a href="{{ route('supplier.sponsored') }}"
                   class="block p-4 rounded-2xl font-bold text-xs uppercase tracking-wider transition {{ Route::currentRouteName() == 'supplier.sponsored' ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/20' : 'text-gray-400 hover:bg-gray-900 hover:text-white' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                        </svg>
                        <span>Matérias</span>
                    </div>
                </a>

                {{-- Mensagens / Leads Link --}}
                <a href="{{ route('supplier.messages') }}"
                   class="block p-4 rounded-2xl font-bold text-xs uppercase tracking-wider transition {{ Route::currentRouteName() == 'supplier.messages' ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/20' : 'text-gray-400 hover:bg-gray-900 hover:text-white' }}">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                            </svg>
                            <span>Mensagens</span>
                        </div>

                        {{-- Contador de Mensagens Não Lidas --}}
                        @php
                            $unreadCount = \App\Models\Lead::whereHas('supplier', function($q) {
                                $q->where('user_id', auth()->id());
                            })->where('is_read', false)->count();
                        @endphp

                        @if($unreadCount > 0)
                            <span class="bg-brand-500 text-white text-[9px] px-2 py-0.5 rounded-full font-black shadow-sm">
                                {{ $unreadCount }}
                            </span>
                        @endif
                    </div>
                </a>

                @php
                    $mySupplier = \App\Models\Supplier::where('user_id', auth()->id())->first();
                @endphp

                {{-- Link externo para visualizar perfil público da marca --}}
                @if($mySupplier)
                    <div class="border-t border-gray-900 my-4"></div>

                    <a href="{{ route('suppliers.show', $mySupplier->slug) }}"
                       target="_blank"
                       class="flex items-center gap-3 px-4 py-3 text-[10px] font-black uppercase tracking-widest text-brand-400 hover:bg-gray-900 transition-all rounded-xl">
                        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 512 512">
                            <path d="M448 96l0 256-384 0 0-256 384 0zM64 32C28.7 32 0 60.7 0 96L0 352c0 35.3 28.7 64 64 64l144 0-16 48-72 0c-13.3 0-24 10.7-24 24s10.7 24 24 24l272 0c13.3 0 24-10.7 24-24s-10.7-24-24-24l-72 0-16-48 144 0c35.3 0 64-28.7 64-64l0-256c0-35.3-28.7-64-64-64L64 32z"/>
                        </svg>
                        <span>Ver Perfil Público</span>
                    </a>
                @endif

                {{-- Logout Seguro --}}
                <div class="pt-6">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                onclick="event.preventDefault(); this.closest('form').submit();"
                                class="w-full text-left px-4 py-3.5 rounded-2xl hover:bg-red-950/30 text-red-400 font-bold text-xs uppercase tracking-wider transition flex items-center gap-3">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            <span>Sair da Conta</span>
                        </button>
                    </form>
                </div>
            </nav>
        </aside>

        {{-- Conteúdo Injetado do Painel --}}
        <main class="flex-1 p-6 md:p-12 overflow-y-auto">
            @if(isset($slot))
                {{ $slot }}
            @else
                @yield('content')
            @endif
        </main>
    </div>

    @livewireScripts
</body>
</html>
