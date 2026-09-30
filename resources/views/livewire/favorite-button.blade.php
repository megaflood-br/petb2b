<button type="button"
        wire:click.stop="toggle"
        onclick="event.preventDefault(); event.stopPropagation();"
        class="inline-flex items-center justify-center w-10 h-10 rounded-full border shadow-sm transition {{ $favorited ? 'bg-brand-500 border-brand-500 text-white shadow-brand-500/30' : 'bg-white/95 border-gray-100 text-gray-400 hover:text-brand-500 hover:border-brand-200 hover:bg-brand-50' }}"
        aria-label="{{ $favorited ? 'Remover dos favoritos' : 'Favoritar' }}"
        title="{{ $favorited ? 'Remover dos favoritos' : 'Favoritar' }}">
    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="{{ $favorited ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
    </svg>
</button>
