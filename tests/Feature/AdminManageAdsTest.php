<?php

namespace Tests\Feature;

use App\Livewire\Admin\ManageAds;
use App\Livewire\Supplier\ManageAds as SupplierManageAds;
use App\Models\Advertisement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AdminManageAdsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create(['email' => 'admin_ads@t.com']);
        $user->forceFill(['role' => User::ROLE_ADMIN])->save();

        return $user;
    }

    private function supplierUser(): User
    {
        $user = User::factory()->create(['email' => 'supplier_ads@t.com']);
        $user->forceFill(['role' => User::ROLE_SUPPLIER])->save();

        return $user;
    }

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

    private function makeAd(Supplier $supplier, array $overrides = []): Advertisement
    {
        return Advertisement::create(array_merge([
            'supplier_id' => $supplier->id,
            'title' => 'Campanha Paga',
            'link' => 'https://exemplo.com',
            'position' => 'banner_topo',
            'image_path' => 'ads/pago.png',
            'is_active' => true,
            'cost_per_click' => 0.50,
            'cost_per_impression' => 0.0070,
            'skip_credits' => false,
        ], $overrides));
    }

    public function test_visitante_nao_acessa_anuncios_admin(): void
    {
        $this->get(route('admin.ads'))
            ->assertRedirect(route('login'));
    }

    public function test_fornecedor_nao_acessa_anuncios_admin(): void
    {
        $this->actingAs($this->supplierUser())
            ->get(route('admin.ads'))
            ->assertForbidden();
    }

    public function test_componente_admin_recusa_fornecedor(): void
    {
        Livewire::actingAs($this->supplierUser())
            ->test(ManageAds::class)
            ->assertForbidden();
    }

    public function test_admin_cria_anuncio_para_empresa_selecionada(): void
    {
        Storage::fake('public');
        $supplier = $this->supplier();

        Livewire::actingAs($this->admin())
            ->test(ManageAds::class)
            ->call('openCreateModal')
            ->set('supplierId', $supplier->id)
            ->set('title', 'Campanha do Admin')
            ->set('link', 'https://exemplo.com')
            ->set('position', 'banner_topo')
            ->set('image', UploadedFile::fake()->image('banner.jpg'))
            ->set('cost_per_click', 0.50)
            ->set('cost_per_impression', 0.0070)
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

        Livewire::actingAs($this->admin())
            ->test(ManageAds::class)
            ->call('openCreateModal')
            ->assertSee('Não gastar créditos')
            ->set('supplierId', $supplier->id)
            ->set('title', 'Banner Cortesia')
            ->set('link', 'https://exemplo.com')
            ->set('position', 'banner_topo')
            ->set('image', UploadedFile::fake()->image('banner.jpg'))
            ->set('cost_per_click', 0.50)
            ->set('cost_per_impression', 0.0070)
            ->set('skip_credits', true)
            ->call('createAd')
            ->assertHasNoErrors();

        $ad = Advertisement::where('title', 'Banner Cortesia')->first();
        $this->assertNotNull($ad);
        $this->assertTrue($ad->skip_credits);
    }

    public function test_admin_edita_banner_completo(): void
    {
        Storage::fake('public');
        $supplier = $this->supplier();
        $other = $this->supplier();
        $ad = $this->makeAd($supplier);

        Livewire::actingAs($this->admin())
            ->test(ManageAds::class)
            ->call('editAd', $ad->id)
            ->assertSee('Editar Anúncio Manualmente')
            ->assertSee('Não gastar créditos')
            ->set('supplierId', $other->id)
            ->set('title', 'Campanha Atualizada')
            ->set('link', 'https://novo.exemplo.com')
            ->set('position', 'sidebar_guia')
            ->set('cost_per_click', 1.25)
            ->set('cost_per_impression', 0.0100)
            ->set('is_active', false)
            ->set('skip_credits', true)
            ->call('updateAd')
            ->assertHasNoErrors();

        $ad->refresh();
        $this->assertEquals($other->id, $ad->supplier_id);
        $this->assertEquals('Campanha Atualizada', $ad->title);
        $this->assertEquals('https://novo.exemplo.com', $ad->link);
        $this->assertEquals('sidebar_guia', $ad->position);
        $this->assertEqualsWithDelta(1.25, (float) $ad->cost_per_click, 0.0001);
        $this->assertEqualsWithDelta(0.0100, (float) $ad->cost_per_impression, 0.0001);
        $this->assertFalse($ad->is_active);
        $this->assertTrue($ad->skip_credits);
        $this->assertEquals('ads/pago.png', $ad->image_path);
    }

    public function test_admin_troca_imagem_ao_editar(): void
    {
        Storage::fake('public');
        $supplier = $this->supplier();
        Storage::disk('public')->put('ads/pago.png', 'old');
        $ad = $this->makeAd($supplier);

        Livewire::actingAs($this->admin())
            ->test(ManageAds::class)
            ->call('editAd', $ad->id)
            ->set('image', UploadedFile::fake()->image('novo.jpg'))
            ->call('updateAd')
            ->assertHasNoErrors();

        $ad->refresh();
        $this->assertNotEquals('ads/pago.png', $ad->image_path);
        Storage::disk('public')->assertMissing('ads/pago.png');
        Storage::disk('public')->assertExists($ad->image_path);
    }

    public function test_admin_exclui_banner_e_arquivo(): void
    {
        Storage::fake('public');
        $supplier = $this->supplier();
        Storage::disk('public')->put('ads/pago.png', 'old');
        $ad = $this->makeAd($supplier);

        Livewire::actingAs($this->admin())
            ->test(ManageAds::class)
            ->assertSee('Excluir')
            ->call('deleteAd', $ad->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('advertisements', ['id' => $ad->id]);
        Storage::disk('public')->assertMissing('ads/pago.png');
    }

    public function test_criacao_valida_campos_obrigatorios(): void
    {
        Livewire::actingAs($this->admin())
            ->test(ManageAds::class)
            ->call('openCreateModal')
            ->call('createAd')
            ->assertHasErrors(['supplierId', 'title', 'link', 'position', 'image']);

        $this->assertEquals(0, Advertisement::count());
    }

    public function test_aba_exibe_tabela_com_todas_as_medidas_e_posicoes(): void
    {
        $component = Livewire::actingAs($this->admin())
            ->test(ManageAds::class)
            ->assertSee('Medidas e Posições dos Anúncios');

        foreach (Advertisement::getPositionSpecs() as $spec) {
            $component
                ->assertSee($spec['label'])
                ->assertSee($spec['location'])
                ->assertSee($spec['format'])
                ->assertSee((string) $spec['width'])
                ->assertSee((string) $spec['height']);
        }
    }

    public function test_painel_do_fornecedor_nao_tem_opcoes_manuais_de_admin(): void
    {
        $user = $this->supplierUser();
        Supplier::create([
            'name' => 'Minha Loja',
            'email' => 'loja_' . uniqid() . '@t.com',
            'description' => 'd',
            'category' => 'racas',
            'user_id' => $user->id,
            'is_active' => true,
            'is_approved' => true,
        ]);

        Livewire::actingAs($user)
            ->test(SupplierManageAds::class)
            ->call('openCreateModal')
            ->assertDontSee('Não gastar créditos')
            ->assertDontSee('Criar Anúncio Manualmente')
            ->assertDontSee('Editar Anúncio Manualmente');
    }
}
