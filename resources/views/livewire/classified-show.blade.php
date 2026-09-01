<div class="max-w-6xl mx-auto py-12 px-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-12">

        {{-- Coluna da Foto --}}
        <div>
            <div class="bg-white p-4 rounded-[3rem] border border-gray-100 shadow-xl overflow-hidden">
                @if($ad->image)
                    <img src="{{ asset('storage/' . $ad->image) }}" class="w-full h-auto rounded-[2.5rem] object-cover">
                @else
                    <div class="aspect-square bg-gray-50 flex items-center justify-center rounded-[2.5rem]">
                        <svg class="w-20 h-20 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                @endif
            </div>
        </div>

        {{-- Coluna de Informações --}}
        <div class="flex flex-col justify-center">
            <nav class="text-[10px] font-black text-indigo-500 uppercase tracking-[0.3em] mb-4">
                <a href="{{ route('home') }}">Início</a> >
                <a href="{{ route('classifieds.index') }}">Classificados</a> >
                {{ $ad->category }}
            </nav>

            <h1 class="text-5xl font-black text-gray-900 uppercase italic leading-tight mb-6">{{ $ad->title }}</h1>

            <div class="flex items-center gap-4 mb-8">
                <div class="text-3xl font-black text-green-600">
                    R$ {{ number_format($ad->price, 2, ',', '.') }}
                </div>
                <div class="bg-gray-100 px-4 py-1 rounded-full text-[10px] font-black uppercase text-gray-500">Oportunidade</div>
            </div>

            <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm mb-8">
                <h3 class="font-black text-gray-900 uppercase text-xs mb-4 tracking-widest">Especificações</h3>
                <p class="text-gray-600 font-medium leading-relaxed italic">"{{ $ad->description }}"</p>
            </div>

            {{-- Botão WhatsApp - Ajustado para usar o campo whatsapp se disponível --}}
            @php
                $targetPhone = $ad->supplier->whatsapp ?: $ad->supplier->phone;
                $phoneClean = preg_replace('/[^0-9]/', '', $targetPhone);
                $message = urlencode("Olá! Vi seu anúncio de '{$ad->title}' no Pet Business Pro e gostaria de mais informações.");
            @endphp

            <a href="https://wa.me/{{ $phoneClean }}?text={{ $message }}" target="_blank"
               class="flex items-center justify-center gap-3 bg-green-500 text-white p-6 rounded-2xl font-black uppercase tracking-widest hover:bg-green-600 hover:scale-[1.02] transition-all shadow-lg shadow-green-200">
                <i class="fab fa-whatsapp text-2xl"></i>
                Conversar com Anunciante
            </a>

            {{-- Link para o Perfil do Fornecedor --}}
            <div class="mt-8 text-center p-6 bg-gray-50 rounded-[2rem] border border-dashed border-gray-200">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Publicado por</p>
                <a href="{{ route('suppliers.show', $ad->supplier->slug) }}" class="group">
                    <p class="font-black text-gray-900 uppercase text-lg group-hover:text-indigo-600 transition">{{ $ad->supplier->name }}</p>
                    <span class="text-[9px] font-bold text-indigo-500 uppercase tracking-widest border-b border-indigo-200 group-hover:border-indigo-600">Ver Perfil Completo</span>
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Seção: Mais do mesmo Anunciante --}}
@if(isset($otherAds) && $otherAds->count() > 0)
    <div class="max-w-6xl mx-auto mt-24 border-t border-gray-100 pt-16 px-4">
        <div class="flex justify-between items-end mb-10">
            <div>
                <h3 class="text-2xl font-black text-gray-900 uppercase italic leading-none">
                    Mais da <span class="text-indigo-600">{{ $ad->supplier->name }}</span>
                </h3>
                <p class="text-gray-500 text-sm font-medium mt-2">Aproveite outras oportunidades deste parceiro</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($otherAds as $item)
                {{-- Link ajustado para usar SLUG --}}
                <a href="{{ route('classifieds.show', $item->slug) }}" class="group bg-white rounded-[2.5rem] border border-gray-100 p-4 shadow-sm hover:shadow-xl transition duration-500">
                    <div class="aspect-square bg-gray-50 rounded-[2rem] overflow-hidden mb-6 relative">
                        @if($item->image)
                            <img src="{{ asset('storage/' . $item->image) }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-700">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-200">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                        @endif
                        <div class="absolute bottom-3 right-3 bg-white/90 backdrop-blur px-3 py-1 rounded-lg font-black text-[10px] text-gray-900 shadow-sm">
                            R$ {{ number_format($item->price, 2, ',', '.') }}
                        </div>
                    </div>
                    <div class="px-2 pb-2">
                        <h4 class="font-black text-gray-900 uppercase text-sm leading-tight mb-1 group-hover:text-indigo-600 transition">{{ $item->title }}</h4>
                        <p class="text-[10px] font-bold text-indigo-500 uppercase tracking-widest">{{ $item->category }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
@endif
