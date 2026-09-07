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

        <div
            wire:ignore
            x-data="{
                stage: 'idle',
                uploadRatio: 0,
                processed: 0,
                total: 0,
                label: '',
                error: '',
                summary: '',
                errors: [],
                startUrl: '{{ route('admin.wordpress-import') }}',
                processUrl: '{{ route('admin.wordpress-import.process') }}',
                csrf() {
                    return document.querySelector('meta[name=csrf-token]')?.content || '{{ csrf_token() }}';
                },
                barPercent() {
                    if (this.stage === 'done') return 100;
                    if (this.stage === 'upload') return Math.max(2, Math.round(this.uploadRatio * 25));
                    if (this.stage === 'parse') return 28;
                    if (this.stage === 'import' && this.total > 0) return 30 + Math.round((this.processed / this.total) * 70);
                    if (this.stage === 'import') return 30;
                    return 0;
                },
                busy() {
                    return this.stage === 'upload' || this.stage === 'parse' || this.stage === 'import';
                },
                async start(event) {
                    event.preventDefault();
                    this.error = '';
                    this.summary = '';
                    this.errors = [];
                    const form = event.target;
                    const fileInput = form.querySelector('input[type=file]');
                    if (! fileInput.files.length) {
                        this.error = 'Envie o arquivo XML exportado do WordPress.';
                        return;
                    }
                    this.stage = 'upload';
                    this.uploadRatio = 0;
                    this.label = 'Enviando arquivo…';
                    const token = await this.upload(form);
                    if (token === false) return;
                    if (! token) {
                        this.stage = 'done';
                        this.label = 'Concluído';
                        return;
                    }
                    this.stage = 'import';
                    await this.runBatches(token);
                },
                upload(form) {
                    return new Promise((resolve) => {
                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', this.startUrl);
                        xhr.setRequestHeader('Accept', 'application/json');
                        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                        xhr.setRequestHeader('X-CSRF-TOKEN', this.csrf());
                        xhr.upload.onprogress = (ev) => {
                            if (ev.lengthComputable) {
                                this.uploadRatio = ev.loaded / ev.total;
                                this.label = 'Enviando arquivo… ' + Math.round(this.uploadRatio * 100) + '%';
                            }
                        };
                        xhr.onload = () => {
                            let data = {};
                            try { data = JSON.parse(xhr.responseText || '{}'); } catch (e) { data = {}; }
                            if (xhr.status >= 200 && xhr.status < 300 && data.ok) {
                                this.total = data.total || 0;
                                this.processed = 0;
                                this.stage = 'parse';
                                this.label = this.total ? ('XML lido: ' + this.total + ' posts. Importando…') : 'Lendo XML…';
                                if (data.done) {
                                    this.summary = data.summary || 'Importação concluída.';
                                    resolve(null);
                                    return;
                                }
                                resolve(data.token);
                                return;
                            }
                            this.stage = 'idle';
                            this.error = data.message || (data.errors && Object.values(data.errors).flat()[0]) || 'Não foi possível ler o XML.';
                            resolve(false);
                        };
                        xhr.onerror = () => {
                            this.stage = 'idle';
                            this.error = 'Falha de rede ao enviar o arquivo.';
                            resolve(false);
                        };
                        xhr.send(new FormData(form));
                    });
                },
                async runBatches(token) {
                    try {
                        while (true) {
                            const response = await fetch(this.processUrl, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': this.csrf(),
                                },
                                body: JSON.stringify({ token }),
                            });
                            const data = await response.json().catch(() => ({}));
                            if (! response.ok || ! data.ok) {
                                this.stage = 'idle';
                                this.error = data.message || 'Falha ao importar os posts.';
                                return;
                            }
                            this.processed = data.processed || 0;
                            this.total = data.total || this.total;
                            this.label = this.total
                                ? ('Importando posts ' + this.processed + ' / ' + this.total)
                                : 'Importando…';
                            this.errors = data.errors || [];
                            if (data.done) {
                                this.stage = 'done';
                                this.summary = data.summary || 'Importação concluída.';
                                this.label = 'Concluído';
                                return;
                            }
                        }
                    } catch (e) {
                        this.stage = 'idle';
                        this.error = 'Falha de rede ao importar os posts. Envie o XML de novo para continuar.';
                    }
                },
            }"
            class="space-y-4"
        >
            <style>[x-cloak]{display:none!important}</style>
            <form @submit.prevent="start" action="{{ route('admin.wordpress-import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Arquivo XML (WXR)</label>
                    <input type="file" name="wordpress_xml" accept=".xml,text/xml,application/xml" required :disabled="busy()" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-brand-50 file:text-brand-700">
                    @error('wordpress_xml') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    <p class="text-[10px] text-gray-400 font-medium normal-case mt-2">Limite 100 MB. A barra mostra o envio e a importação em lotes — assim o servidor não estoura o tempo.</p>
                </div>

                <label class="flex items-center gap-3 text-[11px] font-bold text-gray-600 normal-case">
                    <input type="checkbox" name="download_images" value="1" checked class="rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                    Baixar imagens (capa e fotos do texto) para o armazenamento do portal
                </label>

                <div x-show="stage !== 'idle' || summary" x-cloak class="space-y-2">
                    <div class="flex justify-between text-[10px] font-black uppercase tracking-widest text-gray-500">
                        <span x-text="label"></span>
                        <span x-text="barPercent() + '%'"></span>
                    </div>
                    <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-brand-500 rounded-full transition-all duration-300" :style="'width:' + barPercent() + '%'"></div>
                    </div>
                </div>

                <p x-show="error" x-cloak class="text-red-600 text-[11px] font-bold normal-case" x-text="error"></p>
                <p x-show="summary" x-cloak class="text-green-700 text-[11px] font-bold normal-case" x-text="summary"></p>
                <ul x-show="errors.length" x-cloak class="bg-red-50 p-4 rounded-xl border border-red-100 text-red-600 text-[11px] font-medium normal-case space-y-1">
                    <template x-for="item in errors" :key="item">
                        <li x-text="item"></li>
                    </template>
                </ul>

                <button type="submit" :disabled="busy()" class="bg-gray-900 hover:bg-brand-500 text-white px-8 py-4 rounded-xl font-black uppercase text-[11px] tracking-widest transition disabled:opacity-50">
                    <span x-show="!busy()">Importar XML</span>
                    <span x-show="busy()" x-cloak>Importando…</span>
                </button>
            </form>
        </div>
    </div>
</div>
