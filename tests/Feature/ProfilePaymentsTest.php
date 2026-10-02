<?php

namespace Tests\Feature;

use App\Livewire\Profile\PaymentHistory;
use App\Models\PixCharge;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProfilePaymentsTest extends TestCase
{
    use RefreshDatabase;

    private function supplierUser(?string $cnpj = '12.345.678/0001-95'): array
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => User::ROLE_SUPPLIER])->save();

        $supplier = Supplier::create([
            'name' => 'Loja PIX',
            'email' => 'pix_' . uniqid() . '@t.com',
            'description' => 'd',
            'category' => 'racas',
            'user_id' => $user->id,
            'cnpj' => $cnpj,
            'is_active' => true,
            'is_approved' => true,
        ]);

        return [$user, $supplier];
    }

    public function test_perfil_mostra_secao_de_pagamentos(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk()
            ->assertSee('Pagamentos')
            ->assertSee('PIX gerados')
            ->assertSee('Pagamentos efetuados')
            ->assertSee('Nenhum PIX em aberto')
            ->assertSee('Nenhum pagamento efetuado ainda');
    }

    public function test_mostra_pix_gerado_e_pagamento_efetuado_do_usuario(): void
    {
        [$user, $supplier] = $this->supplierUser();

        PixCharge::create([
            'supplier_id' => $supplier->id,
            'asaas_payment_id' => 'pay_open',
            'amount' => 50,
            'status' => 'PENDING',
            'pix_payload' => '00020126PIX-ABERTO',
            'pix_expiration' => now()->addDay(),
        ]);

        PixCharge::create([
            'supplier_id' => $supplier->id,
            'asaas_payment_id' => 'pay_paid',
            'amount' => 80,
            'status' => 'RECEIVED',
            'credited_at' => now(),
        ]);

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk()
            ->assertSee('R$ 50,00')
            ->assertSee('Aguardando pagamento')
            ->assertSee('00020126PIX-ABERTO')
            ->assertSee('R$ 80,00')
            ->assertSee('Pago')
            ->assertSee('Gerar novo PIX');
    }

    public function test_nao_mostra_pix_de_outra_empresa(): void
    {
        [$user] = $this->supplierUser();

        $other = Supplier::create([
            'name' => 'Outra Loja',
            'email' => 'outra_' . uniqid() . '@t.com',
            'description' => 'd',
            'category' => 'racas',
            'is_active' => true,
            'is_approved' => true,
        ]);

        PixCharge::create([
            'supplier_id' => $other->id,
            'asaas_payment_id' => 'pay_secreto',
            'amount' => 999,
            'status' => 'PENDING',
            'pix_payload' => 'PIX-SECRETO',
        ]);

        Livewire::actingAs($user)
            ->test(PaymentHistory::class)
            ->assertDontSee('PIX-SECRETO')
            ->assertDontSee('R$ 999,00');
    }

    public function test_pix_pago_sai_da_lista_aberta(): void
    {
        [$user, $supplier] = $this->supplierUser();

        $charge = PixCharge::create([
            'supplier_id' => $supplier->id,
            'asaas_payment_id' => 'pay_move',
            'amount' => 40,
            'status' => 'PENDING',
            'pix_payload' => 'PIX-MOVER',
        ]);

        Livewire::actingAs($user)
            ->test(PaymentHistory::class)
            ->assertSee('PIX-MOVER')
            ->assertSee('Aguardando pagamento');

        $charge->forceFill([
            'status' => 'RECEIVED',
            'credited_at' => now(),
        ])->save();

        Livewire::actingAs($user)
            ->test(PaymentHistory::class)
            ->assertDontSee('PIX-MOVER')
            ->assertSee('Pago')
            ->assertSee('R$ 40,00');
    }
}
