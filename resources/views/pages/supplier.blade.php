@extends('layouts.supplier')

@section('content')
    {{-- Cabeçalho do Painel --}}
    <div class="mb-10">
        <h1 class="text-3xl font-black text-gray-900 uppercase italic tracking-tight">
            Painel do <span class="text-brand-500">Fornecedor</span>
        </h1>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">
            Gerencie as informações da sua empresa e acompanhe seus leads comerciais
        </p>
    </div>

    {{-- Cards de Estatísticas Comerciais --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 text-center flex flex-col justify-center">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Visualizações do Guia</p>
            <p class="text-3xl font-black text-gray-900">{{ $stats['views'] }}</p>
        </div>

        <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 text-center relative flex flex-col justify-center min-h-[100px]">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Contatos Recebidos (Leads)</p>
            <p class="text-3xl font-black text-gray-900">{{ $stats['leads'] }}</p>
            @if($stats['unread_leads'] > 0)
                <span class="absolute top-4 right-4 bg-brand-500 text-white text-[8px] font-black px-2.5 py-1 rounded-full uppercase tracking-widest shadow-sm">
                    {{ $stats['unread_leads'] }} novos
                </span>
            @endif
        </div>

        <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 border-l-4 border-l-green-500 text-center flex flex-col justify-center">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Status da Empresa</p>
            <p class="text-sm font-black text-green-600 uppercase tracking-wider">✓ Ativa no Portal</p>
        </div>
    </div>

    {{-- Lógica de Verificação de Reivindicação de Empresa --}}
    @php
        $pendingClaim = \App\Models\CompanyClaim::where('user_id', auth()->id())
            ->where('status', 'pending')
            ->first();
    @endphp

    @if($pendingClaim)
        <div class="mb-8 p-6 bg-amber-500 rounded-[2rem] shadow-md flex items-center gap-6 border-b-4 border-amber-600">
            {{-- Ícone SVG de Alerta Nativo à prova de falhas --}}
            <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center text-white p-2 flex-shrink-0">
                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" class="w-full h-full">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div class="text-white">
                <h4 class="font-black uppercase text-sm leading-none mb-1">Análise de Vínculo em Andamento</h4>
                <p class="text-[10px] font-bold uppercase tracking-widest text-amber-500/10 text-white opacity-90 leading-relaxed">
                    Não é necessário cadastrar a sua empresa novamente. Estamos validando os documentos de propriedade para liberar o controle completo do seu perfil.
                </p>
            </div>
        </div>
    @endif

    {{-- Bloco do Formulário do Fornecedor (Livewire) --}}
    <div class="bg-white p-10 rounded-[2.5rem] border border-gray-100 shadow-sm">
        <div class="mb-8 border-b pb-4">
            <h2 class="text-xl font-black text-gray-900 uppercase italic tracking-tight">Editar Perfil Corporativo</h2>
            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mt-0.5">Atualize a ficha técnica, catálogo e dados B2B da sua marca</p>
        </div>

        @livewire('supplier.edit-profile')
    </div>
@endsection
