<?php

namespace Tests\Feature;

use App\Livewire\Supplier\ManageSponsoredPosts;
use App\Models\Post;
use App\Models\Supplier;
use App\Models\SupplierCreditTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SponsoredPostTest extends TestCase
{
    use RefreshDatabase;

    private function supplierWithBalance(float $balance): array
    {
        $user = User::create(['name' => 'F', 'email' => 'f_' . uniqid() . '@t.com', 'password' => 'secret']);
        $supplier = Supplier::create([
            'name' => 'Empresa ' . uniqid(),
            'email' => 'e_' . uniqid() . '@t.com',
            'description' => 'd',
            'category' => 'racas',
            'user_id' => $user->id,
            'is_active' => true,
            'is_approved' => true,
        ]);
        $supplier->credit_balance = $balance;
        $supplier->save();

        return [$user, $supplier];
    }

    public function test_publica_materia_debitando_credito(): void
    {
        [$user, $supplier] = $this->supplierWithBalance(200.0);
        $this->actingAs($user);

        Livewire::test(ManageSponsoredPosts::class)
            ->set('title', 'Lançamento da nova linha premium')
            ->set('content', 'Conteúdo do publieditorial com detalhes do produto.')
            ->call('publish');

        $post = Post::where('supplier_id', $supplier->id)->first();
        $this->assertNotNull($post);
        $this->assertTrue($post->is_sponsored);
        $this->assertTrue($post->is_active);

        // Debitou o custo padrão (150).
        $this->assertEqualsWithDelta(50.0, (float) $supplier->fresh()->credit_balance, 0.0001);
        $this->assertDatabaseHas('supplier_credit_transactions', [
            'supplier_id' => $supplier->id,
            'type' => 'expense_sponsored',
        ]);
    }

    public function test_saldo_insuficiente_nao_publica_nem_debita(): void
    {
        [$user, $supplier] = $this->supplierWithBalance(50.0); // < 150

        $this->actingAs($user);

        Livewire::test(ManageSponsoredPosts::class)
            ->set('title', 'Matéria sem saldo suficiente')
            ->set('content', 'Conteúdo que não deve ser publicado por falta de saldo.')
            ->call('publish');

        $this->assertEquals(0, Post::where('supplier_id', $supplier->id)->count());
        $this->assertEqualsWithDelta(50.0, (float) $supplier->fresh()->credit_balance, 0.0001);
        $this->assertDatabaseMissing('supplier_credit_transactions', [
            'supplier_id' => $supplier->id,
            'type' => 'expense_sponsored',
        ]);
    }

    public function test_materia_patrocinada_aparece_publica_com_selo(): void
    {
        [, $supplier] = $this->supplierWithBalance(0.0);

        $post = Post::create([
            'supplier_id' => $supplier->id,
            'title' => 'Publieditorial Visivel',
            'slug' => 'publieditorial-' . uniqid(),
            'content' => str_repeat('Conteúdo do publieditorial. ', 20),
            'is_active' => true,
            'is_sponsored' => true,
        ]);

        $this->get('/materias-geral/' . $post->slug)
            ->assertOk()
            ->assertSee('Patrocinado')
            ->assertSee($supplier->name);
    }
}
