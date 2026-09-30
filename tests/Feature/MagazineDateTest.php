<?php

namespace Tests\Feature;

use App\Livewire\Admin\ManageMagazines;
use App\Models\Magazine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class MagazineDateTest extends TestCase
{
    use RefreshDatabase;

    private function magazine(array $overrides = []): Magazine
    {
        return Magazine::create(array_merge([
            'title' => 'Revista '.uniqid(),
            'slug' => 'revista-'.uniqid(),
            'issue_period' => 'Setembro/2026',
            'pdf_path' => 'magazines/pdfs/teste.pdf',
            'cover_path' => 'magazines/covers/capa.jpg',
            'is_active' => true,
        ], $overrides));
    }

    public function test_formulario_nova_edicao_tem_data_de_postagem(): void
    {
        Livewire::test(ManageMagazines::class)
            ->call('toggleForm')
            ->assertSee('Data de postagem')
            ->assertSet('showForm', true)
            ->assertSet('created_at', now()->format('Y-m-d\TH:i'));
    }

    public function test_admin_define_data_ao_publicar_revista(): void
    {
        Storage::fake('public');

        Livewire::test(ManageMagazines::class)
            ->call('toggleForm')
            ->set('title', 'Mercado Pet em Foco')
            ->set('issue_period', 'Agosto/2023')
            ->set('created_at', '2023-08-15T14:00')
            ->set('pdf', UploadedFile::fake()->create('edicao.pdf', 120, 'application/pdf'))
            ->set('cover', UploadedFile::fake()->image('capa.jpg', 400, 560))
            ->call('save')
            ->assertHasNoErrors();

        $magazine = Magazine::where('title', 'Mercado Pet em Foco')->first();
        $this->assertNotNull($magazine);
        $this->assertEquals('2023-08-15 14:00:00', $magazine->created_at->format('Y-m-d H:i:s'));
        $this->assertEquals('agosto2023', $magazine->slug);
    }

    public function test_admin_altera_data_na_edicao(): void
    {
        $magazine = $this->magazine([
            'title' => 'Edição Editada',
            'slug' => 'edicao-editada',
            'issue_period' => 'Julho/2024',
        ]);

        Livewire::test(ManageMagazines::class)
            ->call('edit', $magazine->id)
            ->assertSee('Data de postagem')
            ->assertSet('created_at', $magazine->created_at->format('Y-m-d\TH:i'))
            ->set('created_at', '2024-01-10T09:30')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals('2024-01-10 09:30:00', $magazine->fresh()->created_at->format('Y-m-d H:i:s'));
    }

    public function test_banca_lista_do_mais_novo_para_o_mais_velho(): void
    {
        $antiga = $this->magazine([
            'title' => 'Aaa Revista Antiga',
            'slug' => 'revista-antiga',
            'issue_period' => 'Janeiro/2024',
        ]);
        $antiga->forceFill(['created_at' => now()->subDays(10)])->save();

        $nova = $this->magazine([
            'title' => 'Zzz Revista Nova',
            'slug' => 'revista-nova',
            'issue_period' => 'Setembro/2026',
        ]);
        $nova->forceFill(['created_at' => now()->subHour()])->save();

        $html = $this->get(route('magazines.index'))->assertOk()->getContent();

        $this->assertTrue(
            strpos($html, 'Zzz Revista Nova') < strpos($html, 'Aaa Revista Antiga'),
            'A revista mais nova deve aparecer antes da mais antiga na banca.'
        );
        $this->assertStringContainsString($nova->created_at->format('d/m/Y'), $html);
    }
}
