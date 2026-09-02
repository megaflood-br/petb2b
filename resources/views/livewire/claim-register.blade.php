<div class="max-w-md mx-auto">
    <div class="mb-6 text-center">
        <p class="text-[10px] font-black uppercase text-brand-500 tracking-[0.25em] mb-1">Ativação de Acesso</p>
        <h1 class="text-2xl font-black text-gray-900 uppercase italic tracking-tight">Crie o acesso da sua empresa</h1>
        <p class="mt-2 text-sm text-gray-500 font-medium">{{ $companyName }}</p>
    </div>

    <form wire:submit.prevent="register" class="space-y-4">
        <div>
            <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Seu nome</label>
            <input type="text" wire:model="name" class="w-full bg-gray-50 border-none rounded-xl p-3.5 focus:ring-2 focus:ring-brand-500">
            @error('name') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">E-mail</label>
            <input type="email" value="{{ $email }}" disabled class="w-full bg-gray-100 border-none rounded-xl p-3.5 text-gray-500 cursor-not-allowed">
        </div>

        <div>
            <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Senha</label>
            <input type="password" wire:model="password" class="w-full bg-gray-50 border-none rounded-xl p-3.5 focus:ring-2 focus:ring-brand-500" autocomplete="new-password">
            @error('password') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Confirme a senha</label>
            <input type="password" wire:model="password_confirmation" class="w-full bg-gray-50 border-none rounded-xl p-3.5 focus:ring-2 focus:ring-brand-500" autocomplete="new-password">
        </div>

        <button type="submit" class="w-full bg-brand-500 hover:bg-brand-600 text-white px-8 py-4 rounded-xl font-black uppercase text-[11px] tracking-widest transition">
            Ativar e acessar painel
        </button>
    </form>
</div>
