<?php

namespace Tests\Feature;

use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierCategoryNormalizationTest extends TestCase
{
    use RefreshDatabase;

    private function supplier(string $category): Supplier
    {
        return Supplier::create([
            'name' => 'Empresa ' . uniqid(),
            'email' => 'e_' . uniqid() . '@t.com',
            'description' => 'd',
            'category' => $category,
            'is_active' => true,
            'is_approved' => true,
        ]);
    }

    public function test_cria_categoria_e_vincula_category_id(): void
    {
        $supplier = $this->supplier('racas');

        $this->assertNotNull($supplier->category_id);
        $this->assertDatabaseHas('categories', ['slug' => 'racas']);
        $this->assertEquals('racas', $supplier->categoryModel->slug);
    }

    public function test_atualizar_categoria_atualiza_o_vinculo(): void
    {
        $supplier = $this->supplier('alimentos');
        $oldId = $supplier->category_id;

        $supplier->update(['category' => 'brinquedos']);
        $supplier->refresh();

        $this->assertNotEquals($oldId, $supplier->category_id);
        $this->assertEquals('brinquedos', $supplier->categoryModel->slug);
    }

    public function test_reaproveita_categoria_existente_sem_duplicar(): void
    {
        $a = $this->supplier('higiene');
        $b = $this->supplier('higiene');

        $this->assertEquals($a->category_id, $b->category_id);
        $this->assertEquals(1, \App\Models\Category::where('slug', 'higiene')->count());
    }

    public function test_categoria_vazia_deixa_category_id_nulo(): void
    {
        $supplier = $this->supplier('');

        $this->assertNull($supplier->category_id);
    }
}
