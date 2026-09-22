<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_leitor_vai_para_perfil_e_nao_para_home(): void
    {
        $user = User::factory()->create();
        $this->assertSame('reader', $user->fresh()->role);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect(route('profile'));
    }

    public function test_admin_vai_para_o_painel_administrativo(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_comando_promove_usuario_a_admin(): void
    {
        $user = User::factory()->create(['email' => 'dono@rnpet.com.br']);

        $this->artisan('user:make-admin', ['email' => 'dono@rnpet.com.br'])
            ->assertSuccessful();

        $this->assertTrue($user->fresh()->isAdmin());
    }
}
