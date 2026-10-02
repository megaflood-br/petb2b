<x-app-layout>
    <div class="bg-gray-50 min-h-screen py-16">
        <div class="max-w-3xl mx-auto px-6 space-y-8">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                <div>
                    <p class="text-[10px] font-black uppercase text-brand-500 tracking-[0.25em] mb-1">Conta</p>
                    <h1 class="text-4xl font-black text-gray-900 uppercase italic tracking-tight">Meu <span class="text-brand-500">Perfil</span></h1>
                    <p class="mt-3 text-base text-gray-500 font-medium">Atualize foto, dados de acesso, CPF/CNPJ e senha.</p>
                </div>
                <a href="{{ route('favorites.index') }}" class="bg-brand-50 text-brand-600 hover:bg-brand-500 hover:text-white px-6 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition shadow-sm">
                    Ver favoritos →
                </a>
            </div>

            <div class="p-6 sm:p-10 bg-white shadow-sm border border-gray-100 rounded-[2.5rem]">
                <livewire:profile.update-profile-information-form />
            </div>

            <div class="p-6 sm:p-10 bg-white shadow-sm border border-gray-100 rounded-[2.5rem]">
                <livewire:profile.update-password-form />
            </div>

            <div class="p-6 sm:p-10 bg-white shadow-sm border border-gray-100 rounded-[2.5rem]">
                <livewire:profile.delete-user-form />
            </div>
        </div>
    </div>
</x-app-layout>
