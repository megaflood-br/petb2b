<?php

namespace Tests\Feature;

use App\Livewire\BreedList;
use App\Models\BlogCategory;
use App\Models\Breed;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RacasFallbackTest extends TestCase
{
    use RefreshDatabase;

    private function racasPost(string $title, string $slug, bool $active = true): Post
    {
        $category = BlogCategory::query()->firstOrCreate(
            ['slug' => 'racas'],
            ['name' => 'Raças']
        );

        $post = Post::create([
            'title' => $title,
            'slug' => $slug,
            'content' => 'Conteúdo de teste sobre '.$title.'.',
            'is_active' => $active,
        ]);
        $post->blogCategories()->sync([$category->id]);

        return $post;
    }


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

    public function test_listagem_sem_guia_mostra_artigos_da_categoria_racas(): void
    {
        $this->racasPost('Guia da raça Golden Retriever', 'guia-golden');
        $this->racasPost('Raca Inativa Oculta', 'raca-inativa', false);

        $noticias = BlogCategory::create(['name' => 'Notícias', 'slug' => 'noticias']);
        $other = Post::create([
            'title' => 'Noticia que nao e raca',
            'slug' => 'noticia-x',
            'content' => 'texto',
            'is_active' => true,
        ]);
        $other->blogCategories()->sync([$noticias->id]);

        $this->get('/racas')
            ->assertOk()
            ->assertSee('Guia da raça Golden Retriever')
            ->assertSee('/racas/guia-golden')
            ->assertDontSee('Noticia que nao e raca')
            ->assertDontSee('Raca Inativa Oculta')
            ->assertDontSee('Nenhuma raça encontrada.');
    }

    public function test_listagem_com_guia_oficial_nao_mistura_artigos(): void
    {
        Breed::create([
            'name' => 'Cao Ativo Visivel',
            'species' => 'Cão',
            'description' => 'Raça amigável.',
            'is_active' => true,
        ]);
        $this->racasPost('Artigo Importado Golden', 'artigo-golden');

        $this->get('/racas')
            ->assertOk()
            ->assertSee('Cao Ativo Visivel')
            ->assertDontSee('Artigo Importado Golden');
    }

    public function test_busca_filtra_artigos_quando_nao_ha_guia(): void
    {
        $this->racasPost('Tudo sobre o Poodle', 'tudo-poodle');
        $this->racasPost('Guia Golden', 'guia-golden-busca');

        Livewire::test(BreedList::class)
            ->set('search', 'Poodle')
            ->assertSee('Tudo sobre o Poodle')
            ->assertDontSee('Guia Golden');
    }
}
