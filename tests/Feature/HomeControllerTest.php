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

    public function test_secoes_sao_cacheadas_ate_invalidar(): void
    {
        Cache::forget(HomeController::CACHE_KEY);

        $this->makePost('Post Alpha');
        $this->get('/')->assertOk();
        $this->assertEquals(1, Cache::get(HomeController::CACHE_KEY)['latestPosts']->count());

        // Novo post não aparece enquanto o cache não expira/invalida.
        $this->makePost('Post Beta');
        $this->get('/')->assertOk();
        $this->assertEquals(1, Cache::get(HomeController::CACHE_KEY)['latestPosts']->count());

        // Após invalidar, reflete os dois posts.
        Cache::forget(HomeController::CACHE_KEY);
        $this->get('/')->assertOk();
        $this->assertEquals(2, Cache::get(HomeController::CACHE_KEY)['latestPosts']->count());
    }
}
