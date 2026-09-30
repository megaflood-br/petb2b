<?php

namespace Tests\Feature;

use App\Livewire\Admin\ManageAds;
use App\Models\Advertisement;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AdminManageAdsTest extends TestCase
{
    use RefreshDatabase;

    private function supplier(): Supplier
    {
        return Supplier::create([
            'name' => 'Empresa ' . uniqid(),
            'email' => 'e_' . uniqid() . '@t.com',
            'description' => 'd',
            'category' => 'racas',
            'is_active' => true,
            'is_approved' => true,
        ]);
    }

    public function test_admin_cria_anuncio_para_empresa_selecionada(): void
    {
        Storage::fake('public');
        $supplier = $this->supplier();

        Livewire::test(ManageAds::class)
            ->call('openCreateModal')
            ->set('newSupplierId', $supplier->id)
            ->set('newTitle', 'Campanha do Admin')
            ->set('newLink', 'https://exemplo.com')
            ->set('newPosition', 'banner_topo')
            ->set('newImage', UploadedFile::fake()->image('banner.jpg'))
            ->set('newCostPerClick', 0.50)
            ->set('newCostPerImpression', 0.0070)
            ->call('createAd')
            ->assertHasNoErrors();

        $ad = Advertisement::where('supplier_id', $supplier->id)->first();
        $this->assertNotNull($ad);
        $this->assertEquals('Campanha do Admin', $ad->title);
        $this->assertEquals('banner_topo', $ad->position);
        $this->assertTrue($ad->is_active);
        $this->assertFalse($ad->skip_credits);
        $this->assertNotEmpty($ad->image_path);
    }

    public function test_admin_cria_banner_sem_gastar_creditos(): void
    {
        Storage::fake('public');
        $supplier = $this->supplier();

        Livewire::test(ManageAds::class)
            ->call('openCreateModal')
            ->assertSee('Não gastar créditos')
            ->set('newSupplierId', $supplier->id)
            ->set('newTitle', 'Banner Cortesia')
            ->set('newLink', 'https://exemplo.com')
            ->set('newPosition', 'banner_topo')
            ->set('newImage', UploadedFile::fake()->image('banner.jpg'))
            ->set('newCostPerClick', 0.50)
            ->set('newCostPerImpression', 0.0070)
            ->set('newSkipCredits', true)
            ->call('createAd')
            ->assertHasNoErrors();

        $ad = Advertisement::where('title', 'Banner Cortesia')->first();
        $this->assertNotNull($ad);
        $this->assertTrue($ad->skip_credits);
    }

    public function test_admin_marca_cortesia_na_edicao(): void
    {
        $supplier = $this->supplier();
        $ad = Advertisement::create([
            'supplier_id' => $supplier->id,
            'title' => 'Campanha Paga',
            'link' => 'https://exemplo.com',
            'position' => 'banner_topo',
            'image_path' => 'ads/pago.png',
            'is_active' => true,
            'cost_per_click' => 0.50,
            'cost_per_impression' => 0.0070,
            'skip_credits' => false,
        ]);

        Livewire::test(ManageAds::class)
            ->call('editAd', $ad->id)
            ->assertSee('Não gastar créditos')
            ->set('skip_credits', true)
            ->call('saveAdSettings')
            ->assertHasNoErrors();

        $this->assertTrue($ad->fresh()->skip_credits);
    }

    public function test_criacao_valida_campos_obrigatorios(): void
    {
        Livewire::test(ManageAds::class)
            ->call('openCreateModal')
            ->call('createAd')
            ->assertHasErrors(['newSupplierId', 'newTitle', 'newLink', 'newPosition', 'newImage']);

        $this->assertEquals(0, Advertisement::count());
    }

    public function test_aba_exibe_tabela_com_todas_as_medidas_e_posicoes(): void
    {
        $component = Livewire::test(ManageAds::class)
            ->assertSee('Medidas e Posições dos Anúncios');

        foreach (Advertisement::getPositionSpecs() as $key => $spec) {
            $component
                ->assertSee($spec['label'])
                ->assertSee($spec['location'])
                ->assertSee($spec['format'])
                ->assertSee((string) $spec['width'])
                ->assertSee((string) $spec['height']);
        }
    }
}
