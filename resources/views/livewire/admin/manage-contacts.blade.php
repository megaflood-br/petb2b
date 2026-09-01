<div class="p-6">
    {{-- ... (cabeçalho e alertas permanecem iguais) --}}

    <div class="space-y-6">
        @forelse($messages as $msg)
            <div class="bg-white p-8 rounded-[2.5rem] border {{ $msg->is_read ? 'border-gray-100' : 'border-indigo-100 shadow-xl shadow-indigo-50' }}">
                <div class="flex flex-col md:flex-row justify-between gap-6">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="text-sm font-black text-gray-900 uppercase tracking-tight">{{ $msg->name }}</span>
                            @if(!$msg->is_read) <span class="bg-indigo-600 text-white text-[8px] font-black px-2 py-0.5 rounded-full uppercase">Nova</span> @endif
                        </div>
                        <h4 class="text-xs font-bold text-indigo-400 uppercase tracking-widest mb-2">{{ $msg->subject }}</h4>
                        <p class="text-gray-600 text-sm leading-relaxed mb-4">{{ $msg->message }}</p>
                    </div>

                    <div class="flex md:flex-col gap-2 shrink-0">
                        <button wire:click="startReply({{ $msg->id }})" class="bg-indigo-600 text-white px-6 py-2 rounded-xl font-black text-[9px] uppercase hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">Responder</button>
                        @if(!$msg->is_read)
                            <button wire:click="markAsRead({{ $msg->id }})" class="bg-gray-100 text-gray-500 px-6 py-2 rounded-xl font-black text-[9px] uppercase hover:bg-gray-200">Lida</button>
                        @endif
                        <button onclick="confirm('Excluir?') || event.stopImmediatePropagation()" wire:click="delete({{ $msg->id }})" class="bg-red-50 text-red-500 px-6 py-2 rounded-xl font-black text-[9px] uppercase">Excluir</button>
                    </div>
                </div>

                {{-- Campo de Resposta Dinâmico --}}
                @if($replyingTo == $msg->id)
                    <div class="mt-6 pt-6 border-t border-gray-50 animate-in fade-in slide-in-from-top-2 duration-300">
                        <label class="text-[9px] font-black text-indigo-600 uppercase tracking-[0.2em] mb-2 block">Sua Resposta para {{ $msg->email }}</label>
                        <textarea wire:model="replyText" rows="4" class="w-full bg-indigo-50/50 border-none rounded-2xl p-5 font-medium text-sm focus:ring-4 focus:ring-indigo-500/10 transition mb-4" placeholder="Escreva sua resposta aqui..."></textarea>

                        <div class="flex gap-3">
                            <button wire:click="sendReply" wire:loading.attr="disabled" class="bg-indigo-600 text-white px-8 py-3 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-indigo-700">
                                <span wire:loading.remove wire:target="sendReply">Enviar E-mail Agora</span>
                                <span wire:loading wire:target="sendReply">Enviando...</span>
                            </button>
                            <button wire:click="$set('replyingTo', null)" class="text-[10px] font-black uppercase text-gray-400 hover:text-gray-600">Cancelar</button>
                        </div>
                    </div>
                @endif
            </div>
        @empty
            {{-- Vazio --}}
        @endforelse
    </div>
</div>
