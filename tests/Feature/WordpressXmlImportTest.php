<?php

namespace Tests\Feature;

use App\Models\BlogCategory;
use App\Models\Post;
use App\Models\User;
use App\Services\Wordpress\WordpressImportResult;
use App\Services\Wordpress\WordpressXmlImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
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

        $result = (new WordpressXmlImporter())->import($this->fixturePath(), downloadImages: true);

        $post = Post::where('slug', 'mercado-pet-cresce-no-brasil')->first();
        $this->assertNotEmpty($post->image);
        Storage::disk('public')->assertExists($post->image);
        $this->assertStringContainsString('/storage/blog/posts/', $post->content);
        $this->assertStringNotContainsString('cdn.example.com/wp-content', $post->content);
        $this->assertGreaterThanOrEqual(2, $result->imagesDownloaded);
    }

    public function test_segunda_importacao_completa_imagens_que_faltaram(): void
    {
        Storage::fake('public');
        Http::fake([
            'cdn.example.com/*' => Http::response(str_repeat('JPEGDATA', 16), 200, [
                'Content-Type' => 'image/jpeg',
            ]),
        ]);

        (new WordpressXmlImporter())->import($this->fixturePath(), downloadImages: false);

        $post = Post::where('slug', 'mercado-pet-cresce-no-brasil')->first();
        $this->assertNull($post->image);
        $this->assertStringContainsString('cdn.example.com/wp-content', $post->content);

        $second = (new WordpressXmlImporter())->import($this->fixturePath(), downloadImages: true);

        $this->assertSame(0, $second->created);
        $this->assertSame(1, $second->updated);
        $this->assertSame(2, Post::count());

        $post->refresh();
        $this->assertNotEmpty($post->image);
        Storage::disk('public')->assertExists($post->image);
        $this->assertStringContainsString('/storage/blog/posts/', $post->content);
        $this->assertStringNotContainsString('cdn.example.com/wp-content', $post->content);
    }

    public function test_usa_guid_do_anexo_quando_nao_ha_attachment_url(): void
    {
        Storage::fake('public');
        Http::fake([
            'cdn.example.com/*' => Http::response(str_repeat('JPEGDATA', 16), 200, [
                'Content-Type' => 'image/jpeg',
            ]),
        ]);

        $tmp = tempnam(sys_get_temp_dir(), 'wxr');
        file_put_contents($tmp, <<<'XML'
<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0"
     xmlns:content="http://purl.org/rss/1.0/modules/content/"
     xmlns:wp="http://wordpress.org/export/1.2/">
<channel>
    <wp:base_blog_url>https://cdn.example.com</wp:base_blog_url>
    <item>
        <title>Post só com guid</title>
        <content:encoded><![CDATA[<p>Texto.</p>]]></content:encoded>
        <wp:post_id>21</wp:post_id>
        <wp:post_name>post-so-com-guid</wp:post_name>
        <wp:status>publish</wp:status>
        <wp:post_type>post</wp:post_type>
        <wp:postmeta>
            <wp:meta_key><![CDATA[_thumbnail_id]]></wp:meta_key>
            <wp:meta_value><![CDATA[88]]></wp:meta_value>
        </wp:postmeta>
    </item>
    <item>
        <title>capa-guid.jpg</title>
        <guid isPermaLink="false">https://cdn.example.com/wp-content/uploads/2024/01/guid-capa.jpg</guid>
        <wp:post_id>88</wp:post_id>
        <wp:post_type>attachment</wp:post_type>
        <wp:status>inherit</wp:status>
    </item>
</channel>
</rss>
XML);

        (new WordpressXmlImporter())->import($tmp, downloadImages: true);

        $post = Post::where('slug', 'post-so-com-guid')->first();
        $this->assertNotNull($post);
        $this->assertNotEmpty($post->image);
        Storage::disk('public')->assertExists($post->image);
    }

    public function test_baixa_imagem_relativa_usando_url_do_site(): void
    {
        Storage::fake('public');
        Http::fake([
            'cdn.example.com/*' => Http::response(str_repeat('PNGDATA', 16), 200, [
                'Content-Type' => 'image/png',
            ]),
        ]);

        $tmp = tempnam(sys_get_temp_dir(), 'wxr');
        file_put_contents($tmp, <<<'XML'
<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0"
     xmlns:content="http://purl.org/rss/1.0/modules/content/"
     xmlns:wp="http://wordpress.org/export/1.2/">
<channel>
    <wp:base_blog_url>https://cdn.example.com</wp:base_blog_url>
    <item>
        <title>Post com imagem relativa</title>
        <content:encoded><![CDATA[<p><img src="/wp-content/uploads/2024/01/relativa.png" alt="Foto" /></p>]]></content:encoded>
        <wp:post_id>22</wp:post_id>
        <wp:post_name>post-com-imagem-relativa</wp:post_name>
        <wp:status>publish</wp:status>
        <wp:post_type>post</wp:post_type>
    </item>
</channel>
</rss>
XML);

        (new WordpressXmlImporter())->import($tmp, downloadImages: true);

        $post = Post::where('slug', 'post-com-imagem-relativa')->first();
        $this->assertNotNull($post);
        $this->assertNotEmpty($post->image);
        $this->assertStringContainsString('/storage/blog/posts/', $post->content);
        $this->assertStringNotContainsString('/wp-content/uploads/', $post->content);
    }

    public function test_xml_invalido_lanca_excecao(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'wxr');
        file_put_contents($tmp, '<html>não é wordpress</html>');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('parece HTML');
        (new WordpressXmlImporter())->import($tmp, downloadImages: false);
    }

    public function test_importa_wxr_realista_com_ampersand_e_cdata(): void
    {
        $path = base_path('tests/Fixtures/wordpress-wxr-realista.xml');
        $result = (new WordpressXmlImporter())->import($path, downloadImages: false);

        $this->assertSame(1, $result->created);
        $this->assertSame(0, $result->failed);
        $this->assertDatabaseHas('posts', [
            'slug' => 'lojas-pet-apostam-em-servicos',
            'title' => 'Lojas pet apostam em serviços',
            'is_active' => true,
        ]);
        $post = Post::where('slug', 'lojas-pet-apostam-em-servicos')->first();
        $this->assertTrue($post->blogCategories->contains('slug', 'mercado'));
    }

    public function test_xml_cortado_explica_upload_incompleto(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'wxr');
        file_put_contents($tmp, "<?xml version=\"1.0\"?><rss version=\"2.0\"><channel><title>Site</title><item><title>Post");

        try {
            (new WordpressXmlImporter())->import($tmp, downloadImages: false);
            $this->fail('Deveria recusar XML incompleto.');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('incompleto', $e->getMessage());
        }
    }

    public function test_admin_importa_xml_pela_tela_de_configuracoes(): void
    {
        $this->actingAs($this->admin());

        $upload = UploadedFile::fake()->createWithContent(
            'wordpress.xml',
            file_get_contents($this->fixturePath())
        );

        $this->post(route('admin.wordpress-import'), [
            'wordpress_xml' => $upload,
        ])->assertRedirect()->assertSessionHas('message');

        $this->assertSame(2, Post::count());
        $this->assertTrue(BlogCategory::where('slug', 'noticias')->exists());
    }

    public function test_admin_exige_arquivo_xml(): void
    {
        $this->actingAs($this->admin());

        $this->from(route('admin.settings'))
            ->post(route('admin.wordpress-import'))
            ->assertRedirect(route('admin.settings'));

        $this->assertSame(0, Post::count());
    }

    public function test_convidado_nao_importa(): void
    {
        $upload = UploadedFile::fake()->createWithContent(
            'wordpress.xml',
            file_get_contents($this->fixturePath())
        );

        $this->post(route('admin.wordpress-import'), [
            'wordpress_xml' => $upload,
        ])->assertRedirect(route('login'));
    }

    public function test_admin_importa_xml_em_lotes_com_barra_de_progresso(): void
    {
        Storage::fake('local');
        $this->actingAs($this->admin());

        $upload = UploadedFile::fake()->createWithContent(
            'wordpress.xml',
            file_get_contents($this->fixturePath())
        );

        $start = $this->withHeaders([
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ])->post(route('admin.wordpress-import'), [
            'wordpress_xml' => $upload,
            'download_images' => '0',
        ]);

        $start->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('total', 2)
            ->assertJsonPath('skipped', 1);

        $this->assertSame(0, Post::count());

        $token = $start->json('token');
        $this->assertNotEmpty($token);
        Storage::disk('local')->assertExists("wxr/{$token}.jsonl");

        $batch = $this->postJson(route('admin.wordpress-import.process'), [
            'token' => $token,
        ]);

        $batch->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('done', true)
            ->assertJsonPath('processed', 2)
            ->assertJsonPath('percent', 100)
            ->assertJsonPath('created', 2);

        $this->assertSame(2, Post::count());
        $this->assertDatabaseHas('posts', ['slug' => 'mercado-pet-cresce-no-brasil']);
        Storage::disk('local')->assertMissing("wxr/{$token}.jsonl");
    }

    public function test_lote_com_imagens_processa_um_post_por_vez(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        Http::fake([
            'cdn.example.com/*' => Http::response(str_repeat('JPEGDATA', 16), 200, [
                'Content-Type' => 'image/jpeg',
            ]),
        ]);

        $this->actingAs($this->admin());

        $upload = UploadedFile::fake()->createWithContent(
            'wordpress.xml',
            file_get_contents($this->fixturePath())
        );

        $token = $this->withHeaders([
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ])->post(route('admin.wordpress-import'), [
            'wordpress_xml' => $upload,
            'download_images' => '1',
        ])->assertOk()->json('token');

        $postsPhase = $this->postJson(route('admin.wordpress-import.process'), ['token' => $token]);
        $postsPhase->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('done', false)
            ->assertJsonPath('phase', 'images')
            ->assertJsonPath('created', 2);

        $this->assertSame(2, Post::count());

        $firstImages = $this->postJson(route('admin.wordpress-import.process'), ['token' => $token]);
        $firstImages->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('done', false)
            ->assertJsonPath('processed', 1);

        $secondImages = $this->postJson(route('admin.wordpress-import.process'), ['token' => $token]);
        $secondImages->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('done', true)
            ->assertJsonPath('processed', 2);

        $post = Post::where('slug', 'mercado-pet-cresce-no-brasil')->first();
        $this->assertNotEmpty($post->image);
        $this->assertStringContainsString('/storage/blog/posts/', $post->content);
    }

    public function test_lote_exige_token_valido(): void
    {
        $this->actingAs($this->admin());

        $this->postJson(route('admin.wordpress-import.process'), [
            'token' => '11111111-1111-4111-8111-111111111111',
        ])->assertStatus(422)->assertJsonPath('ok', false);
    }

    public function test_lote_pode_pular_post_travado(): void
    {
        Storage::fake('local');
        $this->actingAs($this->admin());

        $upload = UploadedFile::fake()->createWithContent(
            'wordpress.xml',
            file_get_contents($this->fixturePath())
        );

        $token = $this->withHeaders([
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ])->post(route('admin.wordpress-import'), [
            'wordpress_xml' => $upload,
            'download_images' => '0',
        ])->assertOk()->json('token');

        $this->postJson(route('admin.wordpress-import.process'), [
            'token' => $token,
            'skip' => true,
        ])->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('done', false)
            ->assertJsonPath('processed', 1)
            ->assertJsonPath('failed', 1);

        $this->assertSame(0, Post::count());

        $this->postJson(route('admin.wordpress-import.process'), [
            'token' => $token,
        ])->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('done', true)
            ->assertJsonPath('created', 1);

        $this->assertSame(1, Post::count());
        $this->assertDatabaseHas('posts', ['slug' => 'guia-de-racas-sem-categoria']);
        $this->assertDatabaseMissing('posts', ['slug' => 'mercado-pet-cresce-no-brasil']);
    }

    public function test_copia_imagem_da_pasta_uploads_local_quando_a_url_e_deste_site(): void
    {
        Storage::fake('public');
        Http::fake([
            '*' => Http::response('not-an-image', 404),
        ]);

        $root = sys_get_temp_dir() . '/wp-uploads-' . uniqid();
        mkdir($root . '/2024/01', 0755, true);
        $jpeg = "\xFF\xD8\xFF" . str_repeat('J', 64);
        file_put_contents($root . '/2024/01/capa.jpg', $jpeg);

        $tmp = tempnam(sys_get_temp_dir(), 'wxr');
        file_put_contents($tmp, <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0"
     xmlns:content="http://purl.org/rss/1.0/modules/content/"
     xmlns:wp="http://wordpress.org/export/1.2/">
<channel>
    <wp:base_blog_url>https://rnpet.com.br</wp:base_blog_url>
    <item>
        <title>Post com foto local</title>
        <content:encoded><![CDATA[<p><img src="https://rnpet.com.br/wp-content/uploads/2024/01/capa.jpg" alt="Capa" /></p>]]></content:encoded>
        <wp:post_id>41</wp:post_id>
        <wp:post_name>post-com-foto-local</wp:post_name>
        <wp:status>publish</wp:status>
        <wp:post_type>post</wp:post_type>
    </item>
</channel>
</rss>
XML);

        $result = (new WordpressXmlImporter())
            ->setUploadRoots([$root])
            ->import($tmp, downloadImages: true);

        $this->assertSame(1, $result->imagesDownloaded);
        $this->assertSame(0, $result->imagesFailed);

        $post = Post::where('slug', 'post-com-foto-local')->first();
        $this->assertNotNull($post);
        $this->assertNotEmpty($post->image);
        Storage::disk('public')->assertExists($post->image);
        $this->assertStringContainsString('/storage/blog/posts/', $post->content);
        $this->assertStringNotContainsString('/wp-content/uploads/', $post->content);
        Http::assertNothingSent();
    }

    public function test_resumo_explica_quando_nenhuma_imagem_baixou(): void
    {
        $result = new WordpressImportResult();
        $result->skipped = 2;
        $result->imagesFailed = 5;

        $this->assertStringContainsString('wp-content/uploads', $result->summary());
    }

    public function test_orcamento_de_imagens_continua_no_mesmo_post(): void
    {
        Storage::fake('public');
        Http::fake([
            'cdn.example.com/*' => Http::response(str_repeat('JPEGDATA', 16), 200, [
                'Content-Type' => 'image/jpeg',
            ]),
        ]);

        $tmp = tempnam(sys_get_temp_dir(), 'wxr');
        file_put_contents($tmp, <<<'XML'
<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0"
     xmlns:content="http://purl.org/rss/1.0/modules/content/"
     xmlns:wp="http://wordpress.org/export/1.2/">
<channel>
    <wp:base_blog_url>https://cdn.example.com</wp:base_blog_url>
    <item>
        <title>Post com várias fotos</title>
        <content:encoded><![CDATA[
            <p><img src="https://cdn.example.com/wp-content/uploads/2024/01/a.jpg" /></p>
            <p><img src="https://cdn.example.com/wp-content/uploads/2024/01/b.jpg" /></p>
            <p><img src="https://cdn.example.com/wp-content/uploads/2024/01/c.jpg" /></p>
            <p><img src="https://cdn.example.com/wp-content/uploads/2024/01/d.jpg" /></p>
        ]]></content:encoded>
        <wp:post_id>31</wp:post_id>
        <wp:post_name>post-com-varias-fotos</wp:post_name>
        <wp:status>publish</wp:status>
        <wp:post_type>post</wp:post_type>
    </item>
</channel>
</rss>
XML);

        $importer = new WordpressXmlImporter();
        $extracted = $importer->extract($tmp);
        $result = new WordpressImportResult();

        $first = $importer
            ->setImageBudget(2)
            ->setSiteBaseUrl('https://cdn.example.com')
            ->importPayload($extracted['posts'][0], $result, true);

        $this->assertTrue($first['inserted']);
        $this->assertFalse($first['done']);

        $second = $importer
            ->setImageBudget(2)
            ->importPayload($extracted['posts'][0], $result, true);

        $this->assertTrue($second['done']);
        $this->assertFalse($second['inserted']);

        $post = Post::where('slug', 'post-com-varias-fotos')->first();
        $this->assertNotNull($post);
        $this->assertStringNotContainsString('cdn.example.com/wp-content', $post->content);
    }

    private function admin(): User
    {
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin_' . uniqid() . '@t.com',
            'password' => 'secret',
        ]);
        $user->forceFill([
            'role' => 'admin',
            'email_verified_at' => now(),
        ])->save();

        return $user;
    }
}
