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
        $this->assertNotEmpty($ad->image_path);
    }

    public function test_criacao_valida_campos_obrigatorios(): void
    {
        Livewire::test(ManageAds::class)
            ->call('openCreateModal')
            ->call('createAd')
            ->assertHasErrors(['newSupplierId', 'newTitle', 'newLink', 'newPosition', 'newImage']);

        $this->assertEquals(0, Advertisement::count());
    }
}
