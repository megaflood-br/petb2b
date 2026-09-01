<div class="bg-gray-50 min-h-screen">

    {{-- Cabeçalho --}}
    <div class="bg-gray-950 text-white py-16">
        <div class="max-w-6xl mx-auto px-6">
            <p class="text-[10px] font-black uppercase text-brand-400 tracking-[0.3em] mb-2">Guia de Empregos</p>
            <h1 class="text-3xl md:text-5xl font-black uppercase italic tracking-tight">Vagas no Mercado <span class="text-brand-500">Pet</span></h1>
            <p class="mt-3 text-sm text-gray-400 font-medium max-w-2xl">Oportunidades de carreira publicadas pelas empresas do portal B2B.</p>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-6 py-12">

        {{-- Filtros --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-10">
            <input type="text" wire:model.live.debounce.400ms="search" placeholder="Buscar cargo ou palavra-chave..." class="bg-white border border-gray-100 rounded-xl p-4 text-sm focus:ring-2 focus:ring-brand-500">
            <select wire:model.live="type" class="bg-white border border-gray-100 rounded-xl p-4 text-sm focus:ring-2 focus:ring-brand-500">
                <option value="">Todos os tipos</option>
                @foreach($types as $t)
                    <option value="{{ $t }}">{{ $t }}</option>
                @endforeach
            </select>
            <select wire:model.live="state" class="bg-white border border-gray-100 rounded-xl p-4 text-sm focus:ring-2 focus:ring-brand-500">
                <option value="">Todos os estados</option>
                @foreach($states as $uf)
                    <option value="{{ $uf }}">{{ $uf }}</option>
                @endforeach
            </select>
        </div>

        {{-- Lista --}}
        <div class="space-y-4">
            @forelse($jobs as $job)
                <a href="{{ route('jobs.show', $job->slug) }}" class="block bg-white border border-gray-100 rounded-2xl p-6 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition group">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="text-lg font-black text-gray-900 uppercase group-hover:text-brand-500 transition truncate">{{ $job->title }}</h2>
                            <p class="text-xs text-gray-500 font-medium mt-1">
                                {{ $job->supplier->name ?? 'Empresa' }} · {{ $job->city ?: 'Local a combinar' }}{{ $job->state ? '/' . $job->state : '' }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="text-[9px] font-black uppercase tracking-wider bg-brand-50 text-brand-600 px-3 py-1.5 rounded-full">{{ $job->type }}</span>
                            @if($job->salary)
                                <span class="text-[9px] font-black uppercase tracking-wider bg-green-50 text-green-600 px-3 py-1.5 rounded-full">{{ $job->salary }}</span>
                            @endif
                        </div>
                    </div>
                </a>
            @empty
                <div class="py-20 text-center bg-white rounded-2xl border border-dashed border-gray-200">
                    <p class="text-gray-400 font-bold uppercase tracking-widest">Nenhuma vaga encontrada.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-10">
            {{ $jobs->links() }}
        </div>
    </div>
</div>
