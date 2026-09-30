<?php

namespace Tests\Feature;

use App\Livewire\Admin\Reports;
use App\Models\Advertisement;
use App\Models\Supplier;
use App\Models\SupplierCreditTransaction;
use App\Models\User;
use App\Support\Reports\AdsReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminReportsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create(['email' => 'admin_reports@t.com']);
        $user->forceFill(['role' => User::ROLE_ADMIN])->save();

        return $user;
    }

    private function supplierUser(): User
    {
        $user = User::factory()->create(['email' => 'supplier_reports@t.com']);
        $user->forceFill(['role' => User::ROLE_SUPPLIER])->save();

        return $user;
    }

    private function supplier(string $name = 'Empresa Relatorio'): Supplier
    {
        return Supplier::create([
            'name' => $name,
            'email' => 'rel_' . uniqid() . '@t.com',
            'description' => 'd',
            'category' => 'racas',
            'is_active' => true,
            'is_approved' => true,
        ]);
    }

    private function makeAd(Supplier $supplier, array $overrides = []): Advertisement
    {
        return Advertisement::create(array_merge([
            'supplier_id' => $supplier->id,
            'title' => 'Banner Relatorio',
            'link' => 'https://exemplo.com',
            'position' => 'banner_topo',
            'image_path' => 'ads/relatorio.png',
            'is_active' => true,
            'views' => 100,
            'clicks' => 10,
            'cost_per_click' => 0.50,
            'cost_per_impression' => 0.0070,
            'skip_credits' => false,
        ], $overrides));
    }

    public function test_visitante_nao_acessa_relatorios(): void
    {
        $this->get(route('admin.reports'))->assertRedirect(route('login'));
        $this->get(route('admin.reports.show', 'anuncios'))->assertRedirect(route('login'));
    }

    public function test_fornecedor_nao_acessa_relatorios(): void
    {
        $this->actingAs($this->supplierUser())
            ->get(route('admin.reports'))
            ->assertForbidden();
    }

    public function test_tipo_desconhecido_ou_em_breve_retorna_404(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.reports.show', 'nao-existe'))
            ->assertNotFound();

        $this->actingAs($admin)
            ->get(route('admin.reports.show', 'leads'))
            ->assertNotFound();
    }

    public function test_hub_lista_anuncios_e_tipos_futuros(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.reports'))
            ->assertOk()
            ->assertSee('Relatórios')
            ->assertSee('Anúncios')
            ->assertSee('Abrir relatório')
            ->assertSee('Leads')
            ->assertSee('Fornecedores')
            ->assertSee('Em breve');
    }

    public function test_sidebar_tem_aba_de_relatorios(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Relatórios')
            ->assertSee(route('admin.reports', absolute: false), false);
    }

    public function test_relatorio_de_anuncios_mostra_totais_e_campanha(): void
    {
        $supplier = $this->supplier();
        $ad = $this->makeAd($supplier, ['title' => 'Campanha Super Banner']);
        SupplierCreditTransaction::create([
            'supplier_id' => $supplier->id,
            'type' => 'expense_click',
            'amount' => 5.00,
            'description' => 'Cliques',
            'advertisement_id' => $ad->id,
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.reports.show', 'anuncios'))
            ->assertOk()
            ->assertSee('Relatório de Anúncios')
            ->assertSee('Campanha Super Banner')
            ->assertSee($supplier->name)
            ->assertSee('Exportar CSV')
            ->assertSee('Gasto debitado');
    }

    public function test_filtra_por_empresa_e_status(): void
    {
        $alpha = $this->supplier('Alpha Pet');
        $beta = $this->supplier('Beta Pet');
        $this->makeAd($alpha, ['title' => 'Banner Alpha']);
        $this->makeAd($beta, ['title' => 'Banner Beta', 'is_active' => false]);

        Livewire::actingAs($this->admin())
            ->test(Reports::class, ['type' => 'anuncios'])
            ->assertSee('Banner Alpha')
            ->assertSee('Banner Beta')
            ->set('supplierId', (string) $alpha->id)
            ->assertSee('Banner Alpha')
            ->assertDontSee('Banner Beta')
            ->set('supplierId', '')
            ->set('status', 'paused')
            ->assertSee('Banner Beta')
            ->assertDontSee('Banner Alpha');
    }

    public function test_agrupa_por_empresa(): void
    {
        $supplier = $this->supplier('Agrupada Ltda');
        $this->makeAd($supplier, ['title' => 'Um', 'views' => 40, 'clicks' => 4]);
        $this->makeAd($supplier, ['title' => 'Dois', 'views' => 60, 'clicks' => 6, 'position' => 'sidebar_guia']);

        Livewire::actingAs($this->admin())
            ->test(Reports::class, ['type' => 'anuncios'])
            ->set('groupBy', 'supplier')
            ->assertSee('Agrupada Ltda')
            ->assertSee('Por empresa');
    }

    public function test_exporta_csv_com_campanha(): void
    {
        $supplier = $this->supplier('CSV Pet');
        $this->makeAd($supplier, ['title' => 'Banner CSV']);

        Livewire::actingAs($this->admin())
            ->test(Reports::class, ['type' => 'anuncios'])
            ->call('exportCsv')
            ->assertFileDownloaded('relatorio-anuncios-'.now()->format('Y-m-d').'.csv');
    }

    public function test_ads_report_calcula_resumo_e_ctr(): void
    {
        $supplier = $this->supplier();
        $paid = $this->makeAd($supplier, ['views' => 200, 'clicks' => 20]);
        $this->makeAd($supplier, [
            'title' => 'Cortesia',
            'views' => 50,
            'clicks' => 5,
            'skip_credits' => true,
        ]);
        SupplierCreditTransaction::create([
            'supplier_id' => $supplier->id,
            'type' => 'expense_impression',
            'amount' => 1.40,
            'description' => 'Views',
            'advertisement_id' => $paid->id,
        ]);

        $summary = AdsReport::fromFilters()->summary();

        $this->assertEquals(2, $summary['campaigns']);
        $this->assertEquals(2, $summary['active']);
        $this->assertEquals(1, $summary['courtesy']);
        $this->assertEquals(250, $summary['views']);
        $this->assertEquals(25, $summary['clicks']);
        $this->assertEquals(10.0, $summary['ctr']);
        $this->assertEqualsWithDelta(1.40, $summary['billed'], 0.0001);
    }
}
