<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExclusiveContentTest extends TestCase
{
    use RefreshDatabase;

    private function makePost(bool $premium): Post
    {
        // Conteúdo maior que o corte da prévia (450), com um marcador após o corte.
        $content = str_repeat('A', 480) . ' MARCADOR_SECRETO_FINAL';

        return Post::create([
            'title' => 'Materia ' . uniqid(),
            'slug' => 'materia-' . uniqid(),
            'content' => $content,
            'is_active' => true,
            'is_premium' => $premium,
        ]);
    }

    private function url(Post $post): string
    {
        return '/materias-geral/' . $post->slug;
    }

    public function test_visitante_ve_paywall_e_nao_ve_conteudo_completo(): void
    {
        $post = $this->makePost(premium: true);

        $this->get($this->url($post))
            ->assertOk()
            ->assertSee('Conteúdo Exclusivo')
            ->assertSee('Criar conta grátis')
            ->assertDontSee('MARCADOR_SECRETO_FINAL');
    }

    public function test_usuario_logado_ve_conteudo_completo(): void
    {
        $user = User::create(['name' => 'Leitor', 'email' => 'l_' . uniqid() . '@t.com', 'password' => 'secret']);
        $post = $this->makePost(premium: true);

        $this->actingAs($user)
            ->get($this->url($post))
            ->assertOk()
            ->assertSee('MARCADOR_SECRETO_FINAL')
            ->assertDontSee('Criar conta grátis');
    }

    public function test_post_nao_exclusivo_e_publico_para_visitante(): void
    {
        $post = $this->makePost(premium: false);

        $this->get($this->url($post))
            ->assertOk()
            ->assertSee('MARCADOR_SECRETO_FINAL')
            ->assertDontSee('Criar conta grátis');
    }
}
