<div class="space-y-8 max-w-3xl">

    @if (session()->has('message'))
        <div class="bg-green-50 p-4 rounded-xl border border-green-100 text-green-600 uppercase text-[10px] font-black">{{ session('message') }}</div>
    @endif

    @if (session()->has('wordpress_import_error'))
        <div class="bg-red-50 p-4 rounded-xl border border-red-100 text-red-600 text-[11px] font-bold normal-case">{{ session('wordpress_import_error') }}</div>
    @endif

    <div class="border-b pb-6">
        <h1 class="text-2xl font-black text-gray-900 uppercase italic tracking-tight">Configurações</h1>
        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mt-0.5">SEO, Google, favicon, manutenção, anúncios e importação WordPress</p>
    </div>

    <form wire:submit.prevent="saveSeo" class="bg-white border border-gray-100 rounded-[2rem] p-8 shadow-sm space-y-5">
        <div>
            <h2 class="text-sm font-black text-gray-900 uppercase tracking-wide">SEO e Google</h2>
            <p class="text-[11px] text-gray-500 font-medium normal-case leading-relaxed mt-2">
                Título, descrição, favicon e códigos de verificação. Isso não garante o 1º lugar no Google, mas deixa o portal pronto para o Search Console, o Analytics e o compartilhamento nas redes.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Nome do site (título padrão)</label>
                <input type="text" wire:model="seo_site_name" maxlength="70" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-sm font-medium focus:ring-2 focus:ring-brand-500">
                @error('seo_site_name') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Descrição padrão (até 180 caracteres)</label>
                <textarea wire:model="seo_default_description" rows="3" maxlength="180" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-sm font-medium focus:ring-2 focus:ring-brand-500"></textarea>
                @error('seo_default_description') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Palavras-chave (separadas por vírgula)</label>
                <input type="text" wire:model="seo_default_keywords" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-sm font-medium focus:ring-2 focus:ring-brand-500" placeholder="mercado pet, fornecedores, revista">
                @error('seo_default_keywords') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Favicon (.ico, .png)</label>
                <input type="file" wire:model="seo_favicon" accept=".ico,image/png,image/jpeg,image/webp,image/svg+xml" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-brand-50 file:text-brand-700">
                <div wire:loading wire:target="seo_favicon" class="text-[9px] text-brand-600 font-black uppercase mt-1">Enviando…</div>
                @if($seo_favicon)
                    <img src="{{ $seo_favicon->temporaryUrl() }}" alt="Prévia do favicon" class="mt-2 w-10 h-10 object-contain bg-gray-50 rounded">
                @elseif($existing_favicon)
                    <img src="{{ asset('storage/'.$existing_favicon) }}" alt="Favicon atual" class="mt-2 w-10 h-10 object-contain bg-gray-50 rounded">
                @endif
                @error('seo_favicon') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Imagem de compartilhamento (Open Graph)</label>
                <input type="file" wire:model="seo_og_image" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-brand-50 file:text-brand-700">
                <div wire:loading wire:target="seo_og_image" class="text-[9px] text-brand-600 font-black uppercase mt-1">Enviando…</div>
                @if($seo_og_image)
                    <img src="{{ $seo_og_image->temporaryUrl() }}" alt="Prévia OG" class="mt-2 h-16 w-auto object-cover rounded">
                @elseif($existing_og_image)
                    <img src="{{ asset('storage/'.$existing_og_image) }}" alt="Imagem OG atual" class="mt-2 h-16 w-auto object-cover rounded">
                @endif
                @error('seo_og_image') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                <p class="text-[10px] text-gray-400 font-medium normal-case mt-1">Ideal 1200×630 px. Aparece no WhatsApp, Facebook e Google.</p>
            </div>
            <div>
                <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Cor do tema (navegador)</label>
                <input type="text" wire:model="seo_theme_color" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-sm font-mono focus:ring-2 focus:ring-brand-500" placeholder="#ed258f">
            </div>
            <div>
                <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Google Analytics (GA4)</label>
                <input type="text" wire:model="seo_ga4_id" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-sm font-mono focus:ring-2 focus:ring-brand-500" placeholder="G-XXXXXXXX">
                @error('seo_ga4_id') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Google Tag Manager</label>
                <input type="text" wire:model="seo_gtm_id" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-sm font-mono focus:ring-2 focus:ring-brand-500" placeholder="GTM-XXXXXXX">
                @error('seo_gtm_id') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Verificação Search Console</label>
                <input type="text" wire:model="seo_google_verification" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-sm font-mono focus:ring-2 focus:ring-brand-500" placeholder="conteúdo da meta google-site-verification">
                @error('seo_google_verification') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Verificação Bing Webmaster</label>
                <input type="text" wire:model="seo_bing_verification" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-sm font-mono focus:ring-2 focus:ring-brand-500">
            </div>
            <div>
                <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Twitter / X (sem @)</label>
                <input type="text" wire:model="seo_twitter" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-sm font-medium focus:ring-2 focus:ring-brand-500" placeholder="rnpet">
            </div>
            <div>
                <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Facebook</label>
                <input type="url" wire:model="seo_facebook" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-sm font-medium focus:ring-2 focus:ring-brand-500" placeholder="https://facebook.com/...">
                @error('seo_facebook') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Instagram</label>
                <input type="url" wire:model="seo_instagram" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-sm font-medium focus:ring-2 focus:ring-brand-500" placeholder="https://instagram.com/...">
                @error('seo_instagram') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">LinkedIn</label>
                <input type="url" wire:model="seo_linkedin" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-sm font-medium focus:ring-2 focus:ring-brand-500">
                @error('seo_linkedin') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">YouTube</label>
                <input type="url" wire:model="seo_youtube" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-sm font-medium focus:ring-2 focus:ring-brand-500">
                @error('seo_youtube') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Regras extras do robots.txt</label>
                <textarea wire:model="seo_robots_extra" rows="3" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-sm font-mono focus:ring-2 focus:ring-brand-500" placeholder="Disallow: /busca"></textarea>
                <p class="text-[10px] text-gray-400 font-medium normal-case mt-1">O sitemap já entra sozinho. Admin, login e painel do fornecedor já estão bloqueados.</p>
            </div>
        </div>

        <button type="submit" class="bg-brand-500 hover:bg-brand-600 text-white px-8 py-4 rounded-xl font-black uppercase text-[11px] tracking-widest transition">
            Salvar SEO e marca
        </button>
    </form>


    <div class="bg-white border {{ $maintenance_enabled ? 'border-amber-200' : 'border-gray-100' }} rounded-[2rem] p-8 shadow-sm space-y-5">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-sm font-black text-gray-900 uppercase tracking-wide">Modo manutenção</h2>
                <p class="text-[11px] text-gray-500 font-medium normal-case leading-relaxed mt-2">
                    Quando ligado, o público vê uma página de aviso. O painel admin e o login continuam acessíveis.
                </p>
            </div>
            <label class="inline-flex items-center gap-3 cursor-pointer shrink-0">
                <span class="text-[9px] font-black uppercase tracking-widest {{ $maintenance_enabled ? 'text-amber-600' : 'text-gray-400' }}">
                    {{ $maintenance_enabled ? 'Ligado' : 'Desligado' }}
                </span>
                <input type="checkbox" wire:model.live="maintenance_enabled" class="rounded-full border-gray-300 text-brand-500 focus:ring-brand-500 h-5 w-5">
            </label>
        </div>
        <div>
            <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Aviso exibido na página</label>
            <textarea wire:model="maintenance_message" rows="3" maxlength="500" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-sm font-medium focus:ring-2 focus:ring-brand-500" placeholder="Estamos em manutenção para melhorar o portal. Voltaremos em breve."></textarea>
            @error('maintenance_message') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
        </div>
        <button type="button" wire:click="saveMaintenance" class="bg-gray-900 hover:bg-black text-white px-6 py-3 rounded-xl font-black uppercase text-[10px] tracking-widest transition">
            Salvar aviso
        </button>
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
                (de preferência com mídia). O XML <span class="font-bold text-gray-500">não traz os arquivos de foto</span> —
                só os links. Se o WordPress saiu do ar neste domínio, copie a pasta
                <span class="font-bold text-gray-500">wp-content/uploads</span> para o servidor e informe o caminho abaixo.
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
                    if (this.stage === 'import' && this.phase === 'images' && this.total > 0) return 55 + Math.round((this.processed / this.total) * 45);
                    if (this.stage === 'import' && this.total > 0) return 30 + Math.round((this.processed / this.total) * 25);
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
                phase: 'posts',
                async requestBatch(token, skip) {
                    const controller = new AbortController();
                    const timer = setTimeout(() => controller.abort(), 10000);
                    try {
                        const response = await fetch(this.processUrl, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': this.csrf(),
                            },
                            body: JSON.stringify({ token, skip: skip ? 1 : 0 }),
                            signal: controller.signal,
                        });
                        const data = await response.json().catch(() => ({}));
                        if (! response.ok || ! data.ok) {
                            throw new Error(data.message || 'lote falhou');
                        }
                        return data;
                    } finally {
                        clearTimeout(timer);
                    }
                },
                async runBatches(token) {
                    let fails = 0;
                    try {
                        while (true) {
                            try {
                                const data = await this.requestBatch(token, fails >= 2);
                                if (data.busy) {
                                    await new Promise((resolve) => setTimeout(resolve, 300));
                                    continue;
                                }
                                fails = 0;
                                this.processed = data.processed || 0;
                                this.total = data.total || this.total;
                                this.phase = data.phase || this.phase;
                                const verb = this.phase === 'images' ? 'Baixando imagens' : 'Criando posts';
                                this.label = this.total
                                    ? (verb + ' ' + this.processed + ' / ' + this.total)
                                    : 'Importando…';
                                this.errors = data.errors || [];
                                if (data.done) {
                                    this.stage = 'done';
                                    this.summary = data.summary || 'Importação concluída.';
                                    this.label = 'Concluído';
                                    return;
                                }
                            } catch (e) {
                                fails++;
                                if (fails >= 6) {
                                    this.stage = 'idle';
                                    this.error = 'A importação parou neste ponto. Envie o mesmo XML de novo — os posts já feitos não duplicam.';
                                    return;
                                }
                                this.label = fails >= 2
                                    ? ('Post lento — pulando e seguindo… ' + this.processed + ' / ' + this.total)
                                    : ('Post lento, tentando de novo… ' + this.processed + ' / ' + this.total);
                                await new Promise((resolve) => setTimeout(resolve, 400));
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
                    <p class="text-[10px] text-gray-400 font-medium normal-case mt-2">Limite 100 MB. Primeiro entram os textos (rápido); depois as fotos. Se um post travar, ele é pulado. Deixe esta aba aberta.</p>
                </div>

                <label class="flex items-center gap-3 text-[11px] font-bold text-gray-600 normal-case">
                    <input type="checkbox" name="download_images" value="1" checked class="rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                    Trazer imagens (capa e fotos do texto) para o armazenamento do portal
                </label>

                <div>
                    <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Pasta wp-content/uploads no servidor</label>
                    <input type="text" name="wordpress_uploads_path" value="{{ \App\Support\Settings::get('wordpress_uploads_path', '') }}" placeholder="/var/www/rnpet.com.br/wp-content/uploads" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-sm font-mono focus:ring-2 focus:ring-brand-500">
                    <p class="text-[10px] text-gray-400 font-medium normal-case mt-2">Obrigatório se o WordPress não estiver mais no ar neste domínio. Ex.: copie a pasta uploads antiga e cole o caminho aqui.</p>
                </div>

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
