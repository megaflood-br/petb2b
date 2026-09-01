<?php

namespace Tests\Feature;

use App\Jobs\ChargeAdEvent;
use App\Models\Advertisement;
use App\Models\Supplier;
use App\Services\AdTracker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class AdTrackerTest extends TestCase
{
    use RefreshDatabase;

    private function makeAd(float $balance = 10.0): Advertisement
    {
        $supplier = Supplier::create([
            'name' => 'Fornecedor Teste',
            'email' => 'forn_' . uniqid() . '@teste.com',
            'description' => 'Descrição',
            'category' => 'racas',
            'is_active' => true,
            'is_approved' => true,
        ]);
        $supplier->credit_balance = $balance;
        $supplier->save();

        return Advertisement::create([
            'supplier_id' => $supplier->id,
            'title' => 'Campanha',
            'link' => 'https://exemplo.com',
            'position' => 'banner_topo',
            'image_path' => 'ads/x.png',
            'is_active' => true,
            'clicks' => 0,
            'views' => 0,
            'cost_per_click' => 0.50,
            'cost_per_impression' => 0.0070,
        ]);
    }

    public function test_evento_repetido_e_deduplicado_e_cobra_apenas_uma_vez(): void
    {
        config(['ads.tracking_queue' => true]);
        Bus::fake();

        $ad = $this->makeAd();
        $tracker = app(AdTracker::class);

        // Mesmo IP/user-agent (mesma request de teste) dentro da janela.
        $this->assertTrue($tracker->record($ad, 'click'));
        $this->assertFalse($tracker->record($ad, 'click'));

        Bus::assertDispatchedTimes(ChargeAdEvent::class, 1);
    }

    public function test_tipos_diferentes_nao_sao_deduplicados_entre_si(): void
    {
        config(['ads.tracking_queue' => true]);
        Bus::fake();

        $ad = $this->makeAd();
        $tracker = app(AdTracker::class);

        $this->assertTrue($tracker->record($ad, 'click'));
        $this->assertTrue($tracker->record($ad, 'impression'));

        Bus::assertDispatchedTimes(ChargeAdEvent::class, 2);
    }

    public function test_job_debita_o_saldo_do_fornecedor(): void
    {
        $ad = $this->makeAd(10.0);

        (new ChargeAdEvent($ad->id, 'click'))->handle();

        $this->assertEquals(1, $ad->fresh()->clicks);
        $this->assertEqualsWithDelta(9.50, (float) $ad->supplier->fresh()->credit_balance, 0.0001);
    }
}
