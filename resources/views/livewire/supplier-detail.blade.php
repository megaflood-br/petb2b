<div class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Breadcrumb - Navegação --}}
        <nav class="flex text-[10px] font-black uppercase tracking-widest text-brand-500 mb-8">
            <a href="{{ route('home') }}" class="hover:text-brand-600">Início</a>
            <span class="mx-2 text-gray-300">/</span>
            <a href="{{ route('suppliers.index') }}" class="hover:text-brand-600">Guia de Fornecedores</a>
            <span class="mx-2 text-gray-300">/</span>
            <span class="text-gray-400">{{ $supplier->name }}</span>
        </nav>

        {{-- Grid Principal --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

            {{-- COLUNA DA ESQUERDA (Agrupa tudo do lado esquerdo para evitar buracos brancos) --}}
            <div class="lg:col-span-2 space-y-8">

                {{-- Card Principal da Empresa --}}
                <div class="bg-white p-8 md:p-12 rounded-[3rem] shadow-sm border border-gray-100">
                    <div class="flex flex-col md:flex-row gap-8 items-center md:items-start mb-10">
                        {{-- Logo ou Sigla --}}
                        <div class="w-40 h-40 bg-white rounded-[2rem] shadow-sm border p-4 flex-shrink-0 flex items-center justify-center">
                            @if($supplier->logo)
                                <img src="{{ asset('storage/' . $supplier->logo) }}" alt="{{ $supplier->name }}" class="w-full h-full object-contain">
                            @else
                                <div class="text-brand-100 font-black text-4xl italic select-none">PBP</div>
                            @endif
                        </div>

                        {{-- Título e Tags --}}
                        <div class="text-center md:text-left">
                            <h1 class="text-4xl md:text-5xl font-black text-gray-900 leading-tight tracking-tighter uppercase italic">
                                {{ $supplier->name }}
                            </h1>
                            <div class="flex flex-wrap justify-center md:justify-start gap-3 mt-4">
                                <span class="bg-brand-50 text-brand-500 px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest">
                                    {{ str_replace('-', ' ', $supplier->category) }}
                                </span>
                                @if($supplier->is_verified)
                                    <span class="bg-amber-50 text-amber-600 px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center shadow-sm">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        Empresa Verificada
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Descrição --}}
                    <div class="prose prose-lg max-w-none text-gray-600 font-medium leading-relaxed">
                        <h3 class="text-xl font-black text-gray-900 uppercase mb-4 tracking-tight border-b pb-2">Sobre a Empresa</h3>
                        @if($supplier->cnpj)
                            <div class="flex items-center gap-2 mt-2 mb-4">
                                <span class="text-[10px] font-black uppercase text-gray-400">CNPJ:</span>
                                <span class="text-[10px] font-mono font-bold text-gray-600">{{ $supplier->cnpj }}</span>
                            </div>
                        @endif
                        <p class="whitespace-pre-line text-sm">{{ $supplier->description ?? 'Nenhuma descrição detalhada disponível.' }}</p>
                    </div>

                    {{-- CORRIGIDO: Exibe se não houver dono OU se você estiver logado como Administrador para auditoria local --}}
                    @if(!$supplier->user_id || (auth()->check() && auth()->user()->role === 'admin'))
                        <div class="mt-8 pt-6 border-t">
                            <p class="text-[9px] font-black text-amber-500 uppercase tracking-widest mb-3 block">ℹ️ Modo de Auditoria: Espaço de Reivindicação</p>
                            @livewire('claim-company', ['supplierId' => $supplier->id])
                        </div>
                    @endif
                </div>

                {{-- Seção de Classificados --}}
                @if(isset($classifieds) && $classifieds->count() > 0)
                    <div class="pt-4">
                        <div class="mb-6">
                            <h3 class="text-2xl font-black text-gray-900 uppercase italic">
                                Produtos e <span class="text-brand-500">Serviços</span>
                            </h3>
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mt-0.5">Catálogo de soluções b2b disponíveis</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($classifieds as $ad)
                                <div class="group bg-white rounded-[2.5rem] border border-gray-100 p-4 shadow-sm hover:shadow-xl transition duration-500 flex flex-col justify-between">
                                    <div>
                                        <div class="aspect-square bg-gray-50 rounded-[2rem] overflow-hidden mb-4 relative">
                                            @if($ad->image)
                                                <img src="{{ asset('storage/' . $ad->image) }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-700">
                                            @endif
                                            <div class="absolute bottom-4 right-4 bg-brand-500 text-white px-4 py-2 rounded-xl font-black text-sm shadow-lg">
                                                R$ {{ number_format($ad->price, 2, ',', '.') }}
                                            </div>
                                        </div>
                                        <div class="px-2">
                                            <h4 class="font-black text-gray-900 uppercase text-base mb-4 line-clamp-2 min-h-[3rem]">{{ $ad->title }}</h4>
                                        </div>
                                    </div>
                                    <div class="px-2 pb-2">
                                        <a href="{{ route('classifieds.show', $ad->slug) }}" class="w-full block text-center bg-gray-950 text-white py-4 rounded-2xl font-black uppercase text-[9px] tracking-[0.2em] hover:bg-brand-500 transition">
                                            Ver Detalhes
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- SIDEBAR (DIREITA) --}}
            <div class="lg:col-span-1 space-y-6">
                {{-- Card de Contatos Diretos --}}
                <div class="bg-gray-950 p-8 rounded-[2.5rem] text-white shadow-xl shadow-brand-100/10">
                    <h4 class="font-black uppercase text-[20px] mb-6 tracking-widest text-brand-400 text-center">Contatos Rápidos</h4>

                    <div class="space-y-4">
                        @if($supplier->whatsapp)
                            @php $whatsappLimpo = preg_replace('/[^0-9]/', '', $supplier->whatsapp); @endphp
                            <a href="https://wa.me/55{{ $whatsappLimpo }}" target="_blank"
                               class="w-full bg-green-600 text-white px-6 py-4 rounded-2xl font-black uppercase text-[10px] tracking-widest flex items-center justify-center gap-3 shadow-lg hover:bg-green-700 transition-all">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 448 512"><path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></svg>
                                WhatsApp Direto
                            </a>
                        @endif

                        @if($supplier->phone)
                            <a href="tel:{{ preg_replace('/[^0-9]/', '', $supplier->phone) }}"
                               class="w-full bg-white/5 text-white px-6 py-4 rounded-2xl font-black uppercase text-[10px] tracking-widest flex items-center justify-center gap-3 hover:bg-white/10 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.72l.54 2.21a1 1 0 01-.24.97l-1.9 1.9a15.59 15.59 0 006.26 6.26l1.9-1.9a1 1 0 01.97-.24l2.21.54a1 1 0 01.72.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                Ligar para Empresa
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Card de Localização --}}
                <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4 italic">Onde estamos</p>
                    <div class="w-full h-64 rounded-2xl overflow-hidden mb-6 border border-gray-100 shadow-inner">
                        @php
                            $fullAddress = "{$supplier->address}, {$supplier->city} - {$supplier->state}, Brasil";
                            $mapUrlStatic = "https://maps.google.com/maps?q=" . urlencode($fullAddress) . "&t=&z=15&ie=UTF8&iwloc=&output=embed";
                        @endphp
                        <iframe width="100%" height="100%" frameborder="0" style="border:0" src="{{ $mapUrlStatic }}" allowfullscreen></iframe>
                    </div>

                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-brand-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span class="text-xs font-bold text-gray-900 leading-tight uppercase">
                            {{ $supplier->address }}<br>
                            {{ $supplier->city }} / {{ $supplier->state }}
                        </span>
                    </div>
                </div>

                {{-- Formulário de Orçamento Livewire --}}
                @livewire('supplier-contact', ['supplier_id' => $supplier->id])
            </div>

        </div>
    </div>
</div>
