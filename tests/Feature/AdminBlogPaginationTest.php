<?php

namespace Tests\Feature;

use App\Livewire\Admin\ManageBlog;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminBlogPaginationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => 'admin'])->save();

        return $user;
    }

    public function test_blog_admin_carrega_mais_posts_ao_rolar(): void
    {
        for ($i = 1; $i <= 15; $i++) {
            Post::create([
                'title' => 'Post paginacao n'.$i.'x',
                'slug' => 'post-paginacao-'.$i,
                'content' => '<p>Conteúdo.</p>',
                'is_active' => true,
            ])->forceFill(['created_at' => now()->subMinutes(16 - $i)])->save();
        }

        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.blog'))
            ->assertOk()
            ->assertSee('Post paginacao n15x')
            ->assertDontSee('Post paginacao n1x')
            ->assertSee('Role para ver mais')
            ->assertDontSee('/admin/blog?page=2', false);

        Livewire::actingAs($admin)
            ->test(ManageBlog::class)
            ->assertSee('Post paginacao n15x')
            ->assertDontSee('Post paginacao n1x')
            ->call('loadMore')
            ->assertSee('Post paginacao n1x');
    }
}
