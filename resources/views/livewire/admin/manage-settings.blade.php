<div class="space-y-8 max-w-3xl">

    @if (session()->has('message'))
        <div class="bg-green-50 p-4 rounded-xl border border-green-100 text-green-600 uppercase text-[10px] font-black">{{ session('message') }}</div>
    @endif

    <div class="border-b pb-6">
        <h1 class="text-2xl font-black text-gray-900 uppercase italic tracking-tight">Configurações</h1>
        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mt-0.5">Custos de anúncios e integração de pagamento (PIX/Asaas)</p>
    </div>

    <form wire:submit.prevent="save" class="space-y-8">

        {{-- Custos de anúncios --}}
        <div class="bg-white border border-gray-100 rounded-[2rem] p-8 shadow-sm space-y-5">
            <h2 class="text-sm font-black text-gray-900 uppercase tracking-wide">Custos dos Anúncios</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Custo por Clique (R$)</label>
                    <input type="number" step="0.01" min="0" wire:model="ads_cost_per_click" class="w-full bg-gray-50 border-none rounded-xl p-3.5 font-mono focus:ring-2 focus:ring-brand-500">
                    @error('ads_cost_per_click') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Custo por Impressão (R$)</label>
                    <input type="number" step="0.0001" min="0" wire:model="ads_cost_per_impression" class="w-full bg-gray-50 border-none rounded-xl p-3.5 font-mono focus:ring-2 focus:ring-brand-500">
                    @error('ads_cost_per_impression') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Recarga mínima (R$)</label>
                    <input type="number" step="1" min="0" wire:model="ads_recharge_min" class="w-full bg-gray-50 border-none rounded-xl p-3.5 font-mono focus:ring-2 focus:ring-brand-500">
                    @error('ads_recharge_min') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Recarga máxima (R$)</label>
                    <input type="number" step="1" min="0" wire:model="ads_recharge_max" class="w-full bg-gray-50 border-none rounded-xl p-3.5 font-mono focus:ring-2 focus:ring-brand-500">
                    @error('ads_recharge_max') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Matéria patrocinada (R$)</label>
                    <input type="number" step="1" min="0" wire:model="ads_sponsored_post_cost" class="w-full bg-gray-50 border-none rounded-xl p-3.5 font-mono focus:ring-2 focus:ring-brand-500">
                    @error('ads_sponsored_post_cost') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        {{-- Asaas / PIX --}}
        <div class="bg-white border border-gray-100 rounded-[2rem] p-8 shadow-sm space-y-5">
            <h2 class="text-sm font-black text-gray-900 uppercase tracking-wide">Pagamento PIX (Asaas)</h2>
            <div class="space-y-4">
                <div>
                    <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Ambiente</label>
                    <select wire:model="asaas_base_url" class="w-full bg-gray-50 border-none rounded-xl p-3.5 focus:ring-2 focus:ring-brand-500">
                        <option value="https://api-sandbox.asaas.com">Sandbox (testes)</option>
                        <option value="https://api.asaas.com">Produção</option>
                    </select>
                    @error('asaas_base_url') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">
                        API Key
                        <span class="ml-2 {{ $asaas_key_set ? 'text-green-600' : 'text-red-500' }}">{{ $asaas_key_set ? '● configurada' : '○ não configurada' }}</span>
                    </label>
                    <input type="password" wire:model="asaas_api_key" placeholder="{{ $asaas_key_set ? 'Deixe em branco para manter a atual' : 'Cole a API key ($aact_...)' }}" class="w-full bg-gray-50 border-none rounded-xl p-3.5 font-mono focus:ring-2 focus:ring-brand-500" autocomplete="off">
                    @error('asaas_api_key') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">
                        Webhook Token
                        <span class="ml-2 {{ $asaas_token_set ? 'text-green-600' : 'text-red-500' }}">{{ $asaas_token_set ? '● configurado' : '○ não configurado' }}</span>
                    </label>
                    <input type="password" wire:model="asaas_webhook_token" placeholder="{{ $asaas_token_set ? 'Deixe em branco para manter o atual' : 'Token forte (32+ caracteres)' }}" class="w-full bg-gray-50 border-none rounded-xl p-3.5 font-mono focus:ring-2 focus:ring-brand-500" autocomplete="off">
                    @error('asaas_webhook_token') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>
                <p class="text-[10px] text-gray-400 font-medium normal-case leading-relaxed">
                    Configure o webhook no painel do Asaas apontando para <code class="font-mono">{{ url('/webhooks/asaas') }}</code> com o mesmo token. As chaves são armazenadas criptografadas.
                </p>
            </div>
        </div>

        <button type="submit" class="bg-brand-500 hover:bg-brand-600 text-white px-8 py-4 rounded-xl font-black uppercase text-[11px] tracking-widest transition">Salvar Configurações</button>
    </form>
</div>
