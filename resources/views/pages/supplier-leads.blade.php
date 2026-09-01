@extends('layouts.supplier')

@section('content')
    {{-- Cabeçalho da Página --}}
    <div class="mb-10">
        <h1 class="text-3xl font-black text-gray-900 uppercase italic tracking-tight">
            Minhas <span class="text-brand-500">Mensagens</span>
        </h1>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">
            Acompanhe e responda as mensagens de contatos e leads recebidos pelo portal
        </p>
    </div>

    {{-- Componente Livewire que gerencia as mensagens/leads recebidos --}}
    <div class="bg-white p-10 rounded-[2.5rem] border border-gray-100 shadow-sm">
        @livewire('supplier-leads')
    </div>
@endsection
