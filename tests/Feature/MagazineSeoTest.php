<?php

namespace Tests\Feature;

use App\Models\Magazine;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MagazineSeoTest extends TestCase
{
    use RefreshDatabase;

    private function magazine(array $overrides = []): Magazine
    {
        return Magazine::create(array_merge([
            'title' => 'Revista Negócios Pet Setembro',
            'slug' => 'setembro-2026',
            'issue_period' => 'Setembro/2026',
            'pdf_path' => 'magazines/pdfs/setembro.pdf',
            'cover_path' => 'magazines/covers/capa-setembro.jpg',
            'is_active' => true,
        ], $overrides));
    }

    public function test_leitor_da_revista_usa_a_capa_no_open_graph(): void
    {
        $magazine = $this->magazine();

        $html = $this->get(route('magazines.show', $magazine))
            ->assertOk()
            ->assertSee('Revista Negócios Pet Setembro', false)
            ->getContent();

        $this->assertStringContainsString('property="og:image"', $html);
        $this->assertStringContainsString('storage/magazines/covers/capa-setembro.jpg', $html);
        $this->assertStringContainsString('name="twitter:image"', $html);
    }

    public function test_estante_usa_a_capa_mais_recente_no_open_graph(): void
    {
        $antiga = $this->magazine([
            'title' => 'Edição Antiga',
            'slug' => 'edicao-antiga',
            'issue_period' => 'Janeiro/2026',
            'cover_path' => 'magazines/covers/antiga.jpg',
        ]);
        $antiga->forceFill(['created_at' => now()->subMonth()])->save();

        $this->magazine([
            'title' => 'Edição Nova',
            'slug' => 'edicao-nova',
            'issue_period' => 'Setembro/2026',
            'cover_path' => 'magazines/covers/nova.jpg',
        ]);

        $html = $this->get(route('magazines.index'))->assertOk()->getContent();

        $this->assertStringContainsString('storage/magazines/covers/nova.jpg', $html);
        $this->assertStringContainsString('property="og:image"', $html);
    }

    public function test_capa_da_revista_vence_a_imagem_padrao_do_site(): void
    {
        Settings::set('seo_og_image', 'site/og-padrao.jpg');
        $this->magazine();

        $html = $this->get(route('magazines.show', 'setembro-2026'))->assertOk()->getContent();

        $cover = strpos($html, 'magazines/covers/capa-setembro.jpg');
        $default = strpos($html, 'site/og-padrao.jpg');

        $this->assertNotFalse($cover);
        $this->assertFalse($default);
    }

    public function test_revista_inativa_nao_abre(): void
    {
        $this->magazine(['is_active' => false, 'slug' => 'edicao-off']);

        $this->get(route('magazines.show', 'edicao-off'))->assertNotFound();
    }
}
