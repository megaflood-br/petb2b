@extends('layouts.admin')

@section('content')
    <div class="mb-10">
        <h1 class="text-3xl font-black text-gray-900 uppercase tracking-tight italic">
            Gestão de <span class="text-indigo-600">Fornecedores</span>
        </h1>
        <p class="text-gray-500 font-medium mt-2">Valide as empresas que solicitam cadastro no portal.</p>
    </div>

    @livewire('admin.approve-suppliers')
@endsection
