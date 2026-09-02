<?php

namespace Tests\Feature;

use App\Models\Breed;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RacasFallbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_racas_slug_mostra_raca_quando_existe(): void
    {
        Breed::create([
            'name' => 'Labrador Retriever',
            'slug' => 'labrador-retriever',
            'species' => 'Cão',
            'description' => 'Raça amigável e popular no Brasil.',
            'is_active' => true,
        ]);

        $this->get('/racas/labrador-retriever')
            ->assertOk()
            ->assertSee('Labrador Retriever');
    }

    public function test_racas_slug_cai_para_artigo_legado_quando_nao_ha_raca(): void
    {
        // Artigo importado do WordPress publicado sob /racas/{slug}.
        Post::create([
            'title' => 'Tudo sobre o Poodle',
            'slug' => 'tudo-sobre-o-poodle',
            'content' => 'Conteúdo do artigo legado sobre a raça Poodle.',
            'is_active' => true,
        ]);

        $this->get('/racas/tudo-sobre-o-poodle')
            ->assertOk()
            ->assertSee('Tudo sobre o Poodle');
    }

    public function test_racas_slug_inexistente_404(): void
    {
        $this->get('/racas/nao-existe-nada')->assertNotFound();
    }

    public function test_raca_tem_prioridade_sobre_artigo_de_mesmo_slug(): void
    {
        Breed::create([
            'name' => 'Bulldog Frances', 'slug' => 'bulldog', 'species' => 'Cão',
            'description' => 'Descrição da raça bulldog.', 'is_active' => true,
        ]);
        Post::create([
            'title' => 'Artigo Bulldog Antigo', 'slug' => 'bulldog',
            'content' => 'texto', 'is_active' => true,
        ]);

        $this->get('/racas/bulldog')
            ->assertOk()
            ->assertSee('Bulldog Frances')
            ->assertDontSee('Artigo Bulldog Antigo');
    }
}
