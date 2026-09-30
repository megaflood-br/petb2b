<div x-data="{ open: false }" class="relative" @keydown.escape.window="open = false">
    <button type="button"
            @click="open = !open"
            class="rounded-full ring-2 ring-transparent hover:ring-brand-200 focus:ring-brand-300 transition outline-none"
            aria-label="Menu do perfil">
        <x-user-avatar :user="auth()->user()" />
    </button>

    <div x-show="open"
         x-cloak
         @click.outside="open = false"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-1"
         class="absolute right-0 mt-3 w-64 bg-white rounded-[1.75rem] border border-gray-100 shadow-2xl p-3 z-50">
        <div class="flex items-center gap-3 px-3 py-3">
            <x-user-avatar :user="auth()->user()" size="sm" />
            <div class="min-w-0">
                <p class="text-sm font-black text-gray-900 truncate">{{ auth()->user()->name }}</p>
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">{{ auth()->user()->roleLabel() }}</p>
            </div>
        </div>

        <div class="border-t border-gray-50 my-1"></div>

        <a href="{{ route('profile') }}" class="block px-4 py-2.5 rounded-xl text-sm font-bold text-gray-700 hover:bg-brand-50 hover:text-brand-600 transition">
            Meu perfil
        </a>
        <a href="{{ route('favorites.index') }}" class="block px-4 py-2.5 rounded-xl text-sm font-bold text-gray-700 hover:bg-brand-50 hover:text-brand-600 transition">
            Favoritos
        </a>
        @if(auth()->user()->hasPanel())
            <a href="{{ route('dashboard') }}" class="block px-4 py-2.5 rounded-xl text-sm font-bold text-gray-700 hover:bg-brand-50 hover:text-brand-600 transition">
                Painel
            </a>
        @endif

        <form method="POST" action="{{ route('logout') }}" class="mt-1">
            @csrf
            <button type="submit" class="w-full text-left px-4 py-2.5 rounded-xl text-sm font-bold text-red-600 hover:bg-red-50 transition">
                Sair
            </button>
        </form>
    </div>
</div>
