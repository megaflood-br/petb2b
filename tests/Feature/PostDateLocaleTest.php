<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostDateLocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_data_da_materia_aparece_em_portugues(): void
    {
        $post = Post::create([
            'title' => 'Matéria de Setembro',
            'slug' => 'materia-de-setembro',
            'content' => 'Conteúdo de teste da matéria.',
            'is_active' => true,
            'is_featured' => true,
        ]);
        $post->forceFill(['created_at' => '2026-09-29 10:00:00'])->save();

        $this->assertSame('29 de set, 2026', $post->fresh()->publishedAt());
        $this->assertSame('29 de set', $post->fresh()->publishedAt('d \d\e M'));

        $this->get('/')
            ->assertOk()
            ->assertSee('29 de set')
            ->assertDontSee('29 de Sep')
            ->assertDontSee('Sep, 2026');

        $this->get(route('blog.show', ['prefixCategory' => 'geral', 'slug' => $post->slug]))
            ->assertOk()
            ->assertSee('29 de set, 2026')
            ->assertDontSee('29 de Sep, 2026');
    }
}
