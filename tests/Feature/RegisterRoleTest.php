<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class RegisterRoleTest extends TestCase
{
    use RefreshDatabase;

    private function register(string $role, ?string $email = null): void
    {
        Volt::test('pages.auth.register')
            ->set('role', $role)
            ->set('name', 'Fulano')
            ->set('email', $email ?? ('u_' . uniqid() . '@teste.com'))
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');
    }

    public function test_cadastro_como_fornecedor_define_role_supplier(): void
    {
        $this->register('supplier', 'forn@teste.com');
        $this->assertEquals('supplier', User::where('email', 'forn@teste.com')->first()->role);
    }

    public function test_cadastro_como_canil_define_role_breeder(): void
    {
        $this->register('breeder', 'canil@teste.com');
        $this->assertEquals('breeder', User::where('email', 'canil@teste.com')->first()->role);
    }

    public function test_cadastro_padrao_define_role_reader(): void
    {
        $this->register('reader', 'leitor@teste.com');
        $this->assertEquals('reader', User::where('email', 'leitor@teste.com')->first()->role);
    }

    public function test_role_invalido_cai_para_reader(): void
    {
        // Tentativa de escalonamento de privilégio deve ser barrada.
        $this->register('admin', 'hacker@teste.com');
        $this->assertEquals('reader', User::where('email', 'hacker@teste.com')->first()->role);
    }
}
