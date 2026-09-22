<?php

namespace Tests\Feature;

use App\Livewire\Admin\ManageUsers;
use App\Models\Kennel;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminManageUsersTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create(['name' => 'Admin Portal', 'email' => 'admin_users@t.com']);
        $user->forceFill(['role' => User::ROLE_ADMIN])->save();

        return $user;
    }

    private function member(string $role, string $name, string $email): User
    {
        $user = User::factory()->create(['name' => $name, 'email' => $email]);
        if ($role !== User::ROLE_READER) {
            $user->forceFill(['role' => $role])->save();
        }

        return $user->fresh();
    }

    public function test_visitante_nao_acessa_gestao_de_usuarios(): void
    {
        $this->get(route('admin.users'))->assertRedirect();
    }

    public function test_admin_lista_os_tres_perfis_e_esconde_admins(): void
    {
        $admin = $this->admin();
        $this->member(User::ROLE_READER, 'Lojista Ana', 'ana@t.com');
        $supplier = $this->member(User::ROLE_SUPPLIER, 'Fornecedor Beto', 'beto@t.com');
        $breeder = $this->member(User::ROLE_BREEDER, 'Criador Caio', 'caio@t.com');

        Supplier::create([
            'name' => 'Pet Indústria CTA',
            'email' => 'ind@t.com',
            'description' => 'd',
            'category' => 'racas',
            'user_id' => $supplier->id,
            'is_active' => true,
            'is_approved' => true,
        ]);
        Kennel::create([
            'user_id' => $breeder->id,
            'name' => 'Canil Estrela',
            'slug' => 'canil-estrela',
            'city' => 'Atibaia',
            'state' => 'SP',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users'))
            ->assertOk()
            ->assertSee('Gestão de')
            ->assertSee('Lojista Ana')
            ->assertSee('Fornecedor Beto')
            ->assertSee('Criador Caio')
            ->assertSee('Pet Indústria CTA')
            ->assertSee('Canil Estrela')
            ->assertDontSee('admin_users@t.com');
    }

    public function test_filtra_por_perfil(): void
    {
        $admin = $this->admin();
        $this->member(User::ROLE_READER, 'Lojista Visivel', 'lojista@t.com');
        $this->member(User::ROLE_BREEDER, 'Canil Oculto', 'canil@t.com');

        Livewire::actingAs($admin)
            ->test(ManageUsers::class)
            ->call('setRoleFilter', User::ROLE_READER)
            ->assertSee('Lojista Visivel')
            ->assertDontSee('Canil Oculto');
    }

    public function test_admin_cadastra_usuario_com_perfil(): void
    {
        Livewire::actingAs($this->admin())
            ->test(ManageUsers::class)
            ->call('toggleForm')
            ->set('name', 'Nova Clínica')
            ->set('email', 'clinica@t.com')
            ->set('password', 'password1')
            ->set('editRole', User::ROLE_BREEDER)
            ->call('save')
            ->assertHasNoErrors();

        $user = User::where('email', 'clinica@t.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals(User::ROLE_BREEDER, $user->role);
        $this->assertFalse(in_array('role', (new User)->getFillable(), true));
    }

    public function test_admin_troca_perfil_sem_permitir_admin(): void
    {
        $admin = $this->admin();
        $user = $this->member(User::ROLE_READER, 'Troca Perfil', 'troca@t.com');

        Livewire::actingAs($admin)
            ->test(ManageUsers::class)
            ->call('changeRole', $user->id, User::ROLE_SUPPLIER)
            ->assertHasNoErrors();

        $this->assertEquals(User::ROLE_SUPPLIER, $user->fresh()->role);

        Livewire::actingAs($admin)
            ->test(ManageUsers::class)
            ->call('changeRole', $user->id, User::ROLE_ADMIN);

        $this->assertEquals(User::ROLE_SUPPLIER, $user->fresh()->role);
    }

    public function test_nao_edita_a_propria_conta_nem_outro_admin(): void
    {
        $admin = $this->admin();
        $otherAdmin = User::factory()->create(['email' => 'outro_admin@t.com']);
        $otherAdmin->forceFill(['role' => User::ROLE_ADMIN])->save();

        Livewire::actingAs($admin)
            ->test(ManageUsers::class)
            ->call('changeRole', $admin->id, User::ROLE_READER)
            ->assertForbidden();

        Livewire::actingAs($admin)
            ->test(ManageUsers::class)
            ->call('changeRole', $otherAdmin->id, User::ROLE_READER)
            ->assertForbidden();

        $this->assertEquals(User::ROLE_ADMIN, $admin->fresh()->role);
        $this->assertEquals(User::ROLE_ADMIN, $otherAdmin->fresh()->role);
    }

    public function test_dashboard_mostra_total_de_usuarios(): void
    {
        $this->member(User::ROLE_READER, 'A', 'a@t.com');
        $this->member(User::ROLE_SUPPLIER, 'B', 'b@t.com');

        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Usuários do Portal')
            ->assertSee(route('admin.users', absolute: false));
    }
}
