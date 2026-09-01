<div>
    @if($submitted)
        <div class="bg-green-50 border border-green-100 rounded-2xl p-6 text-center">
            <p class="text-sm font-black text-green-700 uppercase tracking-wide">Candidatura enviada!</p>
            <p class="text-xs text-green-600 font-medium mt-1">A empresa receberá seus dados. Boa sorte!</p>
        </div>
    @else
        <form wire:submit.prevent="apply" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Nome completo</label>
                    <input type="text" wire:model="name" class="w-full bg-gray-50 border border-gray-100 rounded-xl p-3.5 text-gray-900 focus:ring-2 focus:ring-brand-500">
                    @error('name') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">E-mail</label>
                    <input type="email" wire:model="email" class="w-full bg-gray-50 border border-gray-100 rounded-xl p-3.5 text-gray-900 focus:ring-2 focus:ring-brand-500">
                    @error('email') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Telefone / WhatsApp (opcional)</label>
                    <input type="text" wire:model="phone" class="w-full bg-gray-50 border border-gray-100 rounded-xl p-3.5 text-gray-900 focus:ring-2 focus:ring-brand-500">
                    @error('phone') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Currículo PDF/DOC (opcional)</label>
                    <input type="file" wire:model="resume" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-brand-50 file:text-brand-700">
                    @error('resume') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
            <div>
                <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Mensagem (opcional)</label>
                <textarea wire:model="message" rows="4" placeholder="Conte por que você é ideal para a vaga." class="w-full bg-gray-50 border border-gray-100 rounded-xl p-3.5 text-gray-900 focus:ring-2 focus:ring-brand-500"></textarea>
                @error('message') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
            </div>
            <button type="submit" wire:loading.attr="disabled" class="w-full bg-brand-500 hover:bg-brand-600 text-white px-8 py-4 rounded-xl font-black uppercase text-[11px] tracking-widest transition disabled:opacity-50">
                <span wire:loading.remove wire:target="apply">Enviar candidatura</span>
                <span wire:loading wire:target="apply">Enviando…</span>
            </button>
        </form>
    @endif
</div>
