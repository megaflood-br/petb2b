<?php

namespace Tests\Feature;

use App\Models\BlogCategory;
use App\Models\Breed;
use App\Models\Post;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_lista_home_secoes_e_conteudo_publico(): void
    {
        $category = BlogCategory::create(['name' => 'Notícias', 'slug' => 'noticias']);
        $post = Post::create([
            'title' => 'Matéria no sitemap',
            'slug' => 'materia-sitemap',
            'content' => 'Texto da matéria para o sitemap.',
            'is_active' => true,
        ]);
        $post->blogCategories()->sync([$category->id]);

        Supplier::create([
            'name' => 'Empresa Sitemap',
            'slug' => 'empresa-sitemap',
            'email' => 'sitemap@t.com',
            'description' => 'd',
            'category' => 'clinicas',
            'is_active' => true,
            'is_approved' => true,
        ]);

        Breed::create([
            'name' => 'Raca Sitemap',
            'slug' => 'raca-sitemap',
            'species' => 'Cão',
            'description' => 'Descrição da raça no sitemap.',
            'is_active' => true,
        ]);

        $xml = $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->getContent();

        $this->assertStringContainsString('<urlset', $xml);
        $this->assertStringContainsString('/noticias', $xml);
        $this->assertStringContainsString('/fornecedores', $xml);
        $this->assertStringContainsString('/racas', $xml);
        $this->assertStringContainsString('/vagas', $xml);
        $this->assertStringContainsString('/canis', $xml);
        $this->assertStringContainsString('/noticias/materia-sitemap', $xml);
        $this->assertStringContainsString('/fornecedores/empresa-sitemap', $xml);
        $this->assertStringContainsString('/racas/raca-sitemap', $xml);
        $this->assertStringContainsString('/categoria/noticias', $xml);
        $this->assertStringNotContainsString('/admin', $xml);
    }
}
