<?php

namespace Tests\Feature;

use App\Livewire\Admin\ManageSettings;
use App\Models\User;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MaintenanceModeTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => 'admin'])->save();

        return $user;
    }

    public function test_site_normal_quando_manutencao_desligada(): void
    {
        $this->get('/')->assertOk()->assertDontSee('Site em manutenção');
    }

    public function test_front_mostra_pagina_de_manutencao(): void
    {
        Settings::set('maintenance_enabled', '1');
        Settings::set('maintenance_message', 'Portal em ajuste técnico até amanhã.');

        $this->get('/')
            ->assertStatus(503)
            ->assertSee('Site em')
            ->assertSee('manutenção')
            ->assertSee('Voltaremos em breve')
            ->assertSee('Portal em ajuste técnico até amanhã.');

        $this->get('/noticias')->assertStatus(503);
        $this->get('/racas')->assertStatus(503);
    }

    public function test_login_e_admin_continuam_acessiveis(): void
    {
        Settings::set('maintenance_enabled', '1');
        $admin = $this->admin();

        $this->get('/login')->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.settings'))
            ->assertOk()
            ->assertSee('Modo manutenção');
    }

    public function test_admin_logado_ainda_ve_o_site(): void
    {
        Settings::set('maintenance_enabled', '1');

        $this->actingAs($this->admin())
            ->get('/')
            ->assertOk()
            ->assertDontSee('Voltaremos em breve');
    }

    public function test_admin_liga_e_desliga_manutencao_pelo_painel(): void
    {
        Livewire::actingAs($this->admin())
            ->test(ManageSettings::class)
            ->set('maintenance_enabled', true)
            ->assertSet('maintenance_enabled', true);

        $this->assertTrue(Settings::maintenanceEnabled());

        $this->get('/')->assertStatus(503)->assertSee('Voltaremos em breve');

        Livewire::actingAs($this->admin())
            ->test(ManageSettings::class)
            ->set('maintenance_message', 'Voltamos depois do almoço.')
            ->call('saveMaintenance')
            ->assertHasNoErrors();

        $this->assertEquals('Voltamos depois do almoço.', Settings::maintenanceMessage());

        Livewire::actingAs($this->admin())
            ->test(ManageSettings::class)
            ->set('maintenance_enabled', false);

        $this->assertFalse(Settings::maintenanceEnabled());
        $this->get('/')->assertOk();
    }
}
