<?php

namespace Tests\Feature;

use App\Http\Controllers\HomeController;
use App\Models\Classified;
use App\Models\JobPosting;
use App\Models\Kennel;
use App\Models\Post;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makePost(string $title): Post
    {
        return Post::create([
            'title' => $title,
            'slug' => 'post-' . uniqid(),
            'content' => 'Conteúdo de teste.',
            'is_active' => true,
        ]);
    }

    public function test_home_renderiza_e_popula_o_cache(): void
    {
        Cache::forget(HomeController::CACHE_KEY);

        $this->get('/')->assertOk();

        $this->assertTrue(Cache::has(HomeController::CACHE_KEY));
        $this->assertArrayHasKey('latestPosts', Cache::get(HomeController::CACHE_KEY));
    }

    public function test_conteudo_novo_invalida_o_cache_da_home(): void
    {
        $this->makePost('Post Alpha');
        $this->get('/')->assertOk();
        $this->assertEquals(1, Cache::get(HomeController::CACHE_KEY)['latestPosts']->count());

        // Criar um post invalida o cache automaticamente (evento saved).
        $this->makePost('Post Beta');
        $this->assertFalse(Cache::has(HomeController::CACHE_KEY));

        // O próximo acesso reconstrói já com os dois posts.
        $this->get('/')->assertOk();
        $this->assertEquals(2, Cache::get(HomeController::CACHE_KEY)['latestPosts']->count());
    }

    public function test_alteracao_de_fornecedor_invalida_o_cache_da_home(): void
    {
        $this->get('/')->assertOk();
        $this->assertTrue(Cache::has(HomeController::CACHE_KEY));

        \App\Models\Supplier::create([
            'name' => 'Nova Empresa',
            'email' => 'nova_' . uniqid() . '@t.com',
            'description' => 'd',
            'category' => 'racas',
            'is_active' => true,
            'is_approved' => true,
        ]);

        $this->assertFalse(Cache::has(HomeController::CACHE_KEY));
    }

    public function test_home_sempre_exibe_secoes_de_classificados_vagas_e_canis(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Classificados')
            ->assertSee('Vagas no Mercado')
            ->assertSee('Guia de')
            ->assertSee('Canis')
            ->assertSee('Nenhum classificado publicado no momento.')
            ->assertSee('Nenhuma vaga publicada no momento.')
            ->assertSee('Nenhum canil cadastrado no momento.')
            ->assertSee(route('classifieds.index', absolute: false))
            ->assertSee(route('jobs.index', absolute: false))
            ->assertSee(route('kennels.index', absolute: false));
    }

    public function test_home_lista_classificados_vagas_e_canis_ativos(): void
    {
        $supplier = Supplier::create([
            'name' => 'Empresa Home',
            'email' => 'home_' . uniqid() . '@t.com',
            'description' => 'd',
            'category' => 'racas',
            'city' => 'Atibaia',
            'state' => 'SP',
            'is_active' => true,
            'is_approved' => true,
        ]);

        Classified::create([
            'supplier_id' => $supplier->id,
            'title' => 'Máquina de Tosa Profissional',
            'slug' => 'maquina-tosa-' . uniqid(),
            'description' => 'Equipamento seminovo.',
            'price' => 1290.50,
            'condition' => 'Usado',
            'is_active' => true,
        ]);

        Classified::create([
            'supplier_id' => $supplier->id,
            'title' => 'Classificado Pausado',
            'slug' => 'classificado-pausado-' . uniqid(),
            'description' => 'Não deve aparecer.',
            'price' => 10,
            'condition' => 'Novo',
            'is_active' => false,
        ]);

        JobPosting::create([
            'supplier_id' => $supplier->id,
            'title' => 'Vendedor Pet Home',
            'description' => 'Atendimento a lojistas.',
            'type' => 'CLT',
            'city' => 'Atibaia',
            'state' => 'SP',
            'how_to_apply' => 'rh@empresa.com',
            'is_active' => true,
        ]);

        JobPosting::create([
            'supplier_id' => $supplier->id,
            'title' => 'Vaga Pausada Home',
            'description' => 'Não deve aparecer.',
            'type' => 'PJ',
            'how_to_apply' => 'rh@empresa.com',
            'is_active' => false,
        ]);

        $user = User::create([
            'name' => 'Criador Home',
            'email' => 'criador_' . uniqid() . '@t.com',
            'password' => 'secret',
        ]);

        Kennel::create([
            'user_id' => $user->id,
            'name' => 'Canil Aurora Home',
            'slug' => 'canil-aurora-' . uniqid(),
            'description' => 'Criação de Golden Retriever.',
            'city' => 'Atibaia',
            'state' => 'SP',
            'is_active' => true,
            'is_verified' => true,
        ]);

        Kennel::create([
            'user_id' => $user->id,
            'name' => 'Canil Inativo Home',
            'slug' => 'canil-inativo-' . uniqid(),
            'is_active' => false,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Máquina de Tosa Profissional')
            ->assertSee('Vendedor Pet Home')
            ->assertSee('Canil Aurora Home')
            ->assertDontSee('Classificado Pausado')
            ->assertDontSee('Vaga Pausada Home')
            ->assertDontSee('Canil Inativo Home')
            ->assertDontSee('Nenhum classificado publicado no momento.')
            ->assertDontSee('Nenhuma vaga publicada no momento.')
            ->assertDontSee('Nenhum canil cadastrado no momento.');
    }

    public function test_vaga_e_canil_invalidam_o_cache_da_home(): void
    {
        $this->get('/')->assertOk();
        $this->assertTrue(Cache::has(HomeController::CACHE_KEY));

        $supplier = Supplier::create([
            'name' => 'Empresa Cache',
            'email' => 'cache_' . uniqid() . '@t.com',
            'description' => 'd',
            'category' => 'racas',
            'is_active' => true,
            'is_approved' => true,
        ]);

        // Supplier já invalida o cache; reconstrói e testa Job + Kennel.
        $this->get('/')->assertOk();
        $this->assertTrue(Cache::has(HomeController::CACHE_KEY));

        JobPosting::create([
            'supplier_id' => $supplier->id,
            'title' => 'Nova Vaga Cache',
            'description' => 'Descrição.',
            'type' => 'CLT',
            'how_to_apply' => 'rh@empresa.com',
            'is_active' => true,
        ]);
        $this->assertFalse(Cache::has(HomeController::CACHE_KEY));

        $this->get('/')->assertOk();
        $this->assertTrue(Cache::has(HomeController::CACHE_KEY));

        $user = User::create([
            'name' => 'Criador Cache',
            'email' => 'cache_c_' . uniqid() . '@t.com',
            'password' => 'secret',
        ]);
        Kennel::create([
            'user_id' => $user->id,
            'name' => 'Canil Cache',
            'slug' => 'canil-cache-' . uniqid(),
            'is_active' => true,
        ]);
        $this->assertFalse(Cache::has(HomeController::CACHE_KEY));
    }
}
