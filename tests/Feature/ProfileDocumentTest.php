<?php

namespace Tests\Feature;

use App\Livewire\Supplier\EditProfile;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Livewire\Volt\Volt;
use Tests\TestCase;

class ProfileDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_perfil_da_conta_mostra_campo_de_cpf_ou_cnpj(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk()
            ->assertSee('CPF ou CNPJ');
    }

    public function test_perfil_da_conta_salva_cpf(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Volt::test('profile.update-profile-information-form')
            ->set('name', $user->name)
            ->set('email', $user->email)
            ->set('cnpj', '39053344705')
            ->call('updateProfileInformation')
            ->assertHasNoErrors();

        $this->assertEquals('390.533.447-05', $user->fresh()->cnpj);
    }

    public function test_perfil_da_conta_rejeita_documento_invalido(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Volt::test('profile.update-profile-information-form')
            ->set('name', $user->name)
            ->set('email', $user->email)
            ->set('cnpj', '00000000000')
            ->call('updateProfileInformation')
            ->assertHasErrors(['cnpj']);
    }

    public function test_perfil_da_empresa_salva_cpf(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => User::ROLE_SUPPLIER])->save();
        Category::firstOrCreate(['slug' => 'racas'], ['name' => 'Raças']);

        Livewire::actingAs($user)
            ->test(EditProfile::class)
            ->assertSee('CPF ou CNPJ')
            ->set('name', 'Empresa CPF')
            ->set('email', 'empresa_cpf@t.com')
            ->set('category', 'Raças')
            ->set('description', 'Descrição comercial da empresa para o guia.')
            ->set('address', 'Rua das Flores, 100')
            ->set('city', 'São Paulo')
            ->set('state', 'SP')
            ->set('cnpj', '390.533.447-05')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals('390.533.447-05', Supplier::where('user_id', $user->id)->value('cnpj'));
    }

    public function test_perfil_da_empresa_rejeita_cnpj_invalido(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => User::ROLE_SUPPLIER])->save();
        Category::firstOrCreate(['slug' => 'racas'], ['name' => 'Raças']);

        Livewire::actingAs($user)
            ->test(EditProfile::class)
            ->set('name', 'Empresa CNPJ')
            ->set('email', 'empresa_cnpj@t.com')
            ->set('category', 'Raças')
            ->set('description', 'Descrição comercial da empresa para o guia.')
            ->set('address', 'Rua das Flores, 100')
            ->set('city', 'São Paulo')
            ->set('state', 'SP')
            ->set('cnpj', '12.345.678/0001-99')
            ->call('save')
            ->assertHasErrors(['cnpj']);
    }
}
