<?php

namespace Tests\Feature;

use App\Models\Advertisement;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdSpaceTest extends TestCase
{
    use RefreshDatabase;

    private function makeAd(string $position, string $title = 'Campanha Ativa'): Advertisement
    {
        $supplier = Supplier::create([
            'name' => 'Fornecedor ' . uniqid(),
            'email' => 'f_' . uniqid() . '@t.com',
            'description' => 'd',
            'category' => 'racas',
            'is_active' => true,
            'is_approved' => true,
        ]);
        $supplier->credit_balance = 50;
        $supplier->save();

        return Advertisement::create([
            'supplier_id' => $supplier->id,
            'title' => $title,
            'link' => 'https://exemplo.com',
            'position' => $position,
            'image_path' => 'ads/campanha.png',
            'is_active' => true,
            'clicks' => 0,
            'views' => 0,
            'cost_per_click' => 0.50,
            'cost_per_impression' => 0.0070,
        ]);
    }

    public function test_home_sem_anuncio_mostra_anuncie_aqui(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Anuncie aqui')
            ->assertSee('Este espaço está disponível')
            ->assertSee(route('advertise', absolute: false));
    }

    public function test_home_com_anuncio_sorteia_campanha(): void
    {
        $this->makeAd('banner_topo', 'Banner Topo Patrocinado');

        $this->get('/')
            ->assertOk()
            ->assertSee('Banner Topo Patrocinado')
            ->assertDontSee('Este espaço está disponível');
    }

    public function test_setor_sem_anuncio_mostra_placeholder_fixo(): void
    {
        $this->get('/vagas')
            ->assertOk()
            ->assertSee('Anuncie aqui')
            ->assertSee('Este espaço está disponível');
    }

    public function test_setor_com_anuncio_exibe_campanha_daquela_posicao(): void
    {
        $this->makeAd('setor_vagas', 'Banner das Vagas');
        $this->makeAd('banner_topo', 'Banner da Home');

        $this->get('/vagas')
            ->assertOk()
            ->assertSee('Banner das Vagas')
            ->assertDontSee('Banner da Home')
            ->assertDontSee('Este espaço está disponível');
    }

    public function test_anuncio_inativo_nao_substitui_o_placeholder(): void
    {
        $ad = $this->makeAd('setor_racas', 'Campanha Pausada');
        $ad->update(['is_active' => false]);

        $this->get('/racas')
            ->assertOk()
            ->assertSee('Anuncie aqui')
            ->assertDontSee('Campanha Pausada');
    }
}
