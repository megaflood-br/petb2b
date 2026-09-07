<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_paginacao_do_blog_admin_abre_a_segunda_pagina(): void
    {
        for ($i = 1; $i <= 15; $i++) {
            Post::create([
                'title' => 'Post paginacao n'.$i.'x',
                'slug' => 'post-paginacao-'.$i,
                'content' => '<p>Conteúdo.</p>',
                'is_active' => true,
                'created_at' => now()->subMinutes(16 - $i),
            ]);
        }

        $admin = $this->admin();

        $page1 = $this->actingAs($admin)->get(route('admin.blog'));
        $page1->assertOk()
            ->assertSee('Post paginacao n1x')
            ->assertDontSee('Post paginacao n15x')
            ->assertSee('/admin/blog?page=2', false);

        $page2 = $this->actingAs($admin)->get('/admin/blog?page=2');
        $page2->assertOk()
            ->assertSee('Post paginacao n15x')
            ->assertDontSee('Post paginacao n1x')
            ->assertSee('Showing');
    }
}
