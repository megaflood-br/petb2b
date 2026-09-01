<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    /** Papéis permitidos no cadastro self-service. */
    public const ALLOWED_ROLES = ['reader', 'supplier', 'breeder'];

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $cnpj = '';
    public string $role = 'reader';

    public function mount(): void
    {
        // Perfil escolhido na tela de seleção (?role=supplier|breeder|reader).
        $requested = (string) request('role', 'reader');
        $this->role = in_array($requested, self::ALLOWED_ROLES, true) ? $requested : 'reader';
    }

    /**
     * Rótulo amigável do perfil selecionado (usado no cabeçalho do formulário).
     */
    public function roleLabel(): string
    {
        return match ($this->role) {
            'supplier' => 'Indústria / Fornecedor B2B',
            'breeder' => 'Canil Registrado / Criador',
            default => 'Lojista / Profissional',
        };
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'cnpj' => ['nullable', 'string', 'max:18'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        // 'role' não é mass-assignable (proteção contra escalonamento de
        // privilégio). Aplicamos o papel escolhido, restrito à allowlist.
        $role = in_array($this->role, self::ALLOWED_ROLES, true) ? $this->role : 'reader';
        if ($role !== 'reader') {
            $user->forceFill(['role' => $role])->save();
        }

        event(new Registered($user));

        Auth::login($user);

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    {{-- Perfil selecionado na tela anterior --}}
    <div class="mb-6 rounded-xl bg-brand-50 border border-brand-100 px-4 py-3 flex items-center justify-between">
        <div>
            <p class="text-[9px] font-black uppercase tracking-widest text-brand-500">Criando perfil</p>
            <p class="text-sm font-black text-gray-900">{{ $this->roleLabel() }}</p>
        </div>
        <a href="{{ route('register.select') }}" class="text-[10px] font-bold uppercase tracking-wider text-brand-500 hover:underline">Trocar</a>
    </div>

    <form wire:submit="register">
        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Nome')" />
            <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" name="name" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>
        <div class="mt-4">
    <x-input-label for="cnpj" :value="__('CNPJ (Opcional)')" />
    <x-text-input wire:model="cnpj" id="cnpj" class="block mt-1 w-full" type="text" name="cnpj" />
    <x-input-error :messages="$errors->get('cnpj')" class="mt-2" />
</div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Senha')" />

            <x-text-input wire:model="password" id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirme a Senha')" />

            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}" wire:navigate>
                {{ __('Já tem cadastro?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Cadastrar') }}
            </x-primary-button>
        </div>
    </form>
</div>
