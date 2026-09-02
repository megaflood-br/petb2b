<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogShowRouteTest extends TestCase
{
    use RefreshDatabase;

    private function makePost(string $slug, bool $active = true): Post
    {
        return Post::create([
            'title' => 'Artigo ' . $slug,
            'slug' => $slug,
            'content' => 'Conteúdo do artigo de teste.',
            'is_active' => $active,
        ]);
    }

    public function test_artigo_renderiza_em_prefixo_nao_reservado(): void
    {
        $this->makePost('meu-artigo');

        $this->get('/materias-geral/meu-artigo')
            ->assertOk()
            ->assertSee('Artigo meu-artigo');
    }

    public function test_prefixo_reservado_nao_e_tratado_como_artigo(): void
    {
        // Mesmo existindo um post com este slug, sob um prefixo reservado
        // (seção real do site, ex.: "admin") deve retornar 404.
        $this->makePost('colisao');

        $this->get('/admin/colisao')->assertNotFound();

        // Sob um prefixo qualquer (não reservado), o artigo é exibido.
        $this->get('/categoria-livre/colisao')->assertOk()->assertSee('Artigo colisao');
    }

    public function test_artigo_inativo_retorna_404(): void
    {
        $this->makePost('rascunho', active: false);

        $this->get('/materias-geral/rascunho')->assertNotFound();
    }
}
