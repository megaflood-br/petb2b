<?php

namespace Tests\Feature;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassifiedsCtaTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitante_ve_banner_para_criar_conta(): void
    {
        $this->get('/classificados')
            ->assertOk()
            ->assertSee('Quer anunciar nos classificados?')
            ->assertSee('Criar conta e anunciar')
            ->assertSee(route('register.select', absolute: false))
            ->assertDontSee('Publicar anúncio');
    }

    public function test_fornecedor_logado_vai_para_publicar_anuncio(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => 'supplier'])->save();
        Supplier::create([
            'name' => 'Empresa CTA',
            'email' => 'cta_'.uniqid().'@t.com',
            'description' => 'd',
            'category' => 'racas',
            'user_id' => $user->id,
            'is_active' => true,
            'is_approved' => true,
        ]);

        $this->actingAs($user)
            ->get('/classificados')
            ->assertOk()
            ->assertSee('Publicar anúncio')
            ->assertSee(route('supplier.classifieds', absolute: false))
            ->assertDontSee('Criar conta e anunciar');
    }
}
