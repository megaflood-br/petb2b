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
}
