<div class="bg-gray-50 min-h-screen py-16 px-6">
    <div class="max-w-3xl mx-auto bg-white p-10 rounded-[2.5rem] border border-gray-100 shadow-sm">

        <div class="mb-8 border-b pb-6">
            <p class="text-[10px] font-black uppercase text-brand-500 tracking-widest mb-1">Passo Único obrigatório</p>
            <h1 class="text-2xl font-black text-gray-900 uppercase italic">Configure o Perfil do seu <span class="text-brand-500">Canil</span></h1>
            <p class="text-xs font-medium text-gray-500 mt-1">Insira as informações comerciais da sua criação para liberar o acesso ao catálogo da Revista Negócios Pet.</p>
        </div>

        <form wire:submit.prevent="save" class="space-y-6 text-xs font-bold">

            <div>
                <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">Nome Oficial do Canil</label>
                <input type="text" wire:model="name" placeholder="Ex: Canil Val de Anis" class="w-full bg-gray-50 border-none rounded-xl p-4 text-gray-900 focus:ring-2 focus:ring-brand-500">
                @error('name') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">Afixo do Canil (Se houver)</label>
                    <input type="text" wire:model="affix" placeholder="Ex: Von Atibaia" class="w-full bg-gray-50 border-none rounded-xl p-4 text-gray-900 focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">Nº Registro CBKC / FCI</label>
                    <input type="text" wire:model="registration_number" placeholder="Ex: 5678/26" class="w-full bg-gray-50 border-none rounded-xl p-4 text-gray-900 focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">Raças Que Cria (Separe por vírgula)</label>
                    <input type="text" wire:model="breeds_input" placeholder="Ex: Boxer, Pug" class="w-full bg-gray-50 border-none rounded-xl p-4 text-gray-900 focus:ring-2 focus:ring-brand-500">
                </div>
            </div>

            <div>
                <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">História & Diretrizes de Saúde da Criação</label>
                <textarea wire:model="description" rows="4" placeholder="Conte aos lojistas e veterinários sobre seu controle de displasia, exames genéticos..." class="w-full bg-gray-50 border-none rounded-xl p-4 text-gray-900 focus:ring-2 focus:ring-brand-500"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">Cidade</label>
                    <input type="text" wire:model="city" class="w-full bg-gray-50 border-none rounded-xl p-4 text-gray-900 focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">Estado (UF)</label>
                    <input type="text" wire:model="state" class="w-full bg-gray-50 border-none rounded-xl p-4 text-gray-900 focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">WhatsApp Comercial</label>
                    <input type="text" wire:model="whatsapp" placeholder="11999999999" class="w-full bg-gray-50 border-none rounded-xl p-4 text-gray-900 focus:ring-2 focus:ring-brand-500">
                    @error('whatsapp') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">Instagram (Sem @)</label>
                    <input type="text" wire:model="instagram" placeholder="meucanil" class="w-full bg-gray-50 border-none rounded-xl p-4 text-gray-900 focus:ring-2 focus:ring-brand-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">Logo do Canil</label>
                    <input type="file" wire:model="logo" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-brand-50 file:text-brand-700">
                </div>
                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">Foto da Fachada / Capa</label>
                    <input type="file" wire:model="cover_image" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-brand-50 file:text-brand-700">
                </div>
            </div>

            <button type="submit" class="w-full bg-brand-500 text-white p-5 rounded-2xl font-black uppercase tracking-widest hover:bg-brand-600 transition shadow-lg shadow-brand-100 mt-6">
                Concluir e Abrir Painel
            </button>
        </form>
    </div>
</div>
