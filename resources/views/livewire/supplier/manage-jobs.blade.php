<div class="space-y-8">

    @if (session()->has('message'))
        <div class="bg-green-50 p-4 rounded-xl border border-green-100 text-green-600 uppercase text-[10px] font-black">
            {{ session('message') }}
        </div>
    @endif

    {{-- Cabeçalho --}}
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 border-b pb-6">
        <div>
            <h1 class="text-2xl font-black text-gray-900 uppercase italic tracking-tight">Vagas de Emprego</h1>
            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mt-0.5">Publique oportunidades da sua empresa no guia de empregos do portal</p>
        </div>
        <button wire:click="toggleForm" class="bg-brand-500 hover:bg-brand-600 text-white px-6 py-3.5 rounded-xl font-black uppercase text-[10px] tracking-wider transition shadow-md shadow-brand-500/10">
            {{ $showForm ? 'Fechar' : '+ Nova Vaga' }}
        </button>
    </div>

    {{-- Formulário --}}
    @if($showForm)
        <form wire:submit.prevent="save" class="bg-white border border-gray-100 rounded-[2rem] p-8 shadow-sm space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Título da Vaga</label>
                    <input type="text" wire:model="title" placeholder="Ex: Vendedor(a) Técnico Pet" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-gray-900 focus:ring-2 focus:ring-brand-500">
                    @error('title') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Tipo de Contratação</label>
                    <select wire:model="type" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-gray-900 focus:ring-2 focus:ring-brand-500">
                        @foreach($types as $t)
                            <option value="{{ $t }}">{{ $t }}</option>
                        @endforeach
                    </select>
                    @error('type') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Faixa Salarial (opcional)</label>
                    <input type="text" wire:model="salary" placeholder="Ex: R$ 2.500 + comissão / A combinar" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-gray-900 focus:ring-2 focus:ring-brand-500">
                    @error('salary') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Cidade</label>
                    <input type="text" wire:model="city" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-gray-900 focus:ring-2 focus:ring-brand-500">
                    @error('city') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Estado (UF)</label>
                    <input type="text" wire:model="state" maxlength="2" placeholder="SP" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-gray-900 focus:ring-2 focus:ring-brand-500 uppercase">
                    @error('state') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Descrição / Requisitos</label>
                    <textarea wire:model="description" rows="5" placeholder="Descreva a vaga, responsabilidades e requisitos." class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-gray-900 focus:ring-2 focus:ring-brand-500"></textarea>
                    @error('description') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Como se candidatar (e-mail, WhatsApp ou link)</label>
                    <input type="text" wire:model="how_to_apply" placeholder="Ex: rh@empresa.com | https://wa.me/5511999999999" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-gray-900 focus:ring-2 focus:ring-brand-500">
                    @error('how_to_apply') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex gap-3 pt-4 border-t border-gray-100">
                <button type="button" wire:click="toggleForm" class="w-1/3 bg-gray-100 text-gray-700 p-4 rounded-xl font-black uppercase tracking-widest hover:bg-gray-200 transition">Cancelar</button>
                <button type="submit" class="flex-1 bg-brand-500 text-white p-4 rounded-xl font-black uppercase tracking-widest hover:bg-brand-600 transition">
                    {{ $editingId ? 'Salvar Alterações' : 'Publicar Vaga' }}
                </button>
            </div>
        </form>
    @endif

    {{-- Lista de vagas --}}
    <div class="space-y-4">
        @forelse($jobs as $job)
            <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="text-sm font-black text-gray-900 uppercase truncate">{{ $job->title }}</h3>
                        <span class="text-[8px] font-black uppercase px-2 py-0.5 rounded-full {{ $job->is_active ? 'bg-green-500/10 text-green-600' : 'bg-gray-200 text-gray-500' }}">
                            {{ $job->is_active ? 'Ativa' : 'Pausada' }}
                        </span>
                    </div>
                    <p class="text-[10px] text-gray-500 font-mono mt-1">
                        {{ $job->type }} · {{ $job->city ?: '—' }}/{{ $job->state ?: '—' }}{{ $job->salary ? ' · ' . $job->salary : '' }}
                    </p>
                </div>
                <div class="flex gap-2 shrink-0 flex-wrap justify-end">
                    <button wire:click="viewApplications({{ $job->id }})" class="text-[9px] font-black uppercase tracking-wider px-3 py-2 rounded-lg bg-brand-50 text-brand-600 hover:bg-brand-100 transition">
                        Candidaturas ({{ $job->applications_count }})
                    </button>
                    <button wire:click="toggleStatus({{ $job->id }})" class="text-[9px] font-black uppercase tracking-wider px-3 py-2 rounded-lg {{ $job->is_active ? 'bg-amber-50 text-amber-600 hover:bg-amber-100' : 'bg-green-50 text-green-600 hover:bg-green-100' }} transition">
                        {{ $job->is_active ? 'Pausar' : 'Ativar' }}
                    </button>
                    <button wire:click="edit({{ $job->id }})" class="text-[9px] font-black uppercase tracking-wider px-3 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition">Editar</button>
                    <button wire:click="delete({{ $job->id }})" wire:confirm="Remover esta vaga?" class="text-[9px] font-black uppercase tracking-wider px-3 py-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition">Excluir</button>
                </div>
            </div>
        @empty
            <div class="py-16 text-center bg-white rounded-2xl border border-dashed border-gray-200">
                <p class="text-gray-400 font-bold uppercase tracking-widest text-xs">Você ainda não publicou nenhuma vaga.</p>
            </div>
        @endforelse
    </div>

    {{-- Painel de candidaturas --}}
    @if($selectedJobId)
        <div class="fixed inset-0 bg-gray-950/40 backdrop-blur-sm flex items-center justify-center z-50 p-4" wire:click.self="closeApplications">
            <div class="bg-white rounded-[2rem] border shadow-2xl max-w-3xl w-full max-h-[85vh] overflow-y-auto p-8 space-y-4">
                <div class="flex justify-between items-center border-b pb-4">
                    <h2 class="text-lg font-black text-gray-900 uppercase italic tracking-tight">Candidaturas recebidas</h2>
                    <button wire:click="closeApplications" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                @forelse($applications as $app)
                    <div class="bg-gray-50 border border-gray-100 rounded-2xl p-5">
                        <div class="flex justify-between items-start gap-3">
                            <div class="min-w-0">
                                <p class="font-black text-gray-900 text-sm">{{ $app->name }}</p>
                                <p class="text-xs text-gray-500 font-medium">{{ $app->email }}{{ $app->phone ? ' · ' . $app->phone : '' }}</p>
                            </div>
                            <span class="text-[9px] text-gray-400 font-mono shrink-0">{{ $app->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        @if($app->message)
                            <p class="text-xs text-gray-700 mt-3 whitespace-pre-line">{{ $app->message }}</p>
                        @endif
                        @if($app->resume_path)
                            <a href="{{ asset('storage/' . $app->resume_path) }}" target="_blank" rel="noopener" class="inline-block mt-3 text-[10px] font-black uppercase tracking-wider text-brand-600 hover:underline">↓ Baixar currículo</a>
                        @endif
                    </div>
                @empty
                    <p class="text-center text-gray-400 font-bold uppercase tracking-widest text-xs py-10">Nenhuma candidatura para esta vaga ainda.</p>
                @endforelse
            </div>
        </div>
    @endif
</div>
