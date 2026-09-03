<?php

namespace Tests\Feature;

use App\Http\Controllers\HomeController;
use App\Livewire\Admin\ManageReviews;
use App\Models\BlogCategory;
use App\Models\Post;
use App\Models\ProductReview;
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
        ], $overrides));

        $post->blogCategories()->sync([$category->id]);

        return $post->fresh('blogCategories');
    }

    public function test_admin_publica_analise_so_com_titulo_categoria_e_descricao(): void
    {
        Livewire::test(ManageReviews::class)
            ->set('title', 'Shampoo Neutro Pro')
            ->set('category', 'Higiene')
            ->set('content', 'Texto da análise com detalhes do produto testado.')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDontSee('Nota')
            ->assertDontSee('Prós')
            ->assertDontSee('Veredito');

        $this->assertDatabaseHas('product_reviews', [
            'title' => 'Shampoo Neutro Pro',
            'category' => 'Higiene',
            'content' => 'Texto da análise com detalhes do produto testado.',
            'rating' => null,
            'pros' => null,
            'cons' => null,
            'verdict' => null,
        ]);
    }

    public function test_formulario_admin_nao_tem_nota_pros_contras_nem_veredito(): void
    {
        Livewire::test(ManageReviews::class)
            ->call('toggleForm')
            ->assertSee('Título')
            ->assertSee('Categoria')
            ->assertSee('Descrição')
            ->assertDontSee('Nota (0 a 5)')
            ->assertDontSee('Veredito Final')
            ->assertDontSeeHtml('wire:model="pros"')
            ->assertDontSeeHtml('wire:model="cons"');
    }

    public function test_artigo_publico_nao_exibe_bloco_de_nota(): void
    {
        $post = $this->analysisPost();

        $this->get('/materias-analises-de-produtos/' . $post->slug)
            ->assertOk()
            ->assertSee('Soprador Turbo 10')
            ->assertSee('Análise completa do soprador')
            ->assertDontSee('Pontos positivos')
            ->assertDontSee('Veredito');
    }

    public function test_vitrine_de_analises_lista_posts_da_categoria(): void
    {
        $this->analysisPost();

        $this->get('/analises-produtos')
            ->assertOk()
            ->assertSee('Soprador Turbo 10')
            ->assertSee('Análise completa do soprador');
    }

    public function test_detalhe_analise_slug_resolve_post(): void
    {
        $post = $this->analysisPost();

        $this->get('/analise/' . $post->slug)
            ->assertOk()
            ->assertSee('Soprador Turbo 10')
            ->assertSee('Análise completa do soprador')
            ->assertDontSee('Pontos positivos');
    }

    public function test_detalhe_analise_slug_ainda_resolve_review_legado(): void
    {
        ProductReview::create([
            'title' => 'Secador Legacy',
            'slug' => 'secador-legacy',
            'category' => 'Secadores',
            'content' => 'Review legado da tabela product_reviews.',
            'is_active' => true,
        ]);

        $this->get('/analise/secador-legacy')
            ->assertOk()
            ->assertSee('Secador Legacy')
            ->assertSee('Review legado da tabela product_reviews.')
            ->assertDontSee('Pontos positivos');
    }

    public function test_home_exibe_analise_pelo_titulo(): void
    {
        Cache::forget(HomeController::CACHE_KEY);
        $this->analysisPost();

        $this->get('/')
            ->assertOk()
            ->assertSee('Soprador Turbo 10');
    }
}
