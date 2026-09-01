<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchableTest extends TestCase
{
    use RefreshDatabase;

    private function supplier(string $name, string $description): Supplier
    {
        return Supplier::create([
            'name' => $name,
            'email' => 's_' . uniqid() . '@t.com',
            'description' => $description,
            'category' => 'racas',
            'is_active' => true,
            'is_approved' => true,
        ]);
    }

    public function test_busca_fornecedor_por_nome_e_descricao(): void
    {
        $this->supplier('Alfa Premium', 'produtos de qualidade');
        $this->supplier('Beta Comum', 'itens premium aqui');
        $this->supplier('Gama Loja', 'nada relacionado');

        // "premium" aparece no nome de A e na descrição de B.
        $this->assertEquals(2, Supplier::search('premium', ['name', 'description'])->count());

        // "alfa" só no nome de A.
        $this->assertEquals(1, Supplier::search('alfa', ['name', 'description'])->count());
    }

    public function test_termo_vazio_nao_filtra(): void
    {
        $this->supplier('Um', 'a');
        $this->supplier('Dois', 'b');

        $this->assertEquals(2, Supplier::search('', ['name', 'description'])->count());
        $this->assertEquals(2, Supplier::search(null, ['name', 'description'])->count());
    }

    public function test_busca_post_por_titulo_e_conteudo(): void
    {
        Post::create(['title' => 'Guia de Racao', 'slug' => 'p1-' . uniqid(), 'content' => 'conteudo', 'is_active' => true]);
        Post::create(['title' => 'Diverso', 'slug' => 'p2-' . uniqid(), 'content' => 'melhor racao do mercado', 'is_active' => true]);
        Post::create(['title' => 'Sem relacao', 'slug' => 'p3-' . uniqid(), 'content' => 'outro tema', 'is_active' => true]);

        $this->assertEquals(2, Post::search('racao', ['title', 'content'])->count());
        $this->assertEquals(1, Post::search('Guia', ['title', 'content'])->count());
    }
}
