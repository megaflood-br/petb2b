@props(['paginator'])

@if ($paginator && method_exists($paginator, 'hasMorePages') && $paginator->hasMorePages())
    <div
        {{ $attributes->merge(['class' => 'col-span-full flex flex-col items-center justify-center py-10']) }}
        wire:key="infinite-{{ $paginator->count() }}-{{ $paginator->perPage() }}"
        x-intersect.margin.400px="{{ '$wire.loadMore()' }}"
        role="status"
        aria-live="polite"
    >
        <button
            type="button"
            wire:click="loadMore"
            class="text-[10px] font-black uppercase tracking-widest text-gray-400 hover:text-brand-500 transition"
        >
            <span wire:loading.remove wire:target="loadMore">Carregar mais</span>
            <span wire:loading wire:target="loadMore">Carregando...</span>
        </button>
    </div>
@endif
