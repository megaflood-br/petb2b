<?php

namespace Tests\Feature;

use App\Http\Controllers\HomeController;
use App\Models\Post;
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
}
