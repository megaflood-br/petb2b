<div>
    @if (session()->has('success'))
        <div class="bg-green-50 p-6 rounded-[2rem] border border-green-100 text-green-600 font-black uppercase text-[10px] tracking-widest mb-8 animate-bounce">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit.prevent="send" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <input type="text" wire:model="name" placeholder="Seu Nome" class="w-full bg-gray-50 border-none rounded-2xl p-5 font-bold focus:ring-4 focus:ring-indigo-500/10 transition">
            <input type="email" wire:model="email" placeholder="Seu E-mail" class="w-full bg-gray-50 border-none rounded-2xl p-5 font-bold focus:ring-4 focus:ring-indigo-500/10 transition">
        </div>
        <input type="text" wire:model="subject" placeholder="Assunto" class="w-full bg-gray-50 border-none rounded-2xl p-5 font-bold focus:ring-4 focus:ring-indigo-500/10 transition">
        <textarea wire:model="message" rows="5" placeholder="Como podemos ajudar seu negócio pet?" class="w-full bg-gray-50 border-none rounded-2xl p-5 font-bold focus:ring-4 focus:ring-indigo-500/10 transition"></textarea>

        <button type="submit" wire:loading.attr="disabled" class="w-full bg-indigo-600 text-white p-6 rounded-2xl font-black uppercase tracking-widest hover:bg-indigo-700 transition shadow-xl shadow-indigo-100">
            <span wire:loading.remove>Enviar Mensagem</span>
            <span wire:loading>Enviando...</span>
        </button>
    </form>
</div>
