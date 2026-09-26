<?php

namespace Tests\Feature;

use App\Models\BlogCategory;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBlogEditTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => 'admin'])->save();

        return $user;
    }

    public function test_editar_abre_formulario_com_o_titulo_do_post(): void
    {
        $category = BlogCategory::create(['name' => 'Mercado', 'slug' => 'mercado']);
        $post = Post::create([
            'title' => 'Matéria para editar',
            'slug' => 'materia-para-editar',
            'content' => '<p>Texto original.</p>',
            'is_active' => true,
        ]);
        $post->blogCategories()->sync([$category->id]);

        $this->actingAs($this->admin())
            ->get(route('admin.blog'))
            ->assertOk()
            ->assertSee(route('admin.blog.edit', $post), false)
            ->assertSee('Editar');

        $this->actingAs($this->admin())
            ->get(route('admin.blog.edit', $post))
            ->assertOk()
            ->assertSee('Matéria para editar')
            ->assertSee('Texto original')
            ->assertSee('Salvar Postagem');
    }

    public function test_admin_atualiza_post_pelo_formulario(): void
    {
        $category = BlogCategory::create(['name' => 'Saúde', 'slug' => 'saude']);
        $post = Post::create([
            'title' => 'Título antigo',
            'slug' => 'titulo-antigo',
            'content' => '<p>Antigo.</p>',
            'is_active' => true,
        ]);
        $post->blogCategories()->sync([$category->id]);

        $this->actingAs($this->admin())
            ->put(route('admin.blog.update', $post), [
                'title' => 'Título novo',
                'content' => '<p>Conteúdo novo.</p>',
                'selected_categories' => [$category->id],
            ])
            ->assertRedirect(route('admin.blog'));

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Título novo',
            'slug' => 'titulo-novo',
        ]);
    }

    public function test_formulario_mostra_data_de_criacao_e_admin_pode_definir(): void
    {
        $category = BlogCategory::create(['name' => 'Mercado', 'slug' => 'mercado']);

        $this->actingAs($this->admin())
            ->get(route('admin.blog.create'))
            ->assertOk()
            ->assertSee('Data de criação')
            ->assertSee('name="created_at"', false);

        $this->actingAs($this->admin())
            ->post(route('admin.blog.store'), [
                'title' => 'Matéria com data antiga',
                'content' => '<p>Texto com data escolhida.</p>',
                'selected_categories' => [$category->id],
                'created_at' => '2024-03-10T09:30',
            ])
            ->assertRedirect(route('admin.blog'));

        $post = Post::where('title', 'Matéria com data antiga')->first();
        $this->assertNotNull($post);
        $this->assertEquals('2024-03-10 09:30:00', $post->created_at->format('Y-m-d H:i:s'));
    }
}
