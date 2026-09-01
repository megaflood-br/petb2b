<div class="space-y-6 text-xs font-bold">
    @if (session()->has('message'))
        <div class="bg-green-50 p-4 rounded-xl border border-green-100 text-green-600 uppercase text-[10px]">
            {{ session('message') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-50 p-4 rounded-xl border border-red-100 text-red-600 uppercase text-[10px] space-y-1">
            <p class="font-black">O formulário não salvou pelos seguintes motivos:</p>
            <ul class="list-disc pl-4 font-medium normal-case">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form wire:submit.prevent="save" class="space-y-6">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">Nome Fantasia</label>
                <input type="text" wire:model="name" class="w-full bg-gray-50 border-none rounded-xl p-4 text-gray-900 focus:ring-2 focus:ring-brand-500">
                @error('name') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">E-mail de Contato Público</label>
                <input type="email" wire:model="email" class="w-full bg-gray-50 border-none rounded-xl p-4 text-gray-900 focus:ring-2 focus:ring-brand-500">
                @error('email') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">Website da Empresa (URL)</label>
                <input type="text" wire:model="website" placeholder="https://site.com.br" class="w-full bg-gray-50 border-none rounded-xl p-4 text-gray-900 focus:ring-2 focus:ring-brand-500">
            </div>

            <div>
                <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">Categoria</label>
                <select wire:model="category" class="w-full bg-gray-50 border-none rounded-xl p-4 text-gray-900 focus:ring-2 focus:ring-brand-500">
                    <option value="">Selecione...</option>
                    @foreach($categoriesList as $cat)
                        <option value="{{ $cat->name }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                @error('category') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">WhatsApp Comercial (Apenas números)</label>
                <input type="text" wire:model="whatsapp" placeholder="11999999999" class="w-full bg-gray-50 border-none rounded-xl p-4 text-gray-900 focus:ring-2 focus:ring-brand-500">
                @error('whatsapp') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">Telefone Fixo / Comercial</label>
                <input type="text" wire:model="phone" placeholder="1144112233" class="w-full bg-gray-50 border-none rounded-xl p-4 text-gray-900 focus:ring-2 focus:ring-brand-500">
                @error('phone') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">CNPJ (Opcional)</label>
                <input type="text" wire:model="cnpj" placeholder="00.000.000/0000-00" class="w-full bg-gray-50 border-none rounded-xl p-4 text-gray-900 focus:ring-2 focus:ring-brand-500">
            </div>

            <div>
                <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">Logotipo da Empresa</label>
                <div class="flex items-center gap-4">
                    <input type="file" wire:model="logo" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-brand-50 file:text-brand-700">

                    @if($logo)
                        <div class="w-12 h-12 rounded-xl overflow-hidden border bg-gray-100 flex-shrink-0">
                            <img src="{{ $logo->temporaryUrl() }}" class="w-full h-full object-cover">
                        </div>
                    @elseif($existingLogo)
                        <div class="w-12 h-12 rounded-xl overflow-hidden border bg-gray-100 flex-shrink-0">
                            <img src="{{ asset('storage/' . $existingLogo) }}" class="w-full h-full object-cover">
                        </div>
                    @endif
                </div>
                @error('logo') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <div>
            <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">Descrição da Empresa</label>
            <textarea wire:model="description" rows="4" class="w-full bg-gray-50 border-none rounded-xl p-4 text-gray-900 focus:ring-2 focus:ring-brand-500"></textarea>
            @error('description') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div class="pt-4 border-t border-gray-100 space-y-6">
            <h3 class="text-sm font-black text-gray-900 uppercase italic mb-2">Localização e Endereço</h3>

            <div>
                <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">Logradouro (Rua, Nº, Bairro)</label>
                <input type="text" wire:model="address" placeholder="Ex: Rua Machado de Assis, 465 - Jardim das Cerejeiras" class="w-full bg-gray-50 border-none rounded-xl p-4 text-gray-900 focus:ring-2 focus:ring-brand-500">
                @error('address') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
            </div>

            {{-- CORREÇÃO: Inputs adicionados para sincronizar com as colunas city e state da tabela suppliers --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">Cidade</label>
                    <input type="text" wire:model="city" class="w-full bg-gray-50 border-none rounded-xl p-4 text-gray-900 focus:ring-2 focus:ring-brand-500">
                    @error('city') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block">Estado (UF)</label>
                    <input type="text" wire:model="state" class="w-full bg-gray-50 border-none rounded-xl p-4 text-gray-900 focus:ring-2 focus:ring-brand-500">
                    @error('state') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <button type="submit" class="w-full bg-brand-500 text-white p-5 rounded-2xl font-black uppercase tracking-widest hover:bg-brand-600 transition shadow-lg shadow-brand-100 mt-4">
            Salvar Alterações do Perfil
        </button>
    </form>
</div>
