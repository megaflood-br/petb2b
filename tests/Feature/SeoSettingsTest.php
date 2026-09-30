<?php

namespace Tests\Feature;

use App\Livewire\Admin\ManageSettings;
use App\Models\User;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SeoSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create(['email' => 'admin_seo@t.com']);
        $user->forceFill(['role' => 'admin'])->save();

        return $user;
    }

    public function test_home_tem_titulo_canonical_schema_e_idioma(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('<html lang="pt-BR">', $html);
        $this->assertStringContainsString('<title>Revista Negócios Pet</title>', $html);
        $this->assertStringContainsString('rel="canonical"', $html);
        $this->assertStringContainsString('index, follow', $html);
        $this->assertStringContainsString('application/ld+json', $html);
        $this->assertStringContainsString('"@type":"Organization"', $html);
        $this->assertStringContainsString('"@type":"WebSite"', $html);
        $this->assertStringContainsString('SearchAction', $html);
    }

    public function test_busca_fica_noindex(): void
    {
        $this->get('/busca')
            ->assertOk()
            ->assertSee('noindex, follow', false);
    }

    public function test_robots_aponta_sitemap_e_bloqueia_admin(): void
    {
        $body = $this->get('/robots.txt')->assertOk()->assertHeader('Content-Type', 'text/plain; charset=UTF-8')->getContent();

        $this->assertStringContainsString('Disallow: /admin', $body);
        $this->assertStringContainsString('Sitemap: ', $body);
        $this->assertStringContainsString('/sitemap.xml', $body);
    }

    public function test_admin_salva_seo_favicon_e_verificacao(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(ManageSettings::class)
            ->assertSee('SEO e Google')
            ->assertSee('Favicon')
            ->set('seo_site_name', 'Revista Negócios Pet')
            ->set('seo_default_description', 'Portal B2B do mercado pet brasileiro com fornecedores e notícias.')
            ->set('seo_default_keywords', 'pet, fornecedores, revista')
            ->set('seo_google_verification', 'abc123verificacao')
            ->set('seo_ga4_id', 'G-TEST123')
            ->set('seo_favicon', UploadedFile::fake()->image('favicon.png', 32, 32))
            ->set('seo_og_image', UploadedFile::fake()->image('og.jpg', 1200, 630))
            ->call('saveSeo')
            ->assertHasNoErrors();

        $this->assertEquals('abc123verificacao', Settings::googleVerification());
        $this->assertEquals('G-TEST123', Settings::ga4Id());
        $this->assertNotNull(Settings::faviconPath());
        $this->assertNotNull(Settings::ogImagePath());
        Storage::disk('public')->assertExists(Settings::faviconPath());
        Storage::disk('public')->assertExists(Settings::ogImagePath());

        $this->get('/')
            ->assertOk()
            ->assertSee('google-site-verification', false)
            ->assertSee('abc123verificacao', false)
            ->assertSee('G-TEST123', false)
            ->assertSee('rel="icon"', false);
    }

    public function test_robots_inclui_regras_extras_do_painel(): void
    {
        Settings::set('seo_robots_extra', 'Disallow: /busca');

        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Disallow: /busca');
    }

    public function test_manutencao_nao_derruba_robots_nem_sitemap(): void
    {
        Settings::set('maintenance_enabled', '1');

        $this->get('/robots.txt')->assertOk();
        $this->get('/sitemap.xml')->assertOk();
        $this->get('/')->assertStatus(503);
    }
}
