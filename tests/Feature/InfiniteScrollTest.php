<?php

namespace Tests\Feature;

use App\Livewire\BlogPostGrid;
use App\Livewire\BreedList;
use App\Livewire\JobList;
use App\Models\BlogCategory;
use App\Models\Breed;
use App\Models\JobPosting;
use App\Models\Post;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InfiniteScrollTest extends TestCase
{
    use RefreshDatabase;

    public function test_racas_carrega_mais_ao_rolar(): void
    {
        for ($i = 1; $i <= 15; $i++) {
            Breed::create([
                'name' => sprintf('Raca Infinita %02d', $i),
                'species' => 'Cão',
                'description' => 'Descrição longa o suficiente da raça '.$i.'.',
                'is_active' => true,
            ]);
        }

        $this->get('/racas')
            ->assertOk()
            ->assertSee('Raca Infinita 01')
            ->assertSee('Role para ver mais')
            ->assertDontSee('?page=2')
            ->assertDontSee('Raca Infinita 15');

        Livewire::test(BreedList::class)
            ->assertSee('Raca Infinita 01')
            ->assertDontSee('Raca Infinita 15')
            ->call('loadMore')
            ->assertSee('Raca Infinita 01')
            ->assertSee('Raca Infinita 15');
    }

    public function test_noticias_carrega_mais_ao_rolar(): void
    {
        for ($i = 1; $i <= 8; $i++) {
            Post::create([
                'title' => 'Noticia infinita '.$i,
                'slug' => 'noticia-infinita-'.$i,
                'content' => '<p>Conteúdo da notícia '.$i.'.</p>',
                'is_active' => true,
            ]);
        }

        $this->get('/noticias')
            ->assertOk()
            ->assertSee('Noticia infinita 8')
            ->assertSee('Role para ver mais')
            ->assertDontSee('?page=2');

        Livewire::test(BlogPostGrid::class)
            ->assertSee('Noticia infinita 8')
            ->assertDontSee('Noticia infinita 1')
            ->call('loadMore')
            ->assertSee('Noticia infinita 8')
            ->assertSee('Noticia infinita 1');
    }

    public function test_noticias_por_categoria_respeita_o_filtro(): void
    {
        $racas = BlogCategory::create(['name' => 'Raças', 'slug' => 'racas']);
        $noticias = BlogCategory::create(['name' => 'Notícias', 'slug' => 'noticias']);

        $race = Post::create([
            'title' => 'So na categoria racas',
            'slug' => 'so-racas-scroll',
            'content' => '<p>Raça.</p>',
            'is_active' => true,
        ]);
        $race->blogCategories()->sync([$racas->id]);

        $news = Post::create([
            'title' => 'So na categoria noticias',
            'slug' => 'so-noticias-scroll',
            'content' => '<p>News.</p>',
            'is_active' => true,
        ]);
        $news->blogCategories()->sync([$noticias->id]);

        $this->get('/categoria/racas')
            ->assertOk()
            ->assertSee('So na categoria racas')
            ->assertDontSee('So na categoria noticias');
    }

    public function test_vagas_carrega_mais_ao_rolar(): void
    {
        $supplier = Supplier::create([
            'name' => 'Empresa Scroll',
            'email' => 'scroll_'.uniqid().'@t.com',
            'description' => 'd',
            'category' => 'racas',
            'city' => 'Atibaia',
            'state' => 'SP',
            'is_active' => true,
            'is_approved' => true,
        ]);

        for ($i = 1; $i <= 14; $i++) {
            JobPosting::create([
                'supplier_id' => $supplier->id,
                'title' => 'Vaga infinita '.$i,
                'description' => 'Descrição completa da vaga '.$i.'.',
                'type' => 'CLT',
                'city' => 'Atibaia',
                'state' => 'SP',
                'how_to_apply' => 'rh@empresa.com',
                'is_active' => true,
            ]);
        }

        Livewire::test(JobList::class)
            ->assertSee('Vaga infinita 14')
            ->assertDontSee('Vaga infinita 1')
            ->call('loadMore')
            ->assertSee('Vaga infinita 14')
            ->assertSee('Vaga infinita 1');
    }

    public function test_filtro_reseta_o_carregamento_infinito(): void
    {
        for ($i = 1; $i <= 15; $i++) {
            Breed::create([
                'name' => ($i <= 13 ? 'Cao Infinito '.$i : 'Gato Infinito '.$i),
                'species' => $i <= 13 ? 'Cão' : 'Gato',
                'description' => 'Descrição longa o suficiente da raça '.$i.'.',
                'is_active' => true,
            ]);
        }

        Livewire::test(BreedList::class)
            ->call('loadMore')
            ->assertSee('Cao Infinito 1')
            ->assertSee('Gato Infinito 15')
            ->set('species', 'Gato')
            ->assertSee('Gato Infinito 15')
            ->assertDontSee('Cao Infinito 1');
    }
}
