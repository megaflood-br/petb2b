<?php

namespace Tests\Feature;

use App\Models\Kennel;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListingCtaTest extends TestCase
{
    use RefreshDatabase;

    public function test_vagas_visitante_ve_banner_para_criar_conta(): void
    {
        $this->get('/vagas')
            ->assertOk()
            ->assertSee('Quer publicar uma vaga?')
            ->assertSee('Criar conta e anunciar')
            ->assertSee(route('register.select', absolute: false))
            ->assertDontSee('Publicar vaga');
    }

    public function test_vagas_fornecedor_logado_vai_publicar_vaga(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => 'supplier'])->save();
        Supplier::create([
            'name' => 'Empresa Vagas CTA',
            'email' => 'vagas_cta_'.uniqid().'@t.com',
            'description' => 'd',
            'category' => 'racas',
            'user_id' => $user->id,
            'is_active' => true,
            'is_approved' => true,
        ]);

        $this->actingAs($user)
            ->get('/vagas')
            ->assertOk()
            ->assertSee('Publicar vaga')
            ->assertSee(route('supplier.jobs', absolute: false))
            ->assertDontSee('Criar conta e anunciar');
    }

    public function test_canis_visitante_ve_banner_para_criar_conta(): void
    {
        $this->get('/canis')
            ->assertOk()
            ->assertSee('Quer cadastrar o seu canil?')
            ->assertSee('Criar conta e cadastrar')
            ->assertSee(route('register.select', absolute: false))
            ->assertDontSee('Gerenciar meu canil');
    }

    public function test_canis_criador_logado_vai_ao_painel(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => 'breeder'])->save();
        Kennel::create([
            'user_id' => $user->id,
            'name' => 'Canil CTA',
            'slug' => 'canil-cta-'.uniqid(),
            'city' => 'Atibaia',
            'state' => 'SP',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get('/canis')
            ->assertOk()
            ->assertSee('Gerenciar meu canil')
            ->assertSee(route('breeder.dashboard', absolute: false))
            ->assertDontSee('Criar conta e cadastrar');
    }
}
