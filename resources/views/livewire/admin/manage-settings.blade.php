<div class="space-y-8 max-w-3xl">

    @if (session()->has('message'))
        <div class="bg-green-50 p-4 rounded-xl border border-green-100 text-green-600 uppercase text-[10px] font-black">{{ session('message') }}</div>
    @endif

    @if (session()->has('wordpress_import_error'))
        <div class="bg-red-50 p-4 rounded-xl border border-red-100 text-red-600 text-[11px] font-bold normal-case">{{ session('wordpress_import_error') }}</div>
    @endif

    <div class="border-b pb-6">
        <h1 class="text-2xl font-black text-gray-900 uppercase italic tracking-tight">Configurações</h1>
        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mt-0.5">Custos de anúncios, PIX/Asaas e importação de posts do WordPress</p>
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

    {{-- Importação WordPress (WXR) --}}
    <div class="bg-white border border-gray-100 rounded-[2rem] p-8 shadow-sm space-y-5">
        <div>
            <h2 class="text-sm font-black text-gray-900 uppercase tracking-wide">Importar posts do WordPress</h2>
            <p class="text-[10px] text-gray-400 font-medium normal-case leading-relaxed mt-2">
                No WordPress: <span class="font-bold text-gray-500">Ferramentas → Exportar → Posts</span>
                (de preferência com mídia). Envie o XML gerado. Posts já existentes (mesmo slug) não são duplicados;
                uma nova importação com download de imagens completa a capa e as fotos do texto que faltaram.
            </p>
        </div>

        @if(session('wordpress_import_errors'))
            <ul class="bg-red-50 p-4 rounded-xl border border-red-100 text-red-600 text-[11px] font-medium normal-case space-y-1">
                @foreach(session('wordpress_import_errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <form action="{{ route('admin.wordpress-import') }}" method="POST" enctype="multipart/form-data" class="space-y-4" wire:ignore>
            @csrf
            <div>
                <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Arquivo XML (WXR)</label>
                <input type="file" name="wordpress_xml" accept=".xml,text/xml,application/xml" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-brand-50 file:text-brand-700">
                @error('wordpress_xml') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                <p class="text-[10px] text-gray-400 font-medium normal-case mt-2">Limite 100 MB. A importação pode levar alguns minutos se houver muitos posts.</p>
            </div>

            <label class="flex items-center gap-3 text-[11px] font-bold text-gray-600 normal-case">
                <input type="checkbox" name="download_images" value="1" checked class="rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                Baixar imagens (capa e fotos do texto) para o armazenamento do portal
            </label>

            <button type="submit" class="bg-gray-900 hover:bg-brand-500 text-white px-8 py-4 rounded-xl font-black uppercase text-[11px] tracking-widest transition">
                Importar XML
            </button>
        </form>
    </div>
</div>
