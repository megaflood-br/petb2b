@extends('layouts.admin')

@section('content')
    <div class="mb-10">
        <h1 class="text-3xl font-black text-gray-900 uppercase tracking-tight">
            Painel <span class="text-indigo-600">Eventos</span>
        </h1>
        <p class="text-gray-500 font-medium mt-2">
            Área restrita para cadastro e gerenciamento das feiras e congressos pet.
        </p>
    </div>

    {{-- Chamada do componente Livewire que criamos no passo anterior --}}
    @livewire('admin.manage-events')
@endsection
