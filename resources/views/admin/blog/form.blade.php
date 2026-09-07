@extends('layouts.admin')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-3xl font-black text-gray-900 uppercase italic">
                {{ $post->exists ? 'Editando' : 'Nova' }} <span class="text-brand-500">Postagem</span>
            </h1>
        </div>
        <a href="{{ route('admin.blog') }}" class="bg-gray-100 text-gray-700 px-6 py-3 rounded-2xl font-black uppercase text-xs tracking-widest hover:bg-gray-200 transition shrink-0 text-center">
            Voltar para Lista
        </a>
    </div>

    @if($errors->any())
        <div class="bg-red-50 p-4 rounded-2xl border border-red-100 text-red-600 text-[10px] font-black uppercase space-y-1">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="bg-white p-10 rounded-[2.5rem] border border-gray-100 shadow-sm">
        <form method="POST" action="{{ $post->exists ? route('admin.blog.update', $post) : route('admin.blog.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @if($post->exists)
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-2">
                    <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block tracking-widest">Título da Notícia</label>
                    <input type="text" name="title" value="{{ old('title', $post->title) }}" class="w-full bg-gray-50 border-none rounded-xl p-4 font-bold text-gray-900 focus:ring-2 focus:ring-brand-500">
                </div>

                <div class="md:col-span-1">
                    <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block tracking-widest">Categorias Vinculadas (Selecione 1 ou mais)</label>
                    <div class="bg-gray-50 rounded-xl p-4 max-h-[160px] overflow-y-auto space-y-2 border border-gray-100 shadow-inner">
                        @foreach($categories as $category)
                            <label class="flex items-center gap-3 cursor-pointer p-1 rounded-lg hover:bg-white transition-colors">
                                <input type="checkbox" name="selected_categories[]" value="{{ $category->id }}" @checked(in_array((string) $category->id, $selected, true)) class="w-4 h-4 text-brand-500 rounded border-gray-300 focus:ring-brand-500">
                                <span class="text-xs font-bold text-gray-700 uppercase tracking-tight">{{ $category->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block tracking-widest">Conteúdo HTML</label>
                <input id="content_input" type="hidden" name="content" value="{{ old('content', $post->content) }}">
                <trix-editor input="content_input" class="trix-content bg-white border border-gray-100 rounded-2xl font-medium text-gray-900 min-h-[400px] p-6 outline-none focus:border-brand-500 transition-all"></trix-editor>
            </div>

            <div class="bg-brand-50/30 p-8 rounded-[2rem] border border-brand-100/50 space-y-4">
                <h3 class="text-[10px] font-black uppercase text-brand-600 tracking-widest">Configurações de SEO (Google)</h3>
                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">Meta Description (Máx. 160 caracteres)</label>
                    <textarea name="meta_description" rows="2" class="w-full bg-white border-none rounded-xl p-4 font-bold text-gray-900 focus:ring-2 focus:ring-brand-500 shadow-sm">{{ old('meta_description', $post->meta_description) }}</textarea>
                </div>
                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">Keywords (Separe por vírgula)</label>
                    <input type="text" name="meta_keywords" value="{{ old('meta_keywords', $post->meta_keywords) }}" class="w-full bg-white border-none rounded-xl p-4 font-bold text-gray-900 focus:ring-2 focus:ring-brand-500 shadow-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block tracking-widest">Imagem de Capa</label>
                    <input type="file" name="image" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-brand-50 file:text-brand-700">
                </div>
                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block tracking-widest">Data de Publicação</label>
                    <input type="datetime-local" name="created_at" value="{{ old('created_at', $post->created_at?->format('Y-m-d\TH:i')) }}" class="w-full bg-gray-50 border-none rounded-xl p-3 font-bold text-gray-900 focus:ring-2 focus:ring-brand-500">
                </div>
            </div>

            <label class="flex items-center gap-3 bg-brand-50/50 p-4 rounded-2xl border border-brand-100 cursor-pointer">
                <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $post->is_featured)) class="w-5 h-5 text-brand-600 rounded-lg border-gray-300 focus:ring-brand-500">
                <span class="text-[10px] font-black uppercase text-brand-900 tracking-widest">Destacar esta notícia no topo do blog</span>
            </label>

            <label class="flex items-center gap-3 bg-amber-50/60 p-4 rounded-2xl border border-amber-100 cursor-pointer">
                <input type="checkbox" name="is_premium" value="1" @checked(old('is_premium', $post->is_premium)) class="w-5 h-5 text-amber-600 rounded-lg border-gray-300 focus:ring-amber-500">
                <span class="text-[10px] font-black uppercase text-amber-900 tracking-widest">Conteúdo exclusivo (somente usuários cadastrados)</span>
            </label>

            <button type="submit" class="w-full bg-gray-900 text-white p-5 rounded-2xl font-black uppercase tracking-widest hover:bg-brand-600 transition shadow-lg">
                Salvar Postagem
            </button>
        </form>
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
<script src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>
<style>
    trix-toolbar .trix-button-row { border-bottom: 1px solid #f3f4f6; padding-bottom: 5px; margin-bottom: 10px; }
    trix-editor { border: 1px solid #f3f4f6 !important; }
</style>
@endsection
