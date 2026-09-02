<div>
    @if (session()->has('message'))
        <div class="bg-green-100 text-green-700 p-4 rounded-2xl mb-4 font-bold text-xs uppercase border border-green-200">
            {{ session('message') }}
        </div>
    @endif

    <div class="mt-8 p-8 bg-indigo-600 rounded-[2.5rem] shadow-xl shadow-indigo-200/50 flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="text-center md:text-left text-white">
            <h4 class="font-black uppercase text-lg italic tracking-tighter leading-none mb-2">É proprietário desta empresa?</h4>
            <p class="text-[10px] font-bold uppercase tracking-widest text-indigo-200">Assuma o controle deste perfil para gerenciar seus anúncios.</p>
        </div>

        <button wire:click="$set('showModal', true)"
                class="bg-white text-indigo-600 px-8 py-4 rounded-2xl font-black uppercase text-[10px] tracking-widest hover:scale-105 transition shadow-lg">
            Reivindicar Empresa
        </button>
    </div>

    {{-- Modal de Reivindicação --}}
    @if($showModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-indigo-950/60 backdrop-blur-sm">
            <div class="bg-white w-full max-w-lg rounded-[3rem] p-10 shadow-2xl animate-in zoom-in duration-300">
                <h2 class="text-xl font-black uppercase italic text-gray-900 mb-2 border-l-4 border-indigo-600 pl-4">Reivindicar Empresa</h2>
                <p class="text-xs text-gray-500 font-medium mb-6 pl-5">Após a aprovação, enviaremos um e-mail com o link para você criar o acesso da sua empresa.</p>

                <div class="space-y-4">
                    <div>
                        <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Seu nome</label>
                        <input type="text" wire:model="name" class="w-full bg-gray-50 border-none rounded-2xl p-4 font-bold text-xs focus:ring-4 focus:ring-indigo-500/10">
                        @error('name') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Seu e-mail</label>
                        <input type="email" wire:model="email" class="w-full bg-gray-50 border-none rounded-2xl p-4 font-bold text-xs focus:ring-4 focus:ring-indigo-500/10">
                        @error('email') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Como podemos confirmar que você é o proprietário?</label>
                        <textarea wire:model="message" rows="4" class="w-full bg-gray-50 border-none rounded-2xl p-4 font-bold text-xs focus:ring-4 focus:ring-indigo-500/10" placeholder="Ex: Sou o dono e gostaria de atualizar os dados da empresa..."></textarea>
                        @error('message') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-4 mt-6">
                    <button wire:click="$set('showModal', false)" class="text-[10px] font-black uppercase text-gray-400">Cancelar</button>
                    <button wire:click="submitClaim" class="bg-indigo-600 text-white px-8 py-4 rounded-2xl font-black uppercase text-[10px] tracking-widest shadow-xl">Enviar Pedido</button>
                </div>
            </div>
        </div>
    @endif
</div>
