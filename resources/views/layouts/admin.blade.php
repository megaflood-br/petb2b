<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Revista Negócios Pet</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-50 font-sans antialiased text-gray-900">
    @php
        $unreadContacts = \App\Models\ContactMessage::where('is_read', false)->count();
    @endphp

    <div class="flex min-h-screen">
        {{-- Sidebar Administrativa Master --}}
        <aside class="w-72 bg-gray-950 text-white flex flex-col shadow-2xl border-r border-gray-900 shrink-0">
            <div class="p-8">
                <h2 class="text-2xl font-black tracking-tighter uppercase italic">RN<span class="text-brand-500">Pet</span></h2>
                <p class="text-[9px] font-black text-gray-500 uppercase tracking-[0.2em] mt-1">Gestão Central do Portal</p>
            </div>

            <nav class="flex-1 px-4 space-y-1 overflow-y-auto">
                {{-- Resumo / Dashboard --}}
                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center gap-3 p-4 rounded-2xl text-xs font-black uppercase tracking-wider transition {{ request()->routeIs('admin.dashboard') ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/20' : 'text-gray-400 hover:bg-gray-900 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    <span>Resumo</span>
                </a>

                {{-- Auditar Campanhas de Ads --}}
                <a href="{{ route('admin.ads') }}"
                   class="flex items-center gap-3 p-4 rounded-2xl text-xs font-black uppercase tracking-wider transition {{ request()->routeIs('admin.ads') ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/20' : 'text-gray-400 hover:bg-gray-900 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.003 9.003 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                    <span>Auditar Anúncios</span>
                </a>



                {{-- Fornecedores --}}
                <a href="{{ route('admin.suppliers') }}"
                   class="flex items-center gap-3 p-4 rounded-2xl text-xs font-black uppercase tracking-wider transition {{ request()->routeIs('admin.suppliers') ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/20' : 'text-gray-400 hover:bg-gray-900 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    <span>Aprovar Empresas</span>
                </a>

                {{-- Reivindicações --}}
                <a href="{{ route('admin.claims') }}"
                   class="flex items-center gap-3 p-4 rounded-2xl text-xs font-black uppercase tracking-wider transition {{ request()->routeIs('admin.claims') ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/20' : 'text-gray-400 hover:bg-gray-900 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    <span>Reivindicações</span>
                </a>

                {{-- Canis --}}
                <a href="{{ route('admin.kennels') }}"
                   class="flex items-center gap-3 p-4 rounded-2xl text-xs font-black uppercase tracking-wider transition {{ request()->routeIs('admin.kennels') ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/20' : 'text-gray-400 hover:bg-gray-900 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Canis & Criadores</span>
                </a>

                {{-- Configurações --}}
                <a href="{{ route('admin.settings') }}"
                   class="flex items-center gap-3 p-4 rounded-2xl text-xs font-black uppercase tracking-wider transition {{ request()->routeIs('admin.settings') ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/20' : 'text-gray-400 hover:bg-gray-900 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span>Configurações</span>
                </a>

                {{-- Raças --}}
                <a href="{{ route('admin.breeds') }}"
                   class="flex items-center gap-3 p-4 rounded-2xl text-xs font-black uppercase tracking-wider transition {{ request()->routeIs('admin.breeds') ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/20' : 'text-gray-400 hover:bg-gray-900 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path></svg>
                    <span>Guia de Raças</span>
                </a>

                {{-- Eventos --}}
                <a href="{{ route('admin.events') }}"
                   class="flex items-center gap-3 p-4 rounded-2xl text-xs font-black uppercase tracking-wider transition {{ request()->routeIs('admin.events') ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/20' : 'text-gray-400 hover:bg-gray-900 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v12a2 2 0 002 2z"></path></svg>
                    <span>Eventos Agenda</span>
                </a>

                {{-- Análises --}}
                <a href="{{ route('admin.reviews') }}"
                   class="flex items-center gap-3 p-4 rounded-2xl text-xs font-black uppercase tracking-wider transition {{ request()->routeIs('admin.reviews') ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/20' : 'text-gray-400 hover:bg-gray-900 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    <span>Análises Técnicas</span>
                </a>

                {{-- Blog --}}
                <a href="{{ route('admin.blog') }}"
                   class="flex items-center gap-3 p-4 rounded-2xl text-xs font-black uppercase tracking-wider transition {{ request()->routeIs('admin.blog') ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/20' : 'text-gray-400 hover:bg-gray-900 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                    <span>Matérias Blog</span>
                </a>
                {{-- Categorias Blog --}}
                <a href="{{ route('admin.categories') }}"
                   class="flex items-center gap-3 p-4 rounded-2xl text-xs font-black uppercase tracking-wider transition {{ request()->routeIs('admin.categories') ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/20' : 'text-gray-400 hover:bg-gray-900 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                    <span>Categorias Blog</span>
                </a>
                {{-- Revistas --}}
                <a href="{{ route('admin.magazines') }}"
                   class="flex items-center gap-3 p-4 rounded-2xl text-xs font-black uppercase tracking-wider transition {{ request()->routeIs('admin.magazines') ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/20' : 'text-gray-400 hover:bg-gray-900 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    <span>Banca de Revistas</span>
                </a>

                {{-- Mensagens de Contato --}}
                <a href="{{ route('admin.messages') }}"
                   class="flex items-center justify-between p-4 rounded-2xl text-xs font-black uppercase tracking-wider transition {{ request()->routeIs('admin.messages') ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/20' : 'text-gray-400 hover:bg-gray-900 hover:text-white' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                        <span>Fale Conosco</span>
                    </div>
                    @if($unreadContacts > 0)
                        <span class="bg-brand-500 text-white text-[9px] font-black h-5 w-5 flex items-center justify-center rounded-full shadow-md">{{ $unreadContacts }}</span>
                    @endif
                </a>
            </nav>

            <div class="p-8 border-t border-gray-900">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-xs font-black uppercase text-gray-500 hover:text-white transition">Sair do Painel</button>
                </form>
            </div>
        </aside>

        {{-- Área de Conteúdo Injetado --}}
        <main class="flex-1 p-12 overflow-y-auto">
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
