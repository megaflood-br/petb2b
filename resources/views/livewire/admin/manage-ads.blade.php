<div class="space-y-6 text-xs font-bold">
    <div class="flex justify-between items-center border-b pb-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 uppercase tracking-tight italic">Gerenciamento de Campanhas & Anúncios</h1>
            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mt-0.5">Controle financeiro, auditoria de cliques e alteração de taxas dos fornecedores</p>
        </div>
    </div>

    {{-- Feedback do Admin --}}
    @if (session()->has('message'))
        <div class="bg-green-50 p-4 rounded-xl border border-green-100 text-green-600 uppercase text-[10px]">
            {{ session('message') }}
        </div>
    @endif

    {{-- Tabela de Controle Master --}}
    <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-950 text-white uppercase text-[9px] tracking-wider border-b border-gray-900">
                    <th class="p-5">Fornecedor / Campanha</th>
                    <th class="p-5">Posição Editorial</th>
                    <th class="p-5 text-center">Cliques (Clicks)</th>
                    <th class="p-5 text-center">Visualizações (Views)</th>
                    <th class="p-5 text-center">Custo Clique</th>
                    <th class="p-5 text-center">Custo View</th>
                    <th class="p-5 text-center">Status</th>
                    <th class="p-5 text-center">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($ads as $ad)
                    <tr class="hover:bg-gray-50/80 transition-colors">
                        <td class="p-5">
                            <span class="text-gray-400 text-[9px] uppercase block font-black mb-0.5">{{ $ad->supplier->name ?? 'Fornecedor Desconhecido' }}</span>
                            <span class="text-sm font-black text-gray-900 uppercase truncate max-w-[250px] block">{{ $ad->title }}</span>
                        </td>

                        {{-- COLUNA CORRIGIDA: Agora exibe o rótulo comercial do Model em vez do texto bruto do banco --}}
                        <td class="p-5 text-gray-700 font-bold text-[10px] uppercase tracking-wide max-w-[200px] truncate">
                            {{ \App\Models\Advertisement::getPositions()[$ad->position] ?? str_replace('_', ' ', $ad->position) }}
                        </td>

                        <td class="p-5 text-center font-mono text-sm text-gray-900">
                            {{ $ad->clicks ?? 0 }}
                        </td>
                        <td class="p-5 text-center font-mono text-sm text-gray-900">
                            {{ $ad->views ?? 0 }}
                        </td>
                        <td class="p-5 text-center font-mono text-gray-900 text-xs">
                            R$ {{ number_format($ad->cost_per_click, 2, ',', '.') }}
                        </td>
                        <td class="p-5 text-center font-mono text-gray-900 text-xs">
                            R$ {{ number_format($ad->cost_per_impression, 4, ',', '.') }}
                        </td>
                        <td class="p-5 text-center">
                            <span class="px-2.5 py-1 rounded-full text-[9px] uppercase font-black {{ $ad->is_active ? 'bg-green-50 text-green-600 border border-green-100' : 'bg-red-50 text-red-600 border border-red-100' }}">
                                {{ $ad->is_active ? 'Ativo' : 'Pausado' }}
                            </span>
                        </td>
                        <td class="p-5 text-center">
                            <button wire:click="editAd({{ $ad->id }})" class="bg-gray-900 hover:bg-brand-500 text-white font-black uppercase text-[9px] tracking-wider px-3 py-2 rounded-xl transition-all shadow-sm">
                                Ajustar Taxas
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-10 text-center text-gray-400 uppercase tracking-widest font-bold">
                            Nenhuma campanha de anúncio cadastrada no portal.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($ads->hasPages())
            <div class="p-4 border-t bg-gray-50">
                {{ $ads->links() }}
            </div>
        @endif
    </div>

    {{-- MODAL DE CONFIGURAÇÃO FINANCEIRA DO ANÚNCIO --}}
    @if($isModalOpen)
        <div class="fixed inset-0 bg-gray-950/40 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-[2.5rem] border shadow-2xl max-w-md w-full p-8 space-y-6 relative animate-in fade-in zoom-in-95 duration-150">
                <div>
                    <h3 class="text-lg font-black text-gray-900 uppercase italic tracking-tight">Ajustar Parâmetros de Custo</h3>
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mt-0.5">Campanha: {{ $title }}</p>
                </div>

                <form wire:submit.prevent="saveAdSettings" class="space-y-4">
                    <div>
                        <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Custo por Clique Realizado (R$)</label>
                        <input type="number" step="0.01" min="0" wire:model="cost_per_click" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-gray-900 focus:ring-2 focus:ring-brand-500 font-mono font-bold">
                        @error('cost_per_click') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Custo por Visualização Realizada (R$)</label>
                        <input type="number" step="0.0001" min="0" wire:model="cost_per_impression" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-gray-900 focus:ring-2 focus:ring-brand-500 font-mono font-bold">
                        <span class="text-[8px] text-gray-400 font-medium block mt-1 normal-case">Dica: R$ 0,0050 equivale a R$ 5,00 a cada 1.000 exibições no Rodapé Mobile.</span>
                        @error('cost_per_impression') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Status de Exibição</label>
                        <select wire:model="is_active" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-gray-900 focus:ring-2 focus:ring-brand-500">
                            <option value="1">Ativo (Permitir veiculação se houver saldo)</option>
                            <option value="0">Pausado / Bloqueado pelo Admin</option>
                        </select>
                        @error('is_active') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" wire:click="closeModal" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 p-3.5 rounded-xl font-black uppercase tracking-widest transition">
                            Cancelar
                        </button>
                        <button type="submit" class="flex-1 bg-brand-500 hover:bg-brand-600 text-white p-3.5 rounded-xl font-black uppercase tracking-widest transition shadow-md">
                            Salvar Taxas
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
