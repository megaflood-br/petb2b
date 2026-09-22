@props([
    'eyebrow' => 'Anuncie no portal',
    'title',
    'description',
    'href',
    'label',
])

<div {{ $attributes->merge(['class' => 'mb-8 rounded-[2rem] bg-gray-950 px-6 py-8 md:px-10 md:py-9 flex flex-col md:flex-row md:items-center justify-between gap-6']) }}>
    <div class="max-w-xl">
        <p class="text-[10px] font-black uppercase tracking-[0.25em] text-brand-400 mb-2">{{ $eyebrow }}</p>
        <h2 class="text-2xl md:text-3xl font-black text-white uppercase italic tracking-tight">{{ $title }}</h2>
        <p class="mt-2 text-sm text-gray-400 font-medium">{{ $description }}</p>
    </div>
    <a href="{{ $href }}" class="inline-flex items-center justify-center bg-brand-500 hover:bg-brand-600 text-white px-8 py-4 rounded-2xl font-black uppercase text-[11px] tracking-widest transition shadow-lg shadow-brand-500/20 shrink-0">
        {{ $label }}
    </a>
</div>
