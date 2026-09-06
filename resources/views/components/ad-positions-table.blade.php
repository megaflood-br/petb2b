<div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden">
    <div class="px-6 py-5 border-b border-gray-100">
        <h3 class="text-[10px] font-black uppercase text-gray-900 tracking-widest">Medidas e Posições dos Anúncios</h3>
        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mt-1">Envie o criativo na medida recomendada de cada posição</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-950 text-white uppercase text-[9px] tracking-wider">
                    <th class="p-4 pl-6">Posição</th>
                    <th class="p-4">Onde aparece</th>
                    <th class="p-4">Formato</th>
                    <th class="p-4 pr-6 text-right">Medida (px)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach(\App\Models\Advertisement::getPositionSpecs() as $key => $spec)
                    <tr class="hover:bg-gray-50/80 transition-colors" data-ad-position="{{ $key }}">
                        <td class="p-4 pl-6">
                            <span class="text-[11px] font-black text-gray-900 uppercase tracking-wide">{{ $spec['label'] }}</span>
                        </td>
                        <td class="p-4 text-[10px] font-bold text-gray-500 uppercase tracking-wide">
                            {{ $spec['location'] }}
                        </td>
                        <td class="p-4">
                            <span class="inline-block bg-gray-100 text-gray-700 font-black uppercase text-[8px] tracking-wider px-2.5 py-1 rounded-full">
                                {{ $spec['format'] }}
                            </span>
                        </td>
                        <td class="p-4 pr-6 text-right font-mono text-sm font-black text-gray-900 whitespace-nowrap">
                            {{ $spec['width'] }} × {{ $spec['height'] }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
