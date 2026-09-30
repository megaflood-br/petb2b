@props(['user', 'size' => 'md'])

@php
    $sizes = [
        'sm' => 'w-8 h-8 text-[10px]',
        'md' => 'w-10 h-10 text-xs',
        'lg' => 'w-24 h-24 text-2xl',
    ];
    $box = $sizes[$size] ?? $sizes['md'];
@endphp

<span {{ $attributes->class([$box, 'inline-flex items-center justify-center rounded-full overflow-hidden bg-brand-500 text-white font-black uppercase shrink-0']) }}>@if($user->avatarUrl())<img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}" class="w-full h-full object-cover">@else{{ $user->initials() }}@endif</span>
