@extends('layouts.admin')

@section('content')
    <div class="mb-10 flex justify-between items-end">
        <div>
            <h1 class="text-3xl font-black text-gray-900 uppercase tracking-tight italic">
                Análises de <span class="text-indigo-600">Produtos</span>
            </h1>
            <p class="text-gray-500 font-medium mt-2">
                Cadastre análises com título, categoria, foto e descrição.
            </p>
        </div>

        {{-- Link para visualizar como está ficando no site --}}
        <a href="{{ route('reviews.index') }}" target="_blank" class="text-[10px] font-black uppercase text-gray-400 hover:text-indigo-600 transition tracking-widest flex items-center gap-2">
            Ver Vitrine Pública
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
        </a>
    </div>

    {{-- Chamada do componente Livewire de gerenciamento --}}
    @livewire('admin.manage-reviews')
@endsection
