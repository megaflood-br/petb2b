<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlaceholderInitialsTest extends TestCase
{
    use RefreshDatabase;

    private function supplierWithoutLogo(): Supplier
    {
        return Supplier::create([
            'name' => 'Empresa Sem Logo',
            'slug' => 'empresa-sem-logo',
            'email' => 'semlogo@t.com',
            'description' => 'Empresa de teste sem imagem.',
            'category' => 'racas',
            'is_active' => true,
            'is_approved' => true,
            'is_verified' => true,
        ]);
    }

    public function test_home_usa_rnpet_quando_fornecedor_nao_tem_logo(): void
    {
        $this->supplierWithoutLogo();

        $this->get('/')
            ->assertOk()
            ->assertSee('>RNPET</span>', false)
            ->assertDontSee('>PBP</span>', false)
            ->assertDontSee('>PBP</div>', false);
    }

    public function test_lista_e_ficha_do_fornecedor_usam_rnpet_sem_logo(): void
    {
        $supplier = $this->supplierWithoutLogo();

        $this->get(route('suppliers.index'))
            ->assertOk()
            ->assertSee('>RNPET</span>', false)
            ->assertDontSee('>PBP</span>', false);

        $this->get(route('suppliers.show', $supplier->slug))
            ->assertOk()
            ->assertSee('>RNPET</div>', false)
            ->assertDontSee('>PBP</div>', false);
    }

    public function test_materia_em_destaque_sem_capa_usa_rnpet(): void
    {
        Post::create([
            'title' => 'Matéria Sem Capa',
            'slug' => 'materia-sem-capa',
            'content' => 'Conteúdo de teste da matéria.',
            'is_active' => true,
            'is_featured' => true,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('>RNPET</div>', false)
            ->assertDontSee('>NP</div>', false)
            ->assertDontSee('>PBP</div>', false);
    }
}
