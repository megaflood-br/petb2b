<?php

namespace Tests\Feature;

use App\Models\Advertisement;
use App\Models\Supplier;
use App\Models\SupplierCreditTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Testes do motor de créditos/anúncios.
 *
 * Observação: `lockForUpdate` é um no-op no SQLite (usado nos testes), então
 * a exclusão mútua real só é exercitada em MySQL/Postgres. Estes testes
 * cobrem a lógica de negócio: débito correto, pausa por falta de saldo,
 * ausência de saldo negativo e registro de transações.
 */
class CreditEngineTest extends TestCase
{
    use RefreshDatabase;

    private function makeSupplier(float $balance): Supplier
    {
        $supplier = Supplier::create([
            'name' => 'Fornecedor Teste',
            'email' => 'forn_' . uniqid() . '@teste.com',
            'description' => 'Descrição de teste',
            'category' => 'racas',
            'is_active' => true,
            'is_approved' => true,
        ]);

        // credit_balance é intencionalmente fora do $fillable (não pode ser
        // mass-assignable via formulário); setamos direto para o cenário.
        $supplier->credit_balance = $balance;
        $supplier->save();

        return $supplier;
    }

    private function makeAd(Supplier $supplier, array $overrides = []): Advertisement
    {
        return Advertisement::create(array_merge([
            'supplier_id' => $supplier->id,
            'title' => 'Campanha Teste',
            'link' => 'https://exemplo.com',
            'position' => 'banner_topo',
            'image_path' => 'ads/teste.png',
            'is_active' => true,
            'clicks' => 0,
            'views' => 0,
            'cost_per_click' => 0.50,
            'cost_per_impression' => 0.0070,
        ], $overrides));
    }

    public function test_click_debita_saldo_e_registra_transacao(): void
    {
        $supplier = $this->makeSupplier(10.00);
        $ad = $this->makeAd($supplier);

        $this->assertTrue($ad->chargeClick());

        $this->assertEquals(1, $ad->fresh()->clicks);
        $this->assertEqualsWithDelta(9.50, (float) $supplier->fresh()->credit_balance, 0.0001);
        $this->assertDatabaseHas('supplier_credit_transactions', [
            'supplier_id' => $supplier->id,
            'type' => 'expense_click',
            'advertisement_id' => $ad->id,
        ]);
    }

    public function test_impressao_debita_custo_por_visualizacao(): void
    {
        $supplier = $this->makeSupplier(1.00);
        $ad = $this->makeAd($supplier);

        $this->assertTrue($ad->chargeImpression());

        $this->assertEquals(1, $ad->fresh()->views);

        // Com decimal(_,4) o custo sub-centavo (0.0070) é preservado sem
        // arredondamento: 1.0000 - 0.0070 = 0.9930.
        $this->assertEqualsWithDelta(0.9930, (float) $supplier->fresh()->credit_balance, 0.00001);

        $this->assertDatabaseHas('supplier_credit_transactions', [
            'supplier_id' => $supplier->id,
            'type' => 'expense_impression',
        ]);
    }

    public function test_sem_saldo_pausa_anuncio_e_nao_cobra(): void
    {
        $supplier = $this->makeSupplier(0.10);
        $ad = $this->makeAd($supplier, ['cost_per_click' => 0.50]);

        $this->assertFalse($ad->chargeClick());

        // Não debitou, não contou clique e pausou a campanha.
        $this->assertEquals(0, $ad->fresh()->clicks);
        $this->assertFalse($ad->fresh()->is_active);
        $this->assertEqualsWithDelta(0.10, (float) $supplier->fresh()->credit_balance, 0.0001);
        $this->assertDatabaseMissing('supplier_credit_transactions', [
            'supplier_id' => $supplier->id,
            'type' => 'expense_click',
        ]);
    }

    public function test_saldo_nunca_fica_negativo_em_multiplas_cobrancas(): void
    {
        $supplier = $this->makeSupplier(1.00);
        $ad = $this->makeAd($supplier, ['cost_per_click' => 0.50]);

        // 2 cobranças válidas (1.00 -> 0.50 -> 0.00), a 3ª deve falhar.
        $this->assertTrue($ad->chargeClick());
        $this->assertTrue($ad->chargeClick());
        $this->assertFalse($ad->chargeClick());

        $this->assertGreaterThanOrEqual(0, (float) $supplier->fresh()->credit_balance);
        $this->assertEquals(2, $ad->fresh()->clicks);
    }
}
