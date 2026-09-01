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

                @auth
                    {{-- USUÁRIO LOGADO: Aparece o campo para preencher --}}
                    <h2 class="text-xl font-black uppercase italic text-gray-900 mb-6 border-l-4 border-indigo-600 pl-4">Confirmar Identidade</h2>
                    <p class="text-xs text-gray-500 font-medium mb-6">Explique brevemente como podemos confirmar que você é o proprietário.</p>

                    <textarea wire:model="message" rows="4"
                        class="w-full bg-gray-50 border-none rounded-2xl p-4 font-bold text-xs mb-6 focus:ring-4 focus:ring-indigo-500/10"
                        placeholder="Ex: Sou o dono e gostaria de atualizar meus dados..."></textarea>

                    <div class="flex justify-end gap-4">
                        <button wire:click="$set('showModal', false)" class="text-[10px] font-black uppercase text-gray-400">Cancelar</button>
                        <button wire:click="submitClaim" class="bg-indigo-600 text-white px-8 py-4 rounded-2xl font-black uppercase text-[10px] tracking-widest shadow-xl">Enviar Pedido</button>
                    </div>
                @else
                    {{-- VISITANTE: Apenas aviso e link --}}
                    <div class="text-center">
                        <div class="w-16 h-16 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center text-2xl mx-auto mb-6">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h2 class="text-xl font-black uppercase italic text-gray-900 mb-4">Acesso Necessário</h2>
                        <p class="text-xs text-gray-500 font-medium mb-8 leading-relaxed">
                            Para reivindicar uma empresa, você precisa ter uma conta no portal. <br>
                            <strong>Após o cadastro, você será trazido de volta a esta página automaticamente.</strong>
                        </p>

                        <div class="flex flex-col gap-3">
                           <button wire:click="redirectToRegister"
        class="bg-indigo-600 text-white px-8 py-4 rounded-2xl font-black uppercase text-[10px] tracking-widest shadow-xl shadow-indigo-200">
    Criar Minha Conta Grátis
</button>
                            <button wire:click="$set('showModal', false)" class="text-[10px] font-black uppercase text-gray-400 p-2">Voltar</button>
                        </div>
                    </div>
                @endauth
            </div>
        </div>
    @endif
</div>
