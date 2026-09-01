<div class="bg-gray-50 min-h-screen py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- CASO 1: VISUALIZAÇÃO INTERNA DO EVENTO COMPLETO --}}
        @if($selectedEvent)
            <div class="max-w-4xl mx-auto">

                {{-- Botão Voltar Dinâmico --}}
                <div class="mb-8">
                    <button wire:click="clearSelection" class="text-[10px] font-black uppercase tracking-widest text-brand-500 hover:text-brand-600 transition flex items-center gap-2 focus:outline-none">
                        ← Voltar para a Agenda
                    </button>
                </div>

                {{-- Card Master do Evento --}}
                <div class="bg-white rounded-[3.5rem] border border-gray-100 shadow-sm overflow-hidden p-8 md:p-12 space-y-8">

                    {{-- Imagem de Destaque configurada exatamente para 500x500px centralizada --}}
                    @if($selectedEvent->image)
                        <div class="w-[500px] h-[500px] rounded-[2.5rem] overflow-hidden border bg-gray-100 shadow-inner mx-auto max-w-full">
                            <img src="{{ asset('storage/' . $selectedEvent->image) }}" class="w-full h-full object-cover">
                        </div>
                    @endif

                    {{-- Cabeçalho Principal --}}
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 border-b pb-8 border-gray-100">
                        <div class="space-y-2">
                            <span class="bg-brand-50 text-brand-600 px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest">
                                📅 Evento Oficial do Setor
                            </span>
                            <h1 class="text-3xl md:text-4xl font-black text-gray-900 uppercase italic tracking-tight leading-none">
                                {{ $selectedEvent->title }}
                            </h1>
                            <p class="text-sm font-bold text-gray-400 uppercase tracking-wider">
                                {{ $selectedEvent->city }} / {{ $selectedEvent->state }}
                            </p>
                        </div>

                        {{-- Bloco de Data Lateral --}}
                        <div class="flex items-center gap-4 bg-gray-50 p-4 rounded-2xl border border-gray-100 font-bold shrink-0">
                            <div class="w-12 h-12 bg-brand-500 rounded-xl flex flex-col items-center justify-center text-white font-black">
                                <span class="text-[9px] uppercase leading-none mb-0.5">{{ $selectedEvent->start_date ? $selectedEvent->start_date->translatedFormat('M') : '--' }}</span>
                                <span class="text-lg leading-none">{{ $selectedEvent->start_date ? $selectedEvent->start_date->format('d') : '--' }}</span>
                            </div>
                            <div class="text-xs uppercase tracking-tight text-gray-700 font-bold">
                                <p class="text-gray-400 text-[9px] font-black">Data de Início</p>
                                <p class="font-mono text-gray-900 font-black">{{ $selectedEvent->start_date ? $selectedEvent->start_date->format('d/m/Y') : '---' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Localização e Período --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 font-bold text-xs uppercase text-gray-700 tracking-tight">
                        <div class="bg-gray-50 p-6 rounded-2xl border flex gap-4 items-start">
                            <div class="p-3 bg-white rounded-xl text-brand-500 shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                            </div>
                            <div>
                                <p class="text-[9px] text-gray-400 font-black">Local de Realização</p>
                                <p class="text-gray-900 font-black mt-1 normal-case text-sm">{{ $selectedEvent->location }}</p>
                                <p class="text-gray-400 text-[10px] font-bold mt-0.5">{{ $selectedEvent->city }} - {{ $selectedEvent->state }}</p>
                            </div>
                        </div>

                        @if($selectedEvent->end_date)
                            <div class="bg-gray-50 p-6 rounded-2xl border flex gap-4 items-start">
                                <div class="p-3 bg-white rounded-xl text-brand-500 shadow-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                                <div>
                                    <p class="text-[9px] text-gray-400 font-black">Período Completo</p>
                                    <p class="text-gray-900 font-black mt-1 text-sm font-mono">Até {{ $selectedEvent->end_date->format('d/m/Y') }}</p>
                                    <p class="text-gray-400 text-[10px] font-bold mt-0.5">Confira o cronograma oficial</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Conteúdo / Descrição Detalhada --}}
                    <div class="space-y-3 font-medium text-gray-600 text-sm leading-relaxed normal-case border-t pt-8">
                        <h3 class="text-base font-black text-gray-900 uppercase italic tracking-tight">Sobre o Evento / Informações Gerais</h3>
                        <p class="whitespace-pre-line">{{ $selectedEvent->description ?? 'Nenhuma descrição complementar cadastrada para este evento.' }}</p>
                    </div>

                    {{-- Link Externo Oficial --}}
                    @if($selectedEvent->external_link)
                        <div class="pt-6 border-t">
                            <a href="{{ $selectedEvent->external_link }}" target="_blank" rel="noopener noreferrer"
                               class="w-full block text-center bg-gray-950 hover:bg-brand-500 text-white py-5 rounded-2xl font-black uppercase text-xs tracking-widest transition shadow-lg shadow-gray-200">
                                Acessar Inscrições e Site Oficial ↗
                            </a>
                        </div>
                    @endif

                </div>
            </div>

        {{-- CASO 2: LISTAGEM GERAL EM GRADE --}}
        @else
            <div class="text-center mb-16">
                <h1 class="text-5xl font-black text-gray-900 uppercase tracking-tighter italic">
                    Agenda <span class="text-brand-500">Pet 2026</span>
                </h1>
                <p class="text-gray-500 font-medium mt-4 uppercase tracking-widest text-sm">As principais feiras e congressos do setor no Brasil.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($events as $event)
                    {{-- CORRIGIDO: Passando o slug para o método de seleção --}}
                    <div wire:click="selectEvent('{{ $event->slug }}')" class="bg-white rounded-[3rem] border border-gray-100 shadow-sm flex flex-col items-center text-center group hover:shadow-xl transition-all overflow-hidden relative cursor-pointer">

                        {{-- Banner do Evento (Fundo do Calendário) --}}
                        <div class="w-full h-32 bg-gray-100 relative overflow-hidden shrink-0">
                            @if($event->image)
                                <img src="{{ asset('storage/' . $event->image) }}"
                                     class="w-full h-full object-cover group-hover:scale-110 transition duration-500 opacity-60">
                            @else
                                <div class="w-full h-full bg-brand-50 flex items-center justify-center text-brand-200">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                            @endif
                        </div>

                        {{-- Calendário Estilizado --}}
                        <div class="-mt-12 w-20 h-24 bg-white rounded-2xl flex flex-col overflow-hidden mb-6 shadow-lg z-10 group-hover:bg-brand-500 transition-colors">
                            <div class="bg-brand-50 py-2 text-[10px] font-black text-brand-600 uppercase group-hover:bg-brand-600 group-hover:text-white transition-colors">
                                {{ $event->start_date ? $event->start_date->translatedFormat('M') : '---' }}
                            </div>
                            <div class="flex-1 flex items-center justify-center text-3xl font-black text-brand-900 group-hover:text-white transition-colors">
                                {{ $event->start_date ? $event->start_date->format('d') : '--' }}
                            </div>
                        </div>

                        <div class="px-8 pb-8 flex flex-col flex-1 w-full">
                            <h3 class="text-xl font-black text-gray-900 uppercase tracking-tight leading-tight mb-2 group-hover:text-brand-500 transition-colors">
                                {{ $event->title }}
                            </h3>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-6">
                                {{ $event->city }} / {{ $event->state }}
                            </p>

                            <div class="mt-auto pt-6 border-t border-gray-50 w-full space-y-4 font-bold text-xs">
                                <div class="flex items-center justify-center gap-2 text-[10px] font-black text-gray-500 uppercase italic normal-case">
                                    <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                                    {{ $event->location }}
                                </div>

                                <div class="w-full py-4 bg-gray-950 text-white rounded-2xl font-black uppercase text-[10px] tracking-widest group-hover:bg-brand-500 transition-all shadow-md">
                                    Ver Detalhes do Evento
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>
</div>
