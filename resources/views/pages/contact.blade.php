<x-app-layout>
    <div class="bg-white py-24">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-20">
                {{-- Info de Contato --}}
                <div>
                    <h1 class="text-4xl md:text-6xl font-black text-gray-900 uppercase italic tracking-tighter mb-8 leading-none">
                        Vamos <span class="text-indigo-600">Conversar?</span>
                    </h1>
                    <p class="text-gray-500 font-medium text-lg mb-12">Dúvidas sobre anúncios, sugestões de pauta para a revista ou suporte técnico? Nossa equipe está pronta para te atender.</p>

                    <div class="space-y-8">
                        <div class="flex items-center gap-6">
                            <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">E-mail Direto</p>
                                <p class="font-bold text-gray-900">contato@petbusinesspro.com.br</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Componente Livewire --}}
                <div class="bg-gray-50 p-8 md:p-12 rounded-[3rem]">
                    @livewire('contact-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
