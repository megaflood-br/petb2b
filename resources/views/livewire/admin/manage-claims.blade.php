<div class="p-8 bg-gray-50 min-h-screen">
    <div class="max-w-6xl mx-auto">
        <header class="mb-10">
            <h1 class="text-3xl font-black text-gray-900 uppercase italic">Solicitações de <span class="text-indigo-600">Propriedade</span></h1>
            <p class="text-gray-500 font-medium italic">Gerencie quem são os donos das empresas no portal Pet Business Pro.</p>
        </header>

        @if (session()->has('message'))
            <div class="bg-green-100 text-green-700 p-4 rounded-2xl mb-6 font-bold text-xs uppercase shadow-sm border border-green-200">
                {{ session('message') }}
            </div>
        @endif

        <div class="space-y-4">
            @forelse($claims as $claim)
                <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm flex flex-col md:flex-row justify-between items-center gap-6 hover:shadow-md transition">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="bg-indigo-50 text-indigo-600 px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest">Pendente</span>
                            <span class="text-gray-400 text-[10px] font-bold uppercase">{{ $claim->created_at->format('d/m/Y H:i') }}</span>
                        </div>

                        <h3 class="text-xl font-black text-gray-900 uppercase mb-1">{{ $claim->supplier->name }}</h3>
                        <p class="text-sm font-bold text-gray-500 mb-4 italic">Usuário: {{ $claim->user->name }} ({{ $claim->user->email }})</p>

                        <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                            <p class="text-xs text-gray-600 leading-relaxed font-medium italic">"{{ $claim->message }}"</p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2 w-full md:w-auto">
                        <button wire:click="approve({{ $claim->id }})" class="bg-indigo-600 text-white px-8 py-3 rounded-xl font-black uppercase text-[10px] tracking-widest hover:bg-indigo-700 transition shadow-lg">
                            Aprovar Dono
                        </button>
                        <button wire:click="reject({{ $claim->id }})" class="text-gray-400 hover:text-red-500 text-[10px] font-black uppercase transition p-2">
                            Rejeitar
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-center py-20 bg-white rounded-[3rem] border-2 border-dashed border-gray-200">
                    <p class="text-gray-400 font-black uppercase tracking-widest">Nenhuma solicitação pendente no momento.</p>
                </div>
            @endforelse

            <div class="mt-8">
                {{ $claims->links() }}
            </div>
        </div>
    </div>
</div>
