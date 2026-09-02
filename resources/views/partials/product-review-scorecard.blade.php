{{-- Scorecard de análise: nota, prós, contras e veredito. Aceita Post ou ProductReview. --}}
@php
    $rating = $review->rating ?? null;
    $pros = trim((string) ($review->pros ?? ''));
    $cons = trim((string) ($review->cons ?? ''));
    $verdict = trim((string) ($review->verdict ?? ''));
@endphp

@if($rating !== null || $pros !== '' || $cons !== '' || $verdict !== '')
    <div class="space-y-10 my-12">
        @if($pros !== '' || $cons !== '')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-green-50/60 p-8 rounded-[2rem] border border-green-100">
                    <h3 class="font-black text-green-700 uppercase text-xs mb-4 tracking-widest">Pontos positivos</h3>
                    <div class="text-sm font-semibold text-green-800 whitespace-pre-line leading-relaxed normal-case">{{ $pros !== '' ? $pros : '—' }}</div>
                </div>
                <div class="bg-red-50/60 p-8 rounded-[2rem] border border-red-100">
                    <h3 class="font-black text-red-700 uppercase text-xs mb-4 tracking-widest">Pontos negativos</h3>
                    <div class="text-sm font-semibold text-red-800 whitespace-pre-line leading-relaxed normal-case">{{ $cons !== '' ? $cons : '—' }}</div>
                </div>
            </div>
        @endif

        @if($verdict !== '' || $rating !== null)
            <div class="bg-gray-950 p-10 rounded-[3rem] text-white relative overflow-hidden">
                <div class="relative z-10 flex justify-between items-center border-b border-white/10 pb-5 mb-6">
                    <h3 class="text-xl font-black uppercase italic tracking-tight text-white">Veredito</h3>
                    @if($rating !== null)
                        <span class="text-3xl font-black text-amber-400 font-mono flex items-center gap-1">
                            <span class="text-xl">★</span> {{ number_format((float) $rating, 1) }}
                        </span>
                    @endif
                </div>
                @if($verdict !== '')
                    <p class="relative z-10 text-gray-300 italic text-lg leading-relaxed font-medium">"{{ $verdict }}"</p>
                @endif
            </div>
        @endif
    </div>
@endif
