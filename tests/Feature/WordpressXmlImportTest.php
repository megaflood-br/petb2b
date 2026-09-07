<?php

namespace Tests\Feature;

use App\Livewire\Admin\ManageSettings;
use App\Models\BlogCategory;
use App\Models\Post;
use App\Services\Wordpress\WordpressXmlImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class WordpressXmlImportTest extends TestCase
{
    use RefreshDatabase;

    private function fixturePath(): string
    {
        return base_path('tests/Fixtures/wordpress-export.xml');
    }

    public function test_importa_posts_publicados_e_ignora_rascunho_e_pagina(): void
    {
        $result = (new WordpressXmlImporter())->import($this->fixturePath(), downloadImages: false);

        $this->assertSame(2, $result->created);
        $this->assertSame(1, $result->skipped); // draft
        $this->assertSame(0, $result->failed);

        $this->assertDatabaseHas('posts', [
            'slug' => 'mercado-pet-cresce-no-brasil',
            'title' => 'Mercado pet cresce no Brasil',
            'is_active' => true,
            'is_featured' => true,
        ]);

        $featured = Post::where('slug', 'mercado-pet-cresce-no-brasil')->first();
        $this->assertSame('Resumo do crescimento do mercado pet.', $featured->meta_description);
        $this->assertSame('Mercado', $featured->meta_keywords);
        $this->assertTrue($featured->blogCategories->contains('slug', 'noticias'));
        $this->assertSame('2024-01-15 10:00:00', $featured->created_at->format('Y-m-d H:i:s'));

        $this->assertDatabaseHas('blog_categories', [
            'slug' => 'noticias',
            'name' => 'Notícias',
        ]);

        $uncategorized = Post::where('slug', 'guia-de-racas-sem-categoria')->first();
        $this->assertNotNull($uncategorized);
        $this->assertTrue($uncategorized->blogCategories->contains('slug', 'geral'));

        $this->assertDatabaseMissing('posts', ['slug' => 'rascunho-que-nao-deve-importar']);
        $this->assertDatabaseMissing('posts', ['slug' => 'sobre']);
    }

    public function test_segunda_importacao_nao_duplica_slug(): void
    {
        $importer = new WordpressXmlImporter();
        $importer->import($this->fixturePath(), downloadImages: false);
        $second = $importer->import($this->fixturePath(), downloadImages: false);

        $this->assertSame(0, $second->created);
        $this->assertSame(3, $second->skipped); // 2 published + 1 draft
        $this->assertSame(2, Post::count());
    }

    public function test_baixa_imagem_destacada_quando_habilitado(): void
    {
        Storage::fake('public');
        Http::fake([
            'cdn.example.com/*' => Http::response(str_repeat('JPEGDATA', 16), 200, [
                'Content-Type' => 'image/jpeg',
            ]),
        ]);

        (new WordpressXmlImporter())->import($this->fixturePath(), downloadImages: true);

        $post = Post::where('slug', 'mercado-pet-cresce-no-brasil')->first();
        $this->assertNotEmpty($post->image);
        Storage::disk('public')->assertExists($post->image);
    }

    public function test_xml_invalido_lanca_excecao(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'wxr');
        file_put_contents($tmp, '<html>não é wordpress</html>');

        $this->expectException(\InvalidArgumentException::class);
        (new WordpressXmlImporter())->import($tmp, downloadImages: false);
    }

    public function test_admin_importa_xml_pela_tela_de_configuracoes(): void
    {
        $upload = UploadedFile::fake()->createWithContent(
            'wordpress.xml',
            file_get_contents($this->fixturePath())
        );

        Livewire::test(ManageSettings::class)
            ->set('wordpressDownloadImages', false)
            ->set('wordpressXml', $upload)
            ->call('importWordpress')
            ->assertHasNoErrors()
            ->assertSee('Importação concluída');

        $this->assertSame(2, Post::count());
        $this->assertTrue(BlogCategory::where('slug', 'noticias')->exists());
    }

    public function test_admin_exige_arquivo_xml(): void
    {
        Livewire::test(ManageSettings::class)
            ->call('importWordpress')
            ->assertHasErrors(['wordpressXml']);

        $this->assertSame(0, Post::count());
    }
}
