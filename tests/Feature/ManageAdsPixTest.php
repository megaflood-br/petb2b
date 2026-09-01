<?php

namespace Tests\Feature;

use App\Livewire\Supplier\ManageAds;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ManageAdsPixTest extends TestCase
{
    use RefreshDatabase;

    public function test_gerar_pix_cria_cobranca_e_expoe_qr(): void
    {
        $user = User::create([
            'name' => 'Fornecedor',
            'email' => 'sup_' . uniqid() . '@t.com',
            'password' => 'secret',
        ]);

        $supplier = Supplier::create([
            'name' => 'Loja',
            'email' => 'loja_' . uniqid() . '@t.com',
            'description' => 'd',
            'category' => 'racas',
            'user_id' => $user->id,
            'is_active' => true,
            'is_approved' => true,
        ]);

        $this->actingAs($user);

        $component = Livewire::test(ManageAds::class)
            ->set('amount', 50)
            ->call('generatePix');

        $this->assertNotNull($component->get('pixChargeId'));
        $this->assertNotEmpty($component->get('pixPayload'));

        $this->assertDatabaseHas('pix_charges', [
            'supplier_id' => $supplier->id,
            'status' => 'PENDING',
        ]);

        // Nenhum crédito é lançado na geração (só após o webhook).
        $this->assertEqualsWithDelta(0.0, (float) $supplier->fresh()->credit_balance, 0.0001);
    }

    public function test_gerar_pix_valida_valor_minimo(): void
    {
        $user = User::create([
            'name' => 'F', 'email' => 'u_' . uniqid() . '@t.com', 'password' => 'secret',
        ]);
        Supplier::create([
            'name' => 'Loja', 'email' => 'l_' . uniqid() . '@t.com', 'description' => 'd',
            'category' => 'racas', 'user_id' => $user->id, 'is_active' => true, 'is_approved' => true,
        ]);
        $this->actingAs($user);

        Livewire::test(ManageAds::class)
            ->set('amount', 1)
            ->call('generatePix')
            ->assertHasErrors(['amount']);
    }
}
