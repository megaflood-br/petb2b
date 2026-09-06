@props(['position', 'variant' => 'leaderboard'])

@php
    $ad = \App\Models\Advertisement::pickRandom($position);
    $houseUrl = route('advertise');
@endphp

@if($variant === 'mobile')
    <div x-data="{ showStickyAd: true }"
         x-show="showStickyAd"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-y-full"
         x-transition:enter-end="translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-y-0"
         x-transition:leave-end="translate-y-full"
         class="fixed bottom-0 left-0 right-0 z-[55] sm:hidden bg-white/95 backdrop-blur border-t border-gray-100 shadow-[0_-8px_30px_rgb(0,0,0,0.08)] p-2 flex flex-col items-center justify-center"
         style="display: none;">
        <button @click="showStickyAd = false"
                class="absolute -top-7 right-3 bg-white/90 backdrop-blur text-gray-500 hover:text-gray-800 border border-gray-100 rounded-t-xl px-3 py-1 text-[10px] font-black uppercase tracking-widest transition shadow-sm focus:outline-none">
            ✕ Fechar
        </button>
        <div class="w-full max-w-[400px] aspect-[320/50] rounded-xl overflow-hidden bg-gray-50 border border-gray-100">
            @if($ad)
                <a href="{{ route('ads.redirect', $ad->id) }}" target="_blank" rel="noopener noreferrer" class="block w-full h-full">
                    <img src="{{ asset('storage/' . $ad->image_path) }}"
                         alt="{{ $ad->title }}"
                         class="w-full h-full object-cover"
                         title="Patrocinado: {{ $ad->title }}">
                </a>
            @else
                <a href="{{ $houseUrl }}" class="flex w-full h-full items-center justify-center bg-brand-50 text-brand-600 font-black uppercase text-[10px] tracking-widest">
                    Anuncie aqui
                </a>
            @endif
        </div>
    </div>
@elseif($variant === 'sidebar')
    @if($ad)
        <div class="bg-white p-5 rounded-[3rem] border border-gray-100 shadow-sm space-y-4 text-center">
            <div class="flex justify-between items-center border-b pb-3 border-gray-50">
                <span class="text-[8px] font-black uppercase text-gray-400 tracking-[0.15em]">Publicidade</span>
                <span class="bg-brand-50 text-brand-600 text-[8px] px-2 py-0.5 rounded-full uppercase font-black tracking-wider">Patrocinado</span>
            </div>
            <div class="rounded-2xl overflow-hidden border border-gray-50 aspect-square w-full bg-gray-50 relative group shadow-inner">
                <a href="{{ route('ads.redirect', $ad->id) }}" target="_blank" rel="noopener noreferrer" class="block w-full h-full">
                    <img src="{{ asset('storage/' . $ad->image_path) }}"
                         alt="{{ $ad->title }}"
                         class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                         title="Anúncio: {{ $ad->title }}">
                </a>
            </div>
        </div>
    @else
        <div class="bg-white border-2 border-dashed border-brand-100 p-8 rounded-[3rem] text-center space-y-4">
            <p class="text-brand-500 font-black uppercase tracking-widest text-[10px]">Anuncie aqui</p>
            <p class="text-gray-400 text-xs font-medium leading-relaxed">Sua marca neste espaço do guia de fornecedores.</p>
            <a href="{{ $houseUrl }}" class="inline-block bg-brand-500 hover:bg-brand-600 text-white font-black uppercase text-[9px] tracking-widest px-5 py-3 rounded-xl shadow-md transition">
                Quero anunciar
            </a>
        </div>
    @endif
@else
    {{-- leaderboard / article --}}
    @if($ad)
        <div {{ $attributes->merge(['class' => 'w-full my-8']) }}>
            <p class="text-[8px] font-black text-gray-300 uppercase tracking-widest mb-2 text-center">Publicidade</p>
            <div class="w-full bg-gray-50 rounded-2xl overflow-hidden border border-gray-100 shadow-sm transition hover:shadow-md">
                <a href="{{ route('ads.redirect', $ad->id) }}" target="_blank" rel="noopener noreferrer" class="block w-full">
                    <img src="{{ asset('storage/' . $ad->image_path) }}"
                         alt="{{ $ad->title }}"
                         class="w-full h-auto max-h-[160px] object-cover mx-auto block"
                         title="Patrocinado: {{ $ad->title }}">
                </a>
            </div>
        </div>
    @else
        <div {{ $attributes->merge(['class' => 'w-full my-8']) }}>
            <a href="{{ $houseUrl }}"
               class="flex items-center justify-center w-full min-h-[88px] md:min-h-[120px] rounded-2xl border-2 border-dashed border-brand-200 bg-brand-50/60 hover:bg-brand-50 transition text-center px-6">
                <span class="space-y-1">
                    <span class="block text-brand-600 font-black uppercase tracking-[0.25em] text-[11px]">Anuncie aqui</span>
                    <span class="block text-gray-400 text-[10px] font-bold uppercase tracking-widest">Este espaço está disponível</span>
                </span>
            </a>
        </div>
    @endif
@endif
