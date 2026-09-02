<?php

namespace Tests\Feature;

use App\Http\Controllers\HomeController;
use App\Livewire\Admin\ManageBlog;
use App\Models\BlogCategory;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

class ProductAnalysisPostTest extends TestCase
{
    use RefreshDatabase;

    private function analysisCategory(): BlogCategory
    {
        return BlogCategory::create([
            'name' => 'Análises de Produtos',
            'slug' => 'analises-de-produtos',
        ]);
    }

    private function analysisPost(array $overrides = []): Post
    {
        $category = $this->analysisCategory();

        $post = Post::create(array_merge([
            'title' => 'Soprador Turbo 10',
            'slug' => 'soprador-turbo-10',
            'content' => '<p>Análise completa do soprador em ambiente de tosa.</p>',
            'is_active' => true,
            'rating' => 4.5,
            'pros' => "Motor potente\nBaixo ruído",
            'cons' => 'Preço elevado',
            'verdict' => 'O melhor custo-benefício da categoria.',
        ], $overrides));

        $post->blogCategories()->sync([$category->id]);

        return $post->fresh('blogCategories');
    }

    public function test_admin_salva_nota_pros_contras_e_veredito_no_post(): void
    {
        $category = $this->analysisCategory();

        Livewire::test(ManageBlog::class)
            ->set('title', 'Shampoo Neutro Pro')
            ->set('content', 'Texto da análise com detalhes do produto testado.')
            ->set('selected_categories', [(string) $category->id])
            ->set('rating', 4.8)
            ->set('pros', 'Rende bastante')
            ->set('cons', 'Cheiro forte')
            ->set('verdict', 'Recomendado para pet shops de alto fluxo.')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('posts', [
            'title' => 'Shampoo Neutro Pro',
            'rating' => 4.8,
            'pros' => 'Rende bastante',
            'cons' => 'Cheiro forte',
            'verdict' => 'Recomendado para pet shops de alto fluxo.',
        ]);
    }

    public function test_artigo_publico_exibe_nota_pros_contras_e_veredito(): void
    {
        $post = $this->analysisPost();

        $this->get('/materias-analises-de-produtos/' . $post->slug)
            ->assertOk()
            ->assertSee('4.5')
            ->assertSee('Pontos positivos')
            ->assertSee('Motor potente')
            ->assertSee('Pontos negativos')
            ->assertSee('Preço elevado')
            ->assertSee('Veredito')
            ->assertSee('O melhor custo-benefício da categoria.');
    }

    public function test_materia_comum_nao_exibe_bloco_de_analise(): void
    {
        $news = BlogCategory::create(['name' => 'Notícias', 'slug' => 'noticias']);
        $post = Post::create([
            'title' => 'Feira do setor pet',
            'slug' => 'feira-do-setor-pet',
            'content' => 'Cobertura da feira sem nota técnica.',
            'is_active' => true,
        ]);
        $post->blogCategories()->sync([$news->id]);

        $this->get('/materias-noticias/' . $post->slug)
            ->assertOk()
            ->assertSee('Feira do setor pet')
            ->assertDontSee('Pontos positivos')
            ->assertDontSee('Veredito');
    }

    public function test_vitrine_de_analises_lista_posts_da_categoria(): void
    {
        $this->analysisPost();

        $this->get('/analises-produtos')
            ->assertOk()
            ->assertSee('Soprador Turbo 10')
            ->assertSee('O melhor custo-benefício da categoria.');
    }

    public function test_detalhe_analise_slug_resolve_post(): void
    {
        $post = $this->analysisPost();

        $this->get('/analise/' . $post->slug)
            ->assertOk()
            ->assertSee('Soprador Turbo 10')
            ->assertSee('Motor potente')
            ->assertSee('O melhor custo-benefício da categoria.');
    }

    public function test_detalhe_analise_slug_ainda_resolve_review_legado(): void
    {
        \App\Models\ProductReview::create([
            'title' => 'Secador Legacy',
            'slug' => 'secador-legacy',
            'category' => 'Secadores',
            'rating' => 4.2,
            'pros' => 'Leve',
            'cons' => 'Filtro pequeno',
            'content' => 'Review legado da tabela product_reviews.',
            'verdict' => 'Bom para bancada pequena.',
            'is_active' => true,
        ]);

        $this->get('/analise/secador-legacy')
            ->assertOk()
            ->assertSee('Secador Legacy')
            ->assertSee('Leve')
            ->assertSee('Bom para bancada pequena.');
    }

    public function test_home_exibe_analise_com_nota_e_veredito(): void
    {
        Cache::forget(HomeController::CACHE_KEY);
        $this->analysisPost();

        $this->get('/')
            ->assertOk()
            ->assertSee('Soprador Turbo 10')
            ->assertSee('O melhor custo-benefício da categoria.');
    }
}
