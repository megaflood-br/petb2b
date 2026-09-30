<div class="space-y-8 text-xs font-bold">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 border-b pb-4">
        <div>
            @if($current)
                <p class="text-[9px] font-black text-brand-500 uppercase tracking-[0.2em] mb-1">
                    <a href="{{ route('admin.reports') }}" class="hover:underline">Relatórios</a>
                    <span class="text-gray-300"> / </span>
                    {{ $current['label'] }}
                </p>
                <h1 class="text-2xl font-black text-gray-900 uppercase tracking-tight italic">Relatório de {{ $current['label'] }}</h1>
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mt-0.5">{{ $current['description'] }}</p>
            @else
                <h1 class="text-2xl font-black text-gray-900 uppercase tracking-tight italic">Relatórios</h1>
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mt-0.5">Extraia dados do portal — comece pelos anúncios; outros tipos entram aqui depois</p>
            @endif
        </div>
    </div>

    @if(! $current)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($catalog as $report)
                @if($report['enabled'])
                    <a href="{{ route('admin.reports.show', $report['key']) }}" class="group bg-white p-7 rounded-[2rem] border border-gray-100 shadow-sm hover:shadow-xl hover:border-brand-200 transition duration-300">
                        <div class="flex justify-between items-start mb-5">
                            <div class="p-3 bg-brand-50 text-brand-600 rounded-2xl group-hover:bg-brand-500 group-hover:text-white transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
                            </div>
                            <span class="text-[8px] font-black uppercase tracking-widest text-green-600 bg-green-50 border border-green-100 px-2 py-0.5 rounded-full">Disponível</span>
                        </div>
                        <h2 class="text-sm font-black text-gray-900 uppercase italic tracking-tight">{{ $report['label'] }}</h2>
                        <p class="text-[10px] text-gray-500 font-medium normal-case mt-2 leading-relaxed">{{ $report['description'] }}</p>
                        <p class="text-[9px] font-black uppercase tracking-widest text-brand-500 mt-5">Abrir relatório →</p>
                    </a>
                @else
                    <div class="bg-white p-7 rounded-[2rem] border border-dashed border-gray-200 opacity-70">
                        <div class="flex justify-between items-start mb-5">
                            <div class="p-3 bg-gray-50 text-gray-400 rounded-2xl">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z"/></svg>
                            </div>
                            <span class="text-[8px] font-black uppercase tracking-widest text-gray-400 bg-gray-50 border border-gray-100 px-2 py-0.5 rounded-full">Em breve</span>
                        </div>
                        <h2 class="text-sm font-black text-gray-700 uppercase italic tracking-tight">{{ $report['label'] }}</h2>
                        <p class="text-[10px] text-gray-400 font-medium normal-case mt-2 leading-relaxed">{{ $report['description'] }}</p>
                    </div>
                @endif
            @endforeach
        </div>
    @elseif($type === 'anuncios')
        @include('livewire.admin.reports.ads')
    @endif
</div>
