<?php

namespace Tests\Feature;

use App\Livewire\Admin\ManageBreeds;
use App\Models\Breed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BreedGuideTest extends TestCase
{
    use RefreshDatabase;

    private function makeBreed(array $overrides = []): Breed
    {
        return Breed::create(array_merge([
            'name' => 'Golden Retriever',
            'species' => 'Cão',
            'size' => 'Grande',
            'origin' => 'Escócia',
            'temperament' => 'Dócil e brincalhão',
            'description' => 'Raça amigável e inteligente, ótima para famílias.',
            'is_active' => true,
        ], $overrides));
    }

    public function test_listagem_publica_filtra_ativas_e_por_especie(): void
    {
        $this->makeBreed(['name' => 'Cao Ativo Visivel', 'species' => 'Cão']);
        $this->makeBreed(['name' => 'Gato Siames', 'species' => 'Gato']);
        $this->makeBreed(['name' => 'Raca Inativa Oculta', 'is_active' => false]);

        // Sem filtro: mostra ativas, esconde inativa.
        $this->get('/racas')
            ->assertOk()
            ->assertSee('Cao Ativo Visivel')
            ->assertSee('Gato Siames')
            ->assertDontSee('Raca Inativa Oculta');

        // Filtro por espécie Gato.
        $this->get('/racas?species=' . urlencode('Gato'))
            ->assertOk()
            ->assertSee('Gato Siames')
            ->assertDontSee('Cao Ativo Visivel');
    }

    public function test_listagem_publica_ordena_do_mais_novo_para_o_mais_velho(): void
    {
        $antiga = $this->makeBreed(['name' => 'Aaa Raca Antiga']);
        $antiga->forceFill(['created_at' => now()->subDays(10)])->save();

        $nova = $this->makeBreed(['name' => 'Zzz Raca Nova']);
        $nova->forceFill(['created_at' => now()->subHour()])->save();

        $html = $this->get('/racas')->assertOk()->getContent();

        $this->assertNotFalse(strpos($html, 'Zzz Raca Nova'));
        $this->assertNotFalse(strpos($html, 'Aaa Raca Antiga'));
        $this->assertLessThan(
            strpos($html, 'Zzz Raca Nova'),
            strpos($html, 'Aaa Raca Antiga'),
            'A raça mais nova deve aparecer antes da mais antiga.'
        );
    }

    public function test_detalhe_ativa_carrega_inativa_404(): void
    {
        $ativa = $this->makeBreed(['name' => 'Bulldog Frances']);
        $inativa = $this->makeBreed(['is_active' => false]);

        $this->get('/racas/' . $ativa->slug)->assertOk()->assertSee('Bulldog Frances');
        $this->get('/racas/' . $inativa->slug)->assertNotFound();
    }

    public function test_admin_cadastra_raca(): void
    {
        Livewire::test(ManageBreeds::class)
            ->set('name', 'Poodle')
            ->set('species', 'Cão')
            ->set('size', 'Pequeno')
            ->set('description', 'Raça inteligente, ativa e de fácil adestramento.')
            ->call('save');

        $this->assertDatabaseHas('breeds', ['name' => 'Poodle', 'species' => 'Cão']);
        $breed = Breed::where('name', 'Poodle')->first();
        $this->assertNotEmpty($breed->slug);
    }

    public function test_admin_define_data_de_criacao_da_raca(): void
    {
        Livewire::test(ManageBreeds::class)
            ->call('toggleForm')
            ->assertSee('Data de criação')
            ->set('name', 'Beagle')
            ->set('species', 'Cão')
            ->set('description', 'Raça farejadora, alegre e de médio porte.')
            ->set('created_at', '2023-08-15T14:00')
            ->call('save')
            ->assertHasNoErrors();

        $breed = Breed::where('name', 'Beagle')->first();
        $this->assertNotNull($breed);
        $this->assertEquals('2023-08-15 14:00:00', $breed->created_at->format('Y-m-d H:i:s'));
    }
}
