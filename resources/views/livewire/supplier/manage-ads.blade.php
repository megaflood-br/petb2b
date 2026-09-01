<div class="space-y-10 text-xs font-bold">

    {{-- Mensagens de Feedback e Alertas --}}
    @if (session()->has('message'))
        <div class="bg-green-50 p-4 rounded-xl border border-green-100 text-green-600 uppercase text-[10px]">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-50 p-4 rounded-xl border border-red-100 text-red-600 uppercase text-[10px]">
            {{ session('error') }}
        </div>
    @endif

    {{-- Cabeçalho Superior com Botão de Cadastro --}}
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 border-b pb-6">
        <div>
            <h3 class="text-xl font-black text-gray-900 uppercase italic tracking-tight">Performance dos meus Anúncios</h3>
            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mt-0.5">Acompanhe cliques, visualizações reais e gerencie seus banners criativos</p>
        </div>
        <div>
            <button wire:click="openCreateModal" class="w-full sm:w-auto bg-brand-500 hover:bg-brand-600 text-white px-6 py-4 rounded-xl font-black uppercase text-[10px] tracking-wider transition shadow-md shadow-brand-500/10 flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Criar Nova Campanha
            </button>
        </div>
    </div>

    {{-- Grid Principal do Painel --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

        {{-- Coluna do Lado Esquerdo: Finanças e Preços --}}
        <div class="space-y-6 lg:col-span-1">

            {{-- Card de Saldo Comercial --}}
            <div class="bg-gray-950 text-white p-8 rounded-[2.5rem] shadow-xl border border-gray-900 relative overflow-hidden">
                <div class="text-center">
                    <p class="text-[9px] font-black text-brand-400 uppercase tracking-[0.2em] mb-1">Créditos Disponíveis</p>
                    <p class="text-4xl font-black italic tracking-tight text-white">R$ {{ number_format($supplier->credit_balance, 2, ',', '.') }}</p>

                    <div class="mt-3 mb-6">
                        <span class="inline-block px-3 py-1 rounded-full text-[9px] font-black uppercase {{ $supplier->credit_balance > 0 ? 'bg-green-500/10 text-green-400' : 'bg-red-500/10 text-red-400' }}">
                            {{ $supplier->credit_balance > 0 ? '● Campanhas Ativas' : '⚠️ Saldo Zerado (Anúncios Pausados)' }}
                        </span>
                    </div>
                </div>

                {{-- Input e Botão de Inserção de Crédito --}}
                <div class="pt-5 border-t border-gray-900 space-y-3">
                    <p class="text-[9px] font-black uppercase text-gray-400 tracking-wider">Adicionar Crédito Comercial</p>
                    <form wire:submit.prevent="addCredits" class="flex gap-2">
                        <div class="relative flex-1">
                            <span class="absolute left-3 top-3.5 text-gray-500 font-mono text-[10px]">R$</span>
                            <input type="number" wire:model="amount" placeholder="50" min="10" step="1" class="w-full bg-gray-900 border-gray-800 rounded-xl p-3.5 pl-8 text-white focus:ring-2 focus:ring-brand-500 text-xs font-mono font-bold">
                        </div>
                        <button type="submit" class="bg-brand-500 hover:bg-brand-600 text-white px-4 rounded-xl font-black uppercase text-[10px] tracking-wider transition shrink-0">
                            Colocar Saldo
                        </button>
                    </form>
                    @error('amount') <span class="text-red-400 text-[10px] block font-medium normal-case">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Histórico de Consumo --}}
            <div class="bg-white p-6 rounded-[2.5rem] border border-gray-100 shadow-sm space-y-4">
                <h3 class="text-[10px] font-black uppercase text-gray-400 tracking-widest border-b pb-2">Extrato de Consumo</h3>

                <div class="space-y-3 max-h-[200px] overflow-y-auto pr-1">
                    @forelse($transactions as $tx)
                        <div class="flex justify-between items-center bg-gray-50 p-3 rounded-xl border border-gray-100">
                            <div class="max-w-[70%]">
                                <p class="text-[10px] text-gray-880 font-black truncate">{{ $tx->description }}</p>
                                <span class="text-[8px] text-gray-400 font-mono block">{{ $tx->created_at->format('d/m H:i') }}</span>
                            </div>
                            <span class="font-mono text-[10px] font-black shrink-0 {{ $tx->type == 'deposit' ? 'text-green-600' : 'text-red-500' }}">
                                {{ $tx->type == 'deposit' ? '+' : '-' }} R${{ number_format($tx->amount, 2, ',', '.') }}
                            </span>
                        </div>
                    @empty
                        <p class="text-gray-400 text-center py-4 font-medium">Nenhuma movimentação de crédito.</p>
                    @endforelse
                </div>
            </div>

            {{-- Tabela Informativa de Preços --}}
            <div class="bg-white p-6 rounded-[2.5rem] border border-gray-100 shadow-sm space-y-4">
                <div class="border-b pb-2">
                    <h3 class="text-[10px] font-black uppercase text-gray-900 tracking-widest">Tabela de Taxas do Portal</h3>
                </div>
                <div class="overflow-hidden rounded-xl border border-gray-100 font-mono text-[10px]">
                    <div class="bg-gray-950 text-white p-2.5 grid grid-cols-3 text-center uppercase tracking-wider font-sans font-black text-[8px]">
                        <div class="text-left pl-1">Formato</div>
                        <div>Por Clique</div>
                        <div>Por View</div>
                    </div>
                    <div class="divide-y divide-gray-50 bg-gray-50/50">
                        <div class="p-3 grid grid-cols-3 text-center items-center">
                            <div class="text-left font-sans font-bold text-gray-900 uppercase text-[9px]">Banners Globais</div>
                            <div class="text-gray-900 font-black">R$ {{ number_format((float) env('ADS_COST_PER_CLICK', 0.50), 2, ',', '.') }}</div>
                            <div class="text-brand-600 font-black">R$ {{ number_format((float) env('ADS_COST_PER_IMPRESSION', 0.0070), 3, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
                <span class="text-[8px] text-gray-400 font-medium block leading-normal normal-case pt-1">
                    * O custo por View equivale a R$ {{ number_format((float) env('ADS_COST_PER_IMPRESSION', 0.0070) * 1000, 2, ',', '.') }} a cada 1.000 exibições do banner.
                </span>
            </div>
        </div>

        {{-- Coluna do Lado Direito: Listagem dos Banners do Fornecedor --}}
        <div class="lg:col-span-2">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @forelse($myAds as $ad)
                    <div class="border border-gray-100 bg-white p-5 rounded-[2.5rem] shadow-sm flex flex-col justify-between">
                        <div class="space-y-4">
                            <div class="h-32 w-full bg-gray-50 rounded-2xl overflow-hidden border relative">
                                <img src="{{ asset('storage/' . $ad->image_path) }}" class="w-full h-full object-cover">

                                {{-- Controles do Criativo --}}
                                <div class="absolute top-2 right-2 flex gap-1.5 items-center">
                                    <button wire:click="editAd({{ $ad->id }})" class="bg-gray-950/80 hover:bg-brand-500 backdrop-blur-sm text-white p-1.5 rounded-full transition shadow-sm" title="Editar informações do banner">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                        </svg>
                                    </button>

                                    <button wire:click="toggleStatus({{ $ad->id }})" class="backdrop-blur-sm text-white p-1.5 rounded-full transition shadow-sm {{ $ad->is_active ? 'bg-amber-500/90 hover:bg-amber-600' : 'bg-green-600/90 hover:bg-green-700' }}" title="{{ $ad->is_active ? 'Pausar Exibição' : 'Retomar Exibição' }}">
                                        @if($ad->is_active)
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                                        @else
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                        @endif
                                    </button>

                                    <button wire:click="deleteAd({{ $ad->id }})" wire:confirm="Deseja remover este banner permanentemente?" class="bg-red-600/90 hover:bg-red-700 backdrop-blur-sm text-white p-1.5 rounded-full transition shadow-sm" title="Excluir campanha">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.100 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <h4 class="text-sm font-black text-gray-900 uppercase truncate">{{ $ad->title }}</h4>
                                {{-- CORRIGIDO: Agora cruza a chave com o array comercial de posições unificadas da Model --}}
                                <p class="text-[9px] text-gray-500 font-mono mt-0.5">Posição: {{ strtoupper(\App\Models\Advertisement::getPositions()[$ad->position] ?? str_replace('_', ' ', $ad->position)) }}</p>
                            </div>
                        </div>

                        {{-- Métricas de Desconto --}}
                        <div class="grid grid-cols-2 gap-3 pt-4 border-t border-gray-200/60 mt-4 text-center">
                            <div class="bg-gray-50 p-2.5 rounded-xl border">
                                <span class="text-[8px] font-black uppercase text-gray-400 block">Visualizações (Views)</span>
                                <p class="text-base font-black text-gray-900 font-mono mt-0.5">{{ $ad->views ?? 0 }}</p>
                            </div>
                            <div class="bg-gray-50 p-2.5 rounded-xl border">
                                <span class="text-[8px] font-black uppercase text-gray-400 block">Cliques Computados</span>
                                <p class="text-base font-black text-gray-900 font-mono mt-0.5">{{ $ad->clicks ?? 0 }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-16 text-center bg-white rounded-[2.5rem] border border-dashed border-gray-200 p-8">
                        <p class="text-gray-400 font-bold uppercase tracking-widest">Você ainda não tem nenhuma campanha de banner cadastrada.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- MODAL FLUTUANTE DE CADASTRO E EDIÇÃO DE CAMPANHAS --}}
    @if($isModalOpen)
        <div class="fixed inset-0 bg-gray-950/40 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-[2.5rem] border shadow-2xl max-w-2xl w-full p-8 space-y-6 relative animate-in fade-in zoom-in-95 duration-150">

                {{-- Cabeçalho do Modal --}}
                <div class="flex justify-between items-start border-b pb-4">
                    <div>
                        <h2 class="text-xl font-black text-gray-900 uppercase italic tracking-tight">
                            {{ $isEditing ? 'Editar Parâmetros da Campanha' : 'Criar Nova Campanha de Banner' }}
                        </h2>
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mt-0.5">
                            {{ $isEditing ? 'Atualize as informações do anúncio corporativo' : 'Configure o visual e o link do seu anúncio rotativo' }}
                        </p>
                    </div>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Corpo do Formulário --}}
                <form wire:submit.prevent="{{ $isEditing ? 'updateAd' : 'createAd' }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Título Interno da Campanha</label>
                            <input type="text" wire:model="title" placeholder="Ex: Lançamento Linha K'enzza" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-gray-900 focus:ring-2 focus:ring-brand-500">
                            @error('title') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Link de Destino (URL ou WhatsApp)</label>
                            <input type="text" wire:model="link" placeholder="https://wa.me/551199999999" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-gray-900 focus:ring-2 focus:ring-brand-500">
                            @error('link') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Posição de Exibição do Banner</label>
                            <select wire:model="position" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-gray-900 focus:ring-2 focus:ring-brand-500">
                                <option value="">Selecione a posição...</option>
                                {{-- CORRIGIDO: Loop dinâmico lendo as chaves comerciais e incluindo a nova opção mobile nativa --}}
                                @foreach(\App\Models\Advertisement::getPositions() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('position') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">
                                Upload do Banner Digital {!! $isEditing ? '<span class="normal-case text-gray-400 font-medium">(Deixe vazio para manter o atual)</span>' : '' !!}
                            </label>
                            <input type="file" wire:model="image" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-brand-50 file:text-brand-700">
                            @error('image') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Ações do Modal --}}
                    <div class="flex gap-3 pt-4 border-t border-gray-100">
                        <button type="button" wire:click="closeModal" class="w-1/3 bg-gray-100 text-gray-700 p-4 rounded-xl font-black uppercase tracking-widest hover:bg-gray-200 transition">
                            Fechar
                        </button>
                        <button type="submit" class="flex-1 bg-brand-500 text-white p-4 rounded-xl font-black uppercase tracking-widest hover:bg-brand-600 transition shadow-md shadow-brand-500/10">
                            {{ $isEditing ? 'Salvar Alterações' : 'Colocar na Fila de Exibição' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
