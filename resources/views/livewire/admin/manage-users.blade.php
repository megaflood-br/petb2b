<div class="space-y-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-3xl font-black text-gray-900 uppercase italic tracking-tight">Gestão de <span class="text-brand-500">Usuários</span></h1>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-1">Lojistas, fornecedores e canis — separado das empresas do guia</p>
        </div>
        <button wire:click="toggleForm" class="bg-brand-500 text-white px-6 py-3 rounded-2xl font-black uppercase text-xs tracking-widest hover:bg-brand-600 transition shadow-lg shadow-brand-100">
            {{ $showForm ? 'Cancelar' : 'Novo usuário' }}
        </button>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-50 p-4 rounded-2xl border border-green-100 text-green-600 text-[10px] font-black uppercase">{{ session('message') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="bg-red-50 p-4 rounded-2xl border border-red-100 text-red-600 text-[10px] font-black uppercase">{{ session('error') }}</div>
    @endif

    @if ($showForm)
        <form wire:submit.prevent="save" class="bg-white border border-gray-100 rounded-[2rem] p-8 shadow-sm space-y-5">
            <h2 class="text-sm font-black text-gray-900 uppercase tracking-wide">{{ $editingId ? 'Editar usuário' : 'Cadastrar usuário' }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Nome</label>
                    <input type="text" wire:model="name" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-sm font-bold focus:ring-2 focus:ring-brand-500">
                    @error('name') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">E-mail</label>
                    <input type="email" wire:model="email" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-sm font-bold focus:ring-2 focus:ring-brand-500">
                    @error('email') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">Perfil</label>
                    <select wire:model="editRole" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-sm font-bold focus:ring-2 focus:ring-brand-500">
                        @foreach ($roles as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('editRole') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="text-[9px] font-black uppercase text-gray-400 mb-1.5 block">{{ $editingId ? 'Nova senha (opcional)' : 'Senha' }}</label>
                    <input type="password" wire:model="password" autocomplete="new-password" class="w-full bg-gray-50 border-none rounded-xl p-3.5 text-sm font-bold focus:ring-2 focus:ring-brand-500">
                    @error('password') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
            <button type="submit" class="bg-gray-900 hover:bg-black text-white px-8 py-3 rounded-xl font-black uppercase text-[10px] tracking-widest transition">
                {{ $editingId ? 'Salvar alterações' : 'Cadastrar' }}
            </button>
        </form>
    @endif

    <div class="flex flex-wrap gap-3">
        <button wire:click="setRoleFilter('')" class="px-5 py-3 rounded-xl font-black uppercase text-[10px] tracking-widest transition {{ $roleFilter === '' ? 'bg-brand-500 text-white' : 'bg-white text-gray-400 border border-gray-100' }}">
            Todos ({{ $counts->sum() }})
        </button>
        @foreach ($roles as $value => $label)
            <button wire:click="setRoleFilter('{{ $value }}')" class="px-5 py-3 rounded-xl font-black uppercase text-[10px] tracking-widest transition {{ $roleFilter === $value ? 'bg-brand-500 text-white' : 'bg-white text-gray-400 border border-gray-100' }}">
                {{ $label }} ({{ $counts[$value] ?? 0 }})
            </button>
        @endforeach
    </div>

    <div class="bg-white border border-gray-100 rounded-[2rem] p-4 shadow-sm">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Buscar por nome ou e-mail..." class="w-full bg-gray-50 border-none rounded-xl p-4 text-sm font-bold focus:ring-2 focus:ring-brand-500">
    </div>

    <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden text-xs">
        <table class="w-full text-left">
            <thead class="bg-gray-50/50">
                <tr>
                    <th class="py-4 px-6 font-black text-gray-400 uppercase tracking-widest">Usuário</th>
                    <th class="py-4 px-6 font-black text-gray-400 uppercase tracking-widest">Perfil</th>
                    <th class="py-4 px-6 font-black text-gray-400 uppercase tracking-widest">Vínculo</th>
                    <th class="py-4 px-6 font-black text-gray-400 uppercase tracking-widest">Cadastro</th>
                    <th class="py-4 px-6 font-black text-gray-400 uppercase tracking-widest text-right">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($users as $user)
                    <tr class="hover:bg-gray-50/50 transition font-bold" wire:key="user-{{ $user->id }}">
                        <td class="py-6 px-6">
                            <p class="text-gray-900 uppercase">{{ $user->name }}</p>
                            <p class="text-[10px] text-gray-400 font-medium normal-case mt-0.5">{{ $user->email }}</p>
                        </td>
                        <td class="py-6 px-6">
                            @if ($user->id === auth()->id())
                                <span class="bg-gray-100 text-gray-500 px-3 py-1 rounded-full text-[9px] uppercase tracking-wider">{{ $user->roleLabel() }}</span>
                            @else
                                <select wire:change="changeRole({{ $user->id }}, $event.target.value)" class="bg-gray-50 border-none rounded-xl text-[10px] font-black uppercase tracking-wider focus:ring-2 focus:ring-brand-500">
                                    @foreach ($roles as $value => $label)
                                        <option value="{{ $value }}" @selected($user->role === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </td>
                        <td class="py-6 px-6 text-gray-500 font-medium normal-case">
                            @if ($user->supplier)
                                <a href="{{ route('admin.suppliers') }}" class="text-brand-600 hover:underline">Empresa: {{ $user->supplier->name }}</a>
                            @elseif ($user->kennel)
                                <a href="{{ route('admin.kennels') }}" class="text-brand-600 hover:underline">Canil: {{ $user->kennel->name }}</a>
                            @else
                                <span class="text-gray-400">Sem empresa ou canil</span>
                            @endif
                        </td>
                        <td class="py-6 px-6 text-gray-500 font-mono">{{ $user->created_at?->format('d/m/Y') }}</td>
                        <td class="py-6 px-6 text-right">
                            @if ($user->id !== auth()->id())
                                <button type="button" wire:click="edit({{ $user->id }})" class="text-brand-600 uppercase font-black">Editar</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-gray-400 font-black uppercase tracking-widest">Nenhum usuário encontrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-infinite-scroll :paginator="$users" />
</div>
