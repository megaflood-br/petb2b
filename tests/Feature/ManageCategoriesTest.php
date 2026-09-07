<?php

namespace Tests\Feature;

use App\Livewire\Admin\ManageCategories;
use App\Models\BlogCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ManageCategoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_marca_categoria_para_nao_aparecer_na_home(): void
    {
        Livewire::test(ManageCategories::class)
            ->call('toggleForm')
            ->assertSee('Não exibir posts na home')
            ->set('name', 'Edição Impressa')
            ->set('hide_from_home', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('blog_categories', [
            'name' => 'Edição Impressa',
            'slug' => 'edicao-impressa',
            'hide_from_home' => true,
        ]);
    }

    public function test_lista_mostra_se_categoria_fica_oculta_na_home(): void
    {
        BlogCategory::create([
            'name' => 'Bastidores',
            'slug' => 'bastidores',
            'hide_from_home' => true,
        ]);

        Livewire::test(ManageCategories::class)
            ->assertSee('Bastidores')
            ->assertSee('Oculta');
    }

    public function test_admin_edita_opcao_de_ocultar_na_home(): void
    {
        $category = BlogCategory::create([
            'name' => 'Nutrição',
            'slug' => 'nutricao',
            'hide_from_home' => false,
        ]);

        Livewire::test(ManageCategories::class)
            ->call('edit', $category->id)
            ->assertSet('hide_from_home', false)
            ->set('hide_from_home', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertTrue($category->fresh()->hide_from_home);
    }
}
