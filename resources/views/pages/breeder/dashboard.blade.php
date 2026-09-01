<x-app-layout>
    <div class="bg-gray-50 min-h-screen py-12">
        <div class="max-w-5xl mx-auto px-6 space-y-8">

            {{-- Painel de Boas-vindas --}}
            <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-brand-500 text-white font-black italic flex items-center justify-center text-xl shadow-md overflow-hidden">
                        @if($kennel->logo)
                            <img src="{{ asset('storage/' . $kennel->logo) }}" class="w-full h-full object-cover">
                        @else
                            {{ substr($kennel->name, 0, 2) }}
                        @endif
                    </div>
                    <div>
                        <span class="text-[9px] font-black text-brand-500 uppercase tracking-widest">Painel do Criador</span>
                        <h1 class="text-2xl font-black text-gray-900 uppercase italic tracking-tight">{{ $kennel->name }}</h1>
                        <p class="text-xs font-medium text-gray-400">{{ $kennel->city }} / {{ $kennel->state }} • Afixo: {{ $kennel->affix ?? 'Nenhum' }}</p>
                    </div>
                </div>

                <div>
                    <a href="{{ route('kennels.show', $kennel->slug) }}" target="_blank" class="bg-gray-900 text-white px-5 py-3 rounded-xl font-black uppercase text-[10px] tracking-widest hover:bg-brand-500 transition inline-block">
                        Ver Meu Perfil Público
                    </a>
                </div>
            </div>

            {{-- Grid Lateral --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

                {{-- Informações Cadastrais --}}
                <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm space-y-4 text-xs font-bold">
                    <h2 class="text-[10px] font-black uppercase text-gray-400 tracking-widest border-b pb-2">Dados Cadastrados</h2>

                    <div>
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-tighter">Status no Portal</p>
                        <span class="mt-1 inline-block px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase {{ $kennel->is_verified ? 'bg-amber-100 text-amber-800' : 'bg-blue-50 text-blue-600' }}">
                            {{ $kennel->is_verified ? '★ Verificado' : 'Aguardando Selo VIP' }}
                        </span>
                    </div>

                    <div>
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-tighter">Registro Oficial</p>
                        <p class="text-gray-900 font-mono mt-0.5">{{ $kennel->registration_number ?? 'Não informado' }}</p>
                    </div>

                    <div>
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-tighter">WhatsApp de Atendimento</p>
                        <p class="text-gray-900 mt-0.5">{{ $kennel->whatsapp }}</p>
                    </div>
                </div>

                {{-- Gerenciador da Galeria (Livewire) --}}
                <div class="lg:col-span-2 bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm space-y-6">
                    <div>
                        <h2 class="text-[10px] font-black uppercase text-gray-400 tracking-widest border-b pb-2">Fotos do Canil & Ninhadas</h2>
                        <p class="text-xs font-medium text-gray-400 mt-1">Gerencie o portfólio visual que os lojistas e veterinários verão no seu perfil.</p>
                    </div>

                    {{-- Chamada do componente Livewire criado no Passo 1 --}}
                    @livewire('breeder.manage-photos')
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
