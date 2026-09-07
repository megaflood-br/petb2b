<div class="space-y-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-3xl font-black text-gray-900 uppercase italic">Gestão do <span class="text-brand-500">Blog</span></h1>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 w-full md:w-auto flex-1 md:justify-end">
            <form method="GET" action="{{ route('admin.blog') }}" class="relative w-full sm:w-72">
                <input type="text" name="q" value="{{ $search }}" placeholder="Buscar por título ou conteúdo..."
                    class="w-full bg-white border border-gray-100 rounded-xl py-3 px-4 pl-10 text-xs font-bold text-gray-900 shadow-sm focus:ring-2 focus:ring-brand-500 transition-all outline-none">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </form>

            <a href="{{ route('admin.blog.create') }}" class="bg-brand-500 text-white px-6 py-3 rounded-2xl font-black uppercase text-xs tracking-widest hover:bg-brand-600 transition shadow-lg shadow-brand-100 shrink-0 text-center">
                Nova Postagem
            </a>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-50 p-4 rounded-2xl border border-green-100 text-green-600 text-[10px] font-black uppercase">
            {{ session('message') }}
        </div>
    @endif

    <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden text-xs">
        <table class="w-full text-left">
            <thead class="bg-gray-50/50">
                <tr>
                    <th class="py-4 px-6 font-black text-gray-400 uppercase tracking-widest">Título</th>
                    <th class="py-4 px-6 font-black text-gray-400 uppercase tracking-widest">Categorias Vinculadas</th>
                    <th class="py-4 px-6 font-black text-gray-400 uppercase tracking-widest">Data de Pub.</th>
                    <th class="py-4 px-6 font-black text-gray-400 uppercase tracking-widest text-right">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($posts as $post)
                    <tr class="hover:bg-gray-50/50 transition font-bold">
                        <td class="py-6 px-6 text-gray-900 uppercase max-w-[300px] truncate">{{ $post->title }}</td>
                        <td class="py-6 px-6">
                            <div class="flex flex-wrap gap-1.5">
                                @forelse($post->blogCategories as $cat)
                                    <span class="bg-brand-50 text-brand-600 px-2.5 py-1 rounded-full text-[9px] uppercase tracking-tight font-black">
                                        {{ $cat->name }}
                                    </span>
                                @empty
                                    <span class="bg-gray-100 text-gray-400 px-2.5 py-1 rounded-full text-[9px] uppercase tracking-tight">
                                        Sem Categoria
                                    </span>
                                @endforelse
                            </div>
                        </td>
                        <td class="py-6 px-6 text-gray-500 font-mono">
                            {{ $post->created_at ? $post->created_at->format('d/m/Y H:i') : '---' }}
                        </td>
                        <td class="py-6 px-6 text-right space-x-3 text-sm">
                            <a href="{{ route('admin.blog.edit', $post) }}" class="text-brand-600 uppercase font-black">Editar</a>
                            <form method="POST" action="{{ route('admin.blog.destroy', $post) }}" class="inline" onsubmit="return confirm('Tem certeza?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-400 uppercase font-black">Excluir</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-12 text-center text-gray-400 font-black uppercase tracking-widest">
                            Nenhuma postagem encontrada para esta busca.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $posts->links() }}</div>
</div>
