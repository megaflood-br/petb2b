<div class="space-y-6">
    <form wire:submit.prevent class="bg-white rounded-[2rem] border border-gray-100 shadow-sm p-6 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="lg:col-span-2">
                <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Busca</label>
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Campanha ou empresa..." class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-sm font-bold focus:ring-2 focus:ring-brand-500">
            </div>
            <div>
                <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Empresa</label>
                <select wire:model.live="supplierId" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-sm font-bold focus:ring-2 focus:ring-brand-500">
                    <option value="">Todas</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Posição</label>
                <select wire:model.live="position" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-sm font-bold focus:ring-2 focus:ring-brand-500">
                    <option value="">Todas</option>
                    @foreach($positions as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Status</label>
                <select wire:model.live="status" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-sm font-bold focus:ring-2 focus:ring-brand-500">
                    <option value="">Todos</option>
                    <option value="active">Ativos</option>
                    <option value="paused">Pausados</option>
                </select>
            </div>
            <div>
                <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Cortesia</label>
                <select wire:model.live="courtesy" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-sm font-bold focus:ring-2 focus:ring-brand-500">
                    <option value="">Todas</option>
                    <option value="yes">Só cortesia</option>
                    <option value="no">Só pagas</option>
                </select>
            </div>
            <div>
                <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Cadastro de</label>
                <input type="date" wire:model.live="dateFrom" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-sm font-bold focus:ring-2 focus:ring-brand-500">
            </div>
            <div>
                <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Cadastro até</label>
                <input type="date" wire:model.live="dateTo" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-sm font-bold focus:ring-2 focus:ring-brand-500">
            </div>
        </div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pt-2 border-t border-gray-50">
            <div class="flex flex-wrap gap-2">
                @foreach($groups as $key => $label)
                    <button type="button" wire:click="$set('groupBy', '{{ $key }}')" class="px-4 py-2.5 rounded-xl font-black uppercase text-[9px] tracking-widest transition {{ $groupBy === $key ? 'bg-gray-900 text-white' : 'bg-gray-50 text-gray-400 hover:bg-gray-100' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
            <button type="button" wire:click="exportCsv" class="shrink-0 bg-brand-500 hover:bg-brand-600 text-white font-black uppercase text-[10px] tracking-wider px-5 py-3 rounded-xl transition shadow-md shadow-brand-500/10">
                Exportar CSV
            </button>
        </div>
        <p class="text-[9px] text-gray-400 font-medium normal-case">O período filtra a data de cadastro da campanha e os gastos debitados no mesmo intervalo. Views e cliques são totais da campanha.</p>
    </form>

    <div class="grid grid-cols-2 lg:grid-cols-6 gap-3">
        <div class="bg-white rounded-2xl border border-gray-100 p-4">
            <p class="text-[8px] font-black uppercase text-gray-400 tracking-widest">Campanhas</p>
            <p class="text-xl font-black text-gray-900 mt-1">{{ $summary['campaigns'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-4">
            <p class="text-[8px] font-black uppercase text-gray-400 tracking-widest">Ativas</p>
            <p class="text-xl font-black text-green-600 mt-1">{{ $summary['active'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-4">
            <p class="text-[8px] font-black uppercase text-gray-400 tracking-widest">Views</p>
            <p class="text-xl font-black text-gray-900 font-mono mt-1">{{ number_format($summary['views'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-4">
            <p class="text-[8px] font-black uppercase text-gray-400 tracking-widest">Cliques</p>
            <p class="text-xl font-black text-gray-900 font-mono mt-1">{{ number_format($summary['clicks'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-4">
            <p class="text-[8px] font-black uppercase text-gray-400 tracking-widest">CTR</p>
            <p class="text-xl font-black text-gray-900 font-mono mt-1">{{ number_format($summary['ctr'], 2, ',', '.') }}%</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-4">
            <p class="text-[8px] font-black uppercase text-gray-400 tracking-widest">Gasto debitado</p>
            <p class="text-xl font-black text-gray-900 font-mono mt-1">R$ {{ number_format($summary['billed'], 2, ',', '.') }}</p>
        </div>
    </div>

    <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-x-auto">
        @if($groupBy === 'supplier')
            <table class="w-full text-left border-collapse min-w-[640px]">
                <thead>
                    <tr class="bg-gray-950 text-white uppercase text-[9px] tracking-wider">
                        <th class="p-5"><button type="button" wire:click="sort('supplier')" class="uppercase tracking-wider">Empresa</button></th>
                        <th class="p-5 text-center"><button type="button" wire:click="sort('campaigns')" class="uppercase tracking-wider">Campanhas</button></th>
                        <th class="p-5 text-center"><button type="button" wire:click="sort('views')" class="uppercase tracking-wider">Views</button></th>
                        <th class="p-5 text-center"><button type="button" wire:click="sort('clicks')" class="uppercase tracking-wider">Cliques</button></th>
                        <th class="p-5 text-center">CTR</th>
                        <th class="p-5 text-right">Gasto debitado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($rows as $row)
                        <tr class="hover:bg-gray-50/80">
                            <td class="p-5 text-sm font-black text-gray-900 uppercase">{{ $row->supplier }}</td>
                            <td class="p-5 text-center font-mono">{{ $row->campaigns }}</td>
                            <td class="p-5 text-center font-mono">{{ number_format($row->views, 0, ',', '.') }}</td>
                            <td class="p-5 text-center font-mono">{{ number_format($row->clicks, 0, ',', '.') }}</td>
                            <td class="p-5 text-center font-mono">{{ number_format($row->ctr, 2, ',', '.') }}%</td>
                            <td class="p-5 text-right font-mono">R$ {{ number_format($row->billed, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-10 text-center text-gray-400 uppercase tracking-widest">Nenhuma campanha encontrada com esses filtros.</td></tr>
                    @endforelse
                </tbody>
            </table>
        @elseif($groupBy === 'position')
            <table class="w-full text-left border-collapse min-w-[640px]">
                <thead>
                    <tr class="bg-gray-950 text-white uppercase text-[9px] tracking-wider">
                        <th class="p-5"><button type="button" wire:click="sort('position_label')" class="uppercase tracking-wider">Posição</button></th>
                        <th class="p-5 text-center"><button type="button" wire:click="sort('campaigns')" class="uppercase tracking-wider">Campanhas</button></th>
                        <th class="p-5 text-center"><button type="button" wire:click="sort('views')" class="uppercase tracking-wider">Views</button></th>
                        <th class="p-5 text-center"><button type="button" wire:click="sort('clicks')" class="uppercase tracking-wider">Cliques</button></th>
                        <th class="p-5 text-center">CTR</th>
                        <th class="p-5 text-right">Gasto debitado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($rows as $row)
                        <tr class="hover:bg-gray-50/80">
                            <td class="p-5 text-sm font-black text-gray-900 uppercase">{{ $row->position_label }}</td>
                            <td class="p-5 text-center font-mono">{{ $row->campaigns }}</td>
                            <td class="p-5 text-center font-mono">{{ number_format($row->views, 0, ',', '.') }}</td>
                            <td class="p-5 text-center font-mono">{{ number_format($row->clicks, 0, ',', '.') }}</td>
                            <td class="p-5 text-center font-mono">{{ number_format($row->ctr, 2, ',', '.') }}%</td>
                            <td class="p-5 text-right font-mono">R$ {{ number_format($row->billed, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-10 text-center text-gray-400 uppercase tracking-widest">Nenhuma campanha encontrada com esses filtros.</td></tr>
                    @endforelse
                </tbody>
            </table>
        @else
            <table class="w-full text-left border-collapse min-w-[960px]">
                <thead>
                    <tr class="bg-gray-950 text-white uppercase text-[9px] tracking-wider">
                        <th class="p-5">Fornecedor / Campanha</th>
                        <th class="p-5">Posição</th>
                        <th class="p-5 text-center"><button type="button" wire:click="sort('views')" class="uppercase tracking-wider">Views</button></th>
                        <th class="p-5 text-center"><button type="button" wire:click="sort('clicks')" class="uppercase tracking-wider">Cliques</button></th>
                        <th class="p-5 text-center">CTR</th>
                        <th class="p-5 text-right">Gasto debitado</th>
                        <th class="p-5 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($rows as $row)
                        <tr class="hover:bg-gray-50/80">
                            <td class="p-5">
                                <span class="text-gray-400 text-[9px] uppercase block font-black mb-0.5">{{ $row->supplier }}</span>
                                <span class="text-sm font-black text-gray-900 uppercase truncate max-w-[280px] block">{{ $row->title }}</span>
                                @if($row->skip_credits)
                                    <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-[8px] uppercase font-black bg-amber-50 text-amber-700 border border-amber-100">Cortesia</span>
                                @endif
                            </td>
                            <td class="p-5 text-gray-700 font-bold text-[10px] uppercase tracking-wide max-w-[200px] truncate">{{ $row->position_label }}</td>
                            <td class="p-5 text-center font-mono">{{ number_format($row->views, 0, ',', '.') }}</td>
                            <td class="p-5 text-center font-mono">{{ number_format($row->clicks, 0, ',', '.') }}</td>
                            <td class="p-5 text-center font-mono">{{ number_format($row->ctr, 2, ',', '.') }}%</td>
                            <td class="p-5 text-right font-mono">
                                @if($row->skip_credits)
                                    <span class="text-amber-700">R$ 0,00</span>
                                @else
                                    R$ {{ number_format($row->billed, 2, ',', '.') }}
                                @endif
                            </td>
                            <td class="p-5 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[9px] uppercase font-black {{ $row->is_active ? 'bg-green-50 text-green-600 border border-green-100' : 'bg-red-50 text-red-600 border border-red-100' }}">
                                    {{ $row->is_active ? 'Ativo' : 'Pausado' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="p-10 text-center text-gray-400 uppercase tracking-widest">Nenhuma campanha encontrada com esses filtros.</td></tr>
                    @endforelse
                </tbody>
            </table>
        @endif
    </div>
</div>
