<?php

namespace Tests\Feature;

use App\Livewire\FavoriteButton;
use App\Livewire\FavoritesIndex;
use App\Models\Classified;
use App\Models\Favorite;
use App\Models\Post;
use App\Models\Supplier;
use App\Models\User;
use App\Support\FavoriteCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FavoritesTest extends TestCase
{
    use RefreshDatabase;

    private function post(array $overrides = []): Post
    {
        return Post::create(array_merge([
            'title' => 'Matéria Favorita',
            'slug' => 'materia-favorita-'.uniqid(),
            'content' => 'Texto da matéria.',
            'is_active' => true,
        ], $overrides));
    }

    private function classified(): Classified
    {
        $supplier = Supplier::create([
            'name' => 'Empresa Favoritos',
            'email' => 'fav_'.uniqid().'@t.com',
            'description' => 'd',
            'category' => 'racas',
            'is_active' => true,
            'is_approved' => true,
        ]);

        return Classified::create([
            'supplier_id' => $supplier->id,
            'title' => 'Máquina de Tosa',
            'slug' => 'maquina-tosa-'.uniqid(),
            'description' => 'Equipamento.',
            'price' => 990,
            'condition' => 'Usado',
            'is_active' => true,
        ]);
    }

    public function test_visitante_e_redirecionado_ao_favoritar(): void
    {
        $post = $this->post();

        Livewire::test(FavoriteButton::class, ['favoritable' => $post])
            ->call('toggle')
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('favorites', 0);
    }

    public function test_usuario_favorita_e_desfavorita_materia(): void
    {
        $user = User::factory()->create();
        $post = $this->post();

        Livewire::actingAs($user)
            ->test(FavoriteButton::class, ['favoritable' => $post])
            ->assertSet('favorited', false)
            ->call('toggle')
            ->assertSet('favorited', true);

        $this->assertTrue($user->fresh()->hasFavorited($post));
        $this->assertEquals(FavoriteCatalog::MATERIAS, Favorite::first()->folder);

        Livewire::actingAs($user)
            ->test(FavoriteButton::class, ['favoritable' => $post])
            ->assertSet('favorited', true)
            ->call('toggle')
            ->assertSet('favorited', false);

        $this->assertFalse($user->fresh()->hasFavorited($post));
        $this->assertDatabaseCount('favorites', 0);
    }

    public function test_favoritos_ficam_em_pastas_por_categoria(): void
    {
        $user = User::factory()->create();
        $post = $this->post(['title' => 'Guia de Banho e Tosa']);
        $ad = $this->classified();
        $supplier = $ad->supplier;

        $user->toggleFavorite($post);
        $user->toggleFavorite($ad);
        $user->toggleFavorite($supplier);

        $this->actingAs($user)
            ->get(route('favorites.index'))
            ->assertOk()
            ->assertSee('Meus')
            ->assertSee('Favoritos')
            ->assertSee('Matérias')
            ->assertSee('Classificados')
            ->assertSee('Fornecedores');

        Livewire::actingAs($user)
            ->test(FavoritesIndex::class)
            ->call('openFolder', FavoriteCatalog::MATERIAS)
            ->assertSee('Guia de Banho e Tosa')
            ->call('openFolder', FavoriteCatalog::CLASSIFICADOS)
            ->assertSee('Máquina de Tosa')
            ->call('openFolder', FavoriteCatalog::FORNECEDORES)
            ->assertSee('Empresa Favoritos');
    }

    public function test_cards_publicos_tem_botao_de_favoritar(): void
    {
        $post = $this->post(['is_featured' => true]);
        $ad = $this->classified();

        $this->get('/')
            ->assertOk()
            ->assertSee('Favoritar', false);

        $this->get(route('blog.show', ['prefixCategory' => 'geral', 'slug' => $post->slug]))
            ->assertOk()
            ->assertSee('Favoritar', false);

        $this->get(route('classifieds.index'))
            ->assertOk()
            ->assertSee('Favoritar', false);

        $this->get(route('suppliers.index'))
            ->assertOk()
            ->assertSee('Favoritar', false);

        $this->get(route('classifieds.show', $ad->slug))
            ->assertOk()
            ->assertSee('Favoritar', false);
    }

    public function test_visitante_nao_abre_a_pagina_de_favoritos(): void
    {
        $this->get(route('favorites.index'))->assertRedirect();
    }
}
