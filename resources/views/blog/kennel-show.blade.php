<x-app-layout>
    {{-- Injetado x-data do Alpine para gerenciar o estado do modal de zoom da foto --}}
    <div class="bg-white pb-24" x-data="{ openModal: false, modalImg: '' }">

        {{-- Banner Grande de Capa --}}
        <div class="w-full h-64 md:h-96 bg-brand-500 relative overflow-hidden">
            @if($kennel->cover_image)
                <img src="{{ asset('storage/' . $kennel->cover_image) }}" class="w-full h-full object-cover opacity-90">
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
        </div>

        {{-- Corpo do Perfil --}}
        <div class="max-w-5xl mx-auto px-6 relative -mt-24 z-30">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">

                {{-- Coluna Esquerda: Informações & Contato --}}
                <div class="lg:col-span-4 bg-white rounded-[2.5rem] border border-gray-100 p-8 shadow-xl shadow-gray-100 text-center">

                    {{-- Logo Grande --}}
                    <div class="w-32 h-32 rounded-full bg-white border-4 border-white shadow-xl overflow-hidden mx-auto -mt-24 mb-6 flex items-center justify-center font-black italic text-3xl text-brand-500">
                        @if($kennel->logo)
                            <img src="{{ asset('storage/' . $kennel->logo) }}" class="w-full h-full object-cover">
                        @else
                            {{ substr($kennel->name, 0, 2) }}
                        @endif
                    </div>

                    <h1 class="text-2xl font-black text-gray-900 uppercase tracking-tight mb-1">{{ $kennel->name }}</h1>
                    @if($kennel->affix)
                        <p class="text-xs font-black text-brand-500 uppercase tracking-widest mb-4">Afixo: {{ $kennel->affix }}</p>
                    @endif

                    @if($kennel->is_verified)
                        <span class="bg-amber-100 text-amber-800 text-[9px] font-black px-4 py-1.5 rounded-full uppercase tracking-widest inline-block mb-6">
                            ★ Criador Verificado
                        </span>
                    @endif

                    {{-- Dados de Registro --}}
                    <div class="bg-gray-50 rounded-2xl p-4 text-left space-y-3 mb-6 text-xs">
                        <div>
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Cidade sede</p>
                            <p class="font-bold text-gray-900 uppercase">{{ $kennel->city }} / {{ $kennel->state }}</p>
                        </div>
                        @if($kennel->registration_number)
                            <div>
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Afiliação oficial</p>
                                <p class="font-bold text-gray-900 font-mono">CBKC / FCI: {{ $kennel->registration_number }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Botões de Ação Direta --}}
                    <div class="space-y-3">
                        @if($kennel->whatsapp)
                            <a href="https://wa.me/55{{ preg_replace('/\D/', '', $kennel->whatsapp) }}" target="_blank" class="w-full flex items-center justify-center gap-2 bg-green-500 text-white py-4 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-green-600 transition shadow-lg shadow-green-100">
                                <i class="fab fa-whatsapp text-sm"></i> Chamar no WhatsApp
                            </a>
                        @endif

                        @if($kennel->instagram)
                            <a href="https://instagram.com/{{ $kennel->instagram }}" target="_blank" class="w-full flex items-center justify-center gap-2 bg-gray-100 text-gray-900 py-4 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-gray-200 transition">
                                <i class="fab fa-instagram text-sm"></i> Seguir Instagram
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Coluna Direita: Descrição, Raças Criadas & Galeria --}}
                <div class="lg:col-span-8 bg-white rounded-[2.5rem] border border-gray-100 p-10 shadow-sm mt-6 lg:mt-0 space-y-12">

                    {{-- Sobre o canil --}}
                    <div>
                        <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6 border-b pb-2">Sobre o Canil</h2>
                        <div class="text-gray-600 text-sm font-medium leading-relaxed whitespace-pre-line">
                            {{ $kennel->description ?? 'Este canil profissional ainda não preencheu o texto descritivo sobre as suas diretrizes de criação e instalações.' }}
                        </div>
                    </div>

                    {{-- Raças Criadas --}}
                    <div>
                        <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6 border-b pb-2">Raças Criadas & Linhagens</h2>
                        <div class="flex flex-wrap gap-2">
                            @forelse(\App\Models\RelatedBreed::where('kennel_id', $kennel->id)->get() as $b)
                                <span class="bg-brand-500 text-white px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider">
                                    🐕 {{ $b->breed_name }}
                                </span>
                            @empty
                                <p class="text-gray-400 italic text-xs">Nenhuma raça vinculada ao perfil.</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- GALERIA DE FOTOS COM RECURSO DE ZOOM INDEPENDENTE --}}
                    @if($kennel->images->count() > 0)
                        <div>
                            <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6 border-b pb-2">Galeria de Fotos do Canil</h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                @foreach($kennel->images as $photo)
                                    @php $url = asset('storage/' . $photo->image_path); @endphp
                                    {{-- Ao clicar, o Alpine captura a URL e altera o estado do modal para verdadeiro --}}
                                    <div @click="modalImg = '{{ $url }}'; openModal = true"
                                         class="aspect-square bg-gray-100 rounded-[1.5rem] overflow-hidden border border-gray-100 shadow-sm hover:shadow-md hover:scale-[1.02] cursor-pointer transition duration-300 group relative">
                                        <img src="{{ $url }}" class="w-full h-full object-cover" alt="Foto do Canil {{ $kennel->name }}">

                                        {{-- Overlay sutil indicando clique --}}
                                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition flex items-center justify-center">
                                            <i class="fas fa-search-plus text-white opacity-0 group-hover:opacity-100 text-xl transition duration-300"></i>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Banner Publicitário --}}
                    <div>
                        <x-ad-space position="post_footer" />
                    </div>
                </div>

            </div>
        </div>

        {{-- MODAL DINÂMICO ALPINE.JS (A foto abre grande aqui) --}}
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
             x-show="openModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @keydown.escape.window="openModal = false"
             @click="openModal = false"
             style="display: none;">

            {{-- Container do Card da Imagem Magnificada --}}
            <div class="relative max-w-4xl w-full bg-white p-3 rounded-[2.5rem] shadow-2xl border border-gray-100 flex flex-col"
                 @click.stop
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="scale-95 opacity-0"
                 x-transition:enter-end="scale-100 opacity-100"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="scale-100 opacity-100"
                 x-transition:leave-end="scale-95 opacity-0">

                {{-- Botão Fechar Flutuante Lateral --}}
                <button @click="openModal = false"
                        class="absolute -top-4 -right-4 bg-white text-gray-800 hover:text-brand-500 w-12 h-12 rounded-full flex items-center justify-center shadow-xl border border-gray-50 text-xl font-bold transition transform hover:scale-110 z-50">
                    &times;
                </button>

                {{-- Exibição da foto --}}
                <div class="w-full aspect-[16/10] md:aspect-video rounded-[2rem] overflow-hidden bg-gray-50">
                    <img :src="modalImg" class="w-full h-full object-cover">
                </div>

                {{-- Legenda do rodapé do modal --}}
                <div class="p-4 pt-5">
                    <h3 class="text-lg font-black text-gray-900 uppercase italic leading-tight">{{ $kennel->name }}</h3>
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">Criação profissional • Atibaia/SP</p>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
