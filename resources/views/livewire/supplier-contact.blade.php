<div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm space-y-4">
    <div>
        <h4 class="font-black uppercase text-[10px] tracking-widest text-brand-500 mb-1">Enviar Mensagem</h4>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Solicite orçamentos ou tire suas dúvidas</p>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-50 p-4 rounded-xl border border-green-100 text-green-600 uppercase text-[10px] font-black">
            {{ session('message') }}
        </div>
    @endif

    {{-- ALINHADO: Chamando o método correto save() --}}
    <form wire:submit.prevent="save" class="space-y-4 text-xs font-bold">
        <div>
            <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Seu Nome</label>
            <input type="text" wire:model="name" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-gray-900 focus:ring-2 focus:ring-brand-500">
            @error('name') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Seu E-mail</label>
            <input type="email" wire:model="email" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-gray-900 focus:ring-2 focus:ring-brand-500">
            @error('email') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
        </div>

        {{-- ALINHADO: Input mapeado para a propriedade $phone atuando como WhatsApp opcional --}}
        <div>
            <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">WhatsApp <span class="text-gray-300 font-medium">(Opcional)</span></label>
            <input type="text" wire:model="phone" placeholder="(11) 99999-9999" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-gray-900 focus:ring-2 focus:ring-brand-500">
            @error('phone') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Sua Mensagem</label>
            <textarea wire:model="message" rows="4" placeholder="Escreva os detalhes do seu pedido..." class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-gray-900 focus:ring-2 focus:ring-brand-500"></textarea>
            @error('message') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="w-full bg-gray-900 text-white p-4 rounded-xl font-black uppercase tracking-widest hover:bg-brand-500 transition shadow-md mt-2">
            Enviar Solicitação
        </button>
    </form>
</div>
