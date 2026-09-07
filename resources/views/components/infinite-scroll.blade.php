@props(['paginator'])

@if ($paginator && method_exists($paginator, 'hasMorePages') && $paginator->hasMorePages())
    <div
        {{ $attributes->merge(['class' => 'col-span-full flex flex-col items-center justify-center py-10']) }}
        wire:key="infinite-{{ $paginator->count() }}-{{ $paginator->perPage() }}"
        wire:intersect="loadMore"
        role="status"
        aria-live="polite"
    >
        <span class="text-[10px] font-black uppercase tracking-widest text-gray-400" wire:loading.remove wire:target="loadMore">
            Role para ver mais
        </span>
        <span class="text-[10px] font-black uppercase tracking-widest text-brand-500 animate-pulse" wire:loading wire:target="loadMore">
            Carregando...
        </span>
    </div>
@endif
