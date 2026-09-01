@extends('layouts.supplier')

@section('content')
    {{-- Cabeçalho da Página --}}
    <div class="mb-10">
        <h1 class="text-3xl font-black text-gray-900 uppercase italic tracking-tight">
            Meus <span class="text-brand-500">Classificados</span>
        </h1>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">
            Gerencie seus produtos, equipamentos e acessórios anunciados no portal
        </p>
    </div>

    {{-- Componente Livewire que gerencia a lógica de cadastro e listagem --}}
    <div class="bg-white p-10 rounded-[2.5rem] border border-gray-100 shadow-sm">
        @livewire('supplier.manage-classifieds')
    </div>
@endsection
