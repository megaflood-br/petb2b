@props(['position'])

@php
    $ad = \App\Models\Advertisement::where('position', $position)
            ->where('is_active', true)
            ->inRandomOrder()
            ->first();
@endphp

@if($ad)
    <div class="my-8 text-center">
        <a href="{{ $ad->link }}" target="_blank" class="inline-block group">
            <p class="text-[8px] font-black text-gray-300 uppercase tracking-widest mb-2">Publicidade</p>
            <img src="{{ asset('storage/' . $ad->image_path) }}"
                 class=" shadow-xl group-hover:scale-[1.01] transition duration-500 max-w-full h-auto">
        </a>
    </div>
@endif
