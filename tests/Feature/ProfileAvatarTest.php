<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;
use Tests\TestCase;

class ProfileAvatarTest extends TestCase
{
    use RefreshDatabase;

    public function test_perfil_mostra_upload_de_foto(): void
    {
        $user = User::factory()->create(['name' => 'Ana Silva']);

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk()
            ->assertSee('Dados de acesso')
            ->assertSee('Imagem de perfil')
            ->assertSee('AS')
            ->assertSeeVolt('profile.update-profile-information-form');
    }

    public function test_usuario_envia_foto_de_perfil(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user);

        Volt::test('profile.update-profile-information-form')
            ->set('name', $user->name)
            ->set('email', $user->email)
            ->set('avatar', UploadedFile::fake()->image('foto.jpg', 200, 200))
            ->call('updateProfileInformation')
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertNotNull($user->avatar_path);
        Storage::disk('public')->assertExists($user->avatar_path);
        $this->assertNotNull($user->avatarUrl());
    }

    public function test_header_mostra_foto_bolinha_e_nao_o_texto_dashboard(): void
    {
        $user = User::factory()->create(['name' => 'Joao Leitor']);

        $html = $this->actingAs($user)->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('Menu do perfil', $html);
        $this->assertStringContainsString('Meu perfil', $html);
        $this->assertStringContainsString('Favoritos', $html);
        $this->assertStringContainsString('>JL</span>', $html);
        $this->assertStringNotContainsString('>Dashboard</a>', $html);
        $this->assertStringNotContainsString('>Painel</a>', $html);
    }

    public function test_admin_ainda_enxerga_o_painel_no_menu(): void
    {
        $admin = User::factory()->create(['name' => 'Admin Portal']);
        $admin->forceFill(['role' => User::ROLE_ADMIN])->save();

        $this->actingAs($admin)
            ->get('/')
            ->assertOk()
            ->assertSee('Painel')
            ->assertSee(route('dashboard', absolute: false), false);
    }
}
