@extends('layouts.admin')

@section('content')
<div class="space-y-12">
    {{-- Cabeçalho Dinâmico --}}
    <div class="flex justify-between items-end">
        <div>
            <h1 class="text-4xl font-black text-gray-900 uppercase tracking-tighter italic">
                Resumo do <span class="text-brand-500">Portal</span>
            </h1>
            <p class="text-gray-500 font-medium mt-1 uppercase text-[10px] tracking-[0.2em]">Painel de Controle RNPet</p>
        </div>
        <div class="text-right hidden md:block">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Status do Servidor</p>
            <p class="text-xs font-bold text-green-500 uppercase italic">● Sistema Online na Hostinger</p>
        </div>
    </div>

    {{-- Grid Principal de Estatísticas --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

        {{-- Card: Empresas Pendentes --}}
        <a href="{{ route('admin.suppliers') }}" class="group bg-white p-6 rounded-[2.5rem] border border-gray-100 shadow-sm hover:shadow-xl transition duration-500">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-orange-50 rounded-2xl text-orange-500 group-hover:bg-orange-500 group-hover:text-white transition duration-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <span class="text-[9px] font-black uppercase text-orange-400 tracking-widest italic">Aprovação</span>
            </div>
            <h3 class="text-3xl font-black text-gray-900 leading-none tracking-tighter">{{ $stats['pending_suppliers'] }}</h3>
            <p class="text-[10px] font-bold text-gray-400 uppercase mt-2">Empresas Aguardando</p>
        </a>

        {{-- Card: Análises Técnicas --}}
        <a href="{{ route('admin.reviews') }}" class="group bg-white p-6 rounded-[2.5rem] border border-gray-100 shadow-sm hover:shadow-xl transition duration-500">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-indigo-50 rounded-2xl text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition duration-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                </div>
                <span class="text-[9px] font-black uppercase text-indigo-400 tracking-widest italic">Reviews</span>
            </div>
            <h3 class="text-3xl font-black text-gray-900 leading-none tracking-tighter">{{ $stats['total_reviews'] }}</h3>
            <p class="text-[10px] font-bold text-gray-400 uppercase mt-2">Produtos Analisados</p>
        </a>

        {{-- Card: Blog (Artigos) --}}
        <a href="{{ route('admin.blog') }}" class="group bg-white p-6 rounded-[2.5rem] border border-gray-100 shadow-sm hover:shadow-xl transition duration-500">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-emerald-50 rounded-2xl text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition duration-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                </div>
                <span class="text-[9px] font-black uppercase text-emerald-400 tracking-widest italic">Conteúdo</span>
            </div>
            <h3 class="text-3xl font-black text-gray-900 leading-none tracking-tighter">{{ $stats['total_posts'] }}</h3>
            <p class="text-[10px] font-bold text-gray-400 uppercase mt-2">Matérias Publicadas</p>
        </a>

        {{-- Card: Mensagens Não Lidas --}}
        <a href="{{ route('admin.messages') }}" class="group bg-brand-500 p-6 rounded-[2.5rem] shadow-xl shadow-indigo-100 hover:scale-[1.02] transition duration-500 relative overflow-hidden">
            <div class="relative z-10">
                <div class="flex justify-between items-start mb-4">
                    <div class="p-3 bg-white/20 rounded-2xl text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                    </div>
                    <span class="text-[9px] font-black uppercase text-white/60 tracking-widest italic">Suporte</span>
                </div>
                <h3 class="text-3xl font-black text-white leading-none tracking-tighter">{{ $stats['unread_messages'] }}</h3>
                <p class="text-[10px] font-bold text-white/60 uppercase mt-2">Mensagens Novas</p>
            </div>
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
        </a>
    </div>

    {{-- Segunda Linha: Revistas, Eventos e Leads --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- Estante de Revistas --}}
        <div class="lg:col-span-2 bg-white p-8 rounded-[3rem] border border-gray-100 shadow-sm">
            <div class="flex justify-between items-center mb-8">
                <h4 class="text-sm font-black text-gray-900 uppercase tracking-tight italic">Últimas <span class="text-brand-500">Revistas</span></h4>
                <a href="{{ route('admin.magazines') }}" class="text-[9px] font-black text-brand-500 uppercase tracking-widest">Ver Todas</a>
            </div>
            <div class="grid grid-cols-3 gap-6">
                @foreach($stats['latest_magazines'] as $mag)
                    <div class="text-center group cursor-pointer">
                        <div class="aspect-[3/4] bg-gray-50 rounded-2xl mb-3 overflow-hidden border border-gray-100 group-hover:shadow-lg transition">
                            <img src="{{ asset('storage/' . $mag->cover_path) }}" class="w-full h-full object-cover">
                        </div>
                        <p class="text-[10px] font-black uppercase text-gray-900 line-clamp-1 leading-none">{{ $mag->issue_period }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Eventos e Agenda --}}
        <div class="bg-gray-900 p-8 rounded-[3rem] shadow-2xl relative overflow-hidden">
            <div class="relative z-10 h-full flex flex-col justify-between">
                <div>
                    <h4 class="text-xs font-black text-brand-500 uppercase tracking-[0.2em] mb-6 italic">Próximos Eventos</h4>
                    <div class="space-y-4">
                        @foreach($stats['upcoming_events'] as $event)
                            <div class="flex items-center gap-4">
                                <div class="bg-brand-500 h-2 w-2 rounded-full shadow-[0_0_10px_rgba(79,70,229,0.8)]"></div>
                                <p class="text-[11px] font-bold text-white uppercase leading-none truncate">{{ $event->title }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="mt-8 pt-8 border-t border-white/5">
                    <h3 class="text-5xl font-black text-white leading-none tracking-tighter italic">{{ $stats['total_leads'] }}</h3>
                    <p class="text-[10px] font-bold text-gray-500 uppercase mt-2 tracking-widest leading-none">Total de Leads Gerados</p>
                </div>
            </div>
            <div class="absolute top-0 right-0 w-40 h-40 bg-indigo-500/10 rounded-full blur-3xl"></div>
        </div>
    </div>
</div>
@endsection
