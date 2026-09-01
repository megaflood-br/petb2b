<div class="bg-white p-8 rounded-[3rem] shadow-sm border border-gray-100 mt-12">
    <h3 class="text-xl font-black text-gray-900 uppercase tracking-tight mb-8">Mensagens Recebidas (Leads)</h3>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-gray-50">
                    <th class="py-4 px-2 text-[10px] font-black text-gray-400 uppercase tracking-widest">Data</th>
                    <th class="py-4 px-2 text-[10px] font-black text-gray-400 uppercase tracking-widest">Cliente</th>
                    <th class="py-4 px-2 text-[10px] font-black text-gray-400 uppercase tracking-widest">Mensagem</th>
                    <th class="py-4 px-2 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Ação</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($leads as $lead)
                    <tr class="{{ $lead->is_read ? 'opacity-60' : 'font-bold' }} hover:bg-gray-50/50 transition">
                        <td class="py-6 px-2 text-xs text-gray-500 align-top">{{ $lead->created_at->format('d/m/Y') }}</td>
                        <td class="py-6 px-2 align-top min-w-[150px]">
                            <p class="text-sm text-gray-900">{{ $lead->name }}</p>
                            <p class="text-[10px] text-gray-400 lowercase">{{ $lead->email }}</p>
                            @if($lead->phone)
                                <p class="text-[10px] text-indigo-500 font-black mt-1">{{ $lead->phone }}</p>
                            @endif
                        </td>
                        <td class="py-6 px-2 align-top">
                            {{-- Aqui a mágica: whitespace-pre-line mantém as quebras que o cliente digitou --}}
                            <div class="text-sm text-gray-600 leading-relaxed max-w-lg break-words whitespace-pre-line">
                                {{ $lead->message }}
                            </div>
                        </td>
                        <td class="py-6 px-2 text-right align-top">
                            @if(!$lead->is_read)
                                <button wire:click="markAsRead({{ $lead->id }})" class="text-[10px] font-black uppercase text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-3 py-1 rounded-lg">
                                    Marcar Lida
                                </button>
                            @else
                                <span class="text-[10px] font-black uppercase text-gray-300">Lida</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-20 text-center text-gray-400 text-xs font-black uppercase tracking-widest">
                            Nenhuma mensagem recebida ainda.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
