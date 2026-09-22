<?php

namespace Tests\Feature;

use App\Http\Controllers\HomeController;
use App\Livewire\Admin\ApproveSuppliers;
use App\Models\Advertisement;
use App\Models\Category;
use App\Models\Classified;
use App\Models\CompanyClaim;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\Kennel;
use App\Models\Lead;
use App\Models\PixCharge;
use App\Models\Post;
use App\Models\Supplier;
use App\Models\SupplierCreditTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

class AdminWipeSuppliersTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create(['email' => 'admin_wipe@t.com']);
        $user->forceFill(['role' => 'admin'])->save();

        return $user;
    }

    private function supplier(array $overrides = []): Supplier
    {
        return Supplier::create(array_merge([
            'name' => 'Empresa '.uniqid(),
            'email' => 'e_'.uniqid().'@t.com',
            'description' => 'd',
            'category' => 'racas',
            'city' => 'Atibaia',
            'state' => 'SP',
            'is_active' => true,
            'is_approved' => true,
        ], $overrides));
    }

    public function test_visitante_nao_acessa_a_tela(): void
    {
        $this->get(route('admin.suppliers'))->assertRedirect();
    }

    public function test_tela_mostra_a_opcao_de_zerar(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.suppliers'))
            ->assertOk()
            ->assertSee('Zerar banco de fornecedores')
            ->assertSee('Zerar banco');
    }

    public function test_nao_apaga_sem_digitar_excluir(): void
    {
        $this->supplier(['name' => 'Pet Indústria']);

        Livewire::actingAs($this->admin())
            ->test(ApproveSuppliers::class)
            ->call('openWipeModal')
            ->set('wipeConfirmation', 'apagar')
            ->call('wipeSuppliers')
            ->assertHasErrors(['wipeConfirmation'])
            ->assertSet('confirmingWipe', true);

        $this->assertEquals(1, Supplier::count());
        $this->assertDatabaseHas('suppliers', ['name' => 'Pet Indústria']);
    }

    public function test_nao_apaga_com_confirmacao_vazia(): void
    {
        $this->supplier();

        Livewire::actingAs($this->admin())
            ->test(ApproveSuppliers::class)
            ->call('wipeSuppliers')
            ->assertHasErrors(['wipeConfirmation']);

        $this->assertEquals(1, Supplier::count());
    }

    public function test_zerar_apaga_empresas_e_conteudo_comercial_e_preserva_o_resto(): void
    {
        $admin = $this->admin();
        $owner = User::factory()->create(['name' => 'Dono Fornecedor', 'email' => 'dono@t.com']);
        $owner->forceFill(['role' => 'supplier'])->save();
        $breeder = User::factory()->create(['name' => 'Criador', 'email' => 'canil@t.com']);
        $breeder->forceFill(['role' => 'breeder'])->save();

        $company = $this->supplier([
            'name' => 'Pet Indústria CTA',
            'user_id' => $owner->id,
        ]);
        $this->supplier(['name' => 'Outra Empresa', 'is_approved' => false]);

        Classified::create([
            'supplier_id' => $company->id,
            'title' => 'Ração atacado',
            'slug' => 'racao-atacado',
            'description' => 'Lote',
            'price' => 99.90,
            'condition' => 'Novo',
            'is_active' => true,
        ]);

        $job = JobPosting::create([
            'supplier_id' => $company->id,
            'title' => 'Vendedor Pet',
            'description' => 'Descrição completa da vaga com requisitos.',
            'type' => 'CLT',
            'city' => 'Atibaia',
            'state' => 'SP',
            'how_to_apply' => 'rh@empresa.com',
            'is_active' => true,
        ]);

        JobApplication::create([
            'job_posting_id' => $job->id,
            'name' => 'Candidato',
            'email' => 'cand@t.com',
        ]);

        Lead::create([
            'supplier_id' => $company->id,
            'name' => 'Lojista',
            'email' => 'loja@t.com',
            'message' => 'Quero cotação',
        ]);

        CompanyClaim::create([
            'supplier_id' => $company->id,
            'user_id' => $owner->id,
            'claimant_name' => $owner->name,
            'claimant_email' => $owner->email,
            'status' => 'pending',
        ]);

        $paidAd = Advertisement::create([
            'supplier_id' => $company->id,
            'title' => 'Campanha da empresa',
            'image_path' => 'ads/empresa.jpg',
            'link' => 'https://empresa.test',
            'position' => 'banner_topo',
            'is_active' => true,
        ]);

        $houseAd = Advertisement::create([
            'supplier_id' => null,
            'title' => 'Anúncio institucional',
            'image_path' => 'ads/house.jpg',
            'link' => 'https://portal.test/anuncie',
            'position' => 'home_top',
            'is_active' => true,
        ]);

        SupplierCreditTransaction::create([
            'supplier_id' => $company->id,
            'type' => 'deposit',
            'amount' => 50,
            'description' => 'Recarga',
            'advertisement_id' => $paidAd->id,
        ]);

        PixCharge::create([
            'supplier_id' => $company->id,
            'amount' => 50,
            'status' => 'PENDING',
        ]);

        $editorial = Post::create([
            'title' => 'Matéria da redação',
            'slug' => 'materia-redacao',
            'content' => 'Texto',
            'is_active' => true,
        ]);

        $sponsored = Post::create([
            'title' => 'Matéria patrocinada',
            'slug' => 'materia-patrocinada',
            'content' => 'Texto',
            'supplier_id' => $company->id,
            'is_sponsored' => true,
            'is_active' => true,
        ]);

        Kennel::create([
            'user_id' => $breeder->id,
            'name' => 'Canil Estrela',
            'slug' => 'canil-estrela',
            'city' => 'Atibaia',
            'state' => 'SP',
            'is_active' => true,
        ]);

        Cache::put(HomeController::CACHE_KEY, ['stale' => true], 300);

        Livewire::actingAs($admin)
            ->test(ApproveSuppliers::class)
            ->assertSee('Zerar banco')
            ->call('openWipeModal')
            ->set('wipeConfirmation', '  Excluir  ')
            ->call('wipeSuppliers')
            ->assertHasNoErrors()
            ->assertSet('confirmingWipe', false)
            ->assertSee('2 fornecedor(es) excluído(s)');

        $this->assertEquals(0, Supplier::count());
        $this->assertEquals(0, Classified::count());
        $this->assertEquals(0, JobPosting::count());
        $this->assertEquals(0, JobApplication::count());
        $this->assertEquals(0, Lead::count());
        $this->assertEquals(0, CompanyClaim::count());
        $this->assertEquals(0, SupplierCreditTransaction::count());
        $this->assertEquals(0, PixCharge::count());
        $this->assertDatabaseMissing('advertisements', ['id' => $paidAd->id]);
        $this->assertDatabaseHas('advertisements', [
            'id' => $houseAd->id,
            'title' => 'Anúncio institucional',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $owner->id,
            'email' => 'dono@t.com',
            'role' => 'supplier',
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $breeder->id,
            'role' => 'breeder',
        ]);
        $this->assertDatabaseHas('kennels', ['name' => 'Canil Estrela']);
        $this->assertDatabaseHas('categories', ['slug' => 'racas']);
        $this->assertDatabaseHas('posts', [
            'id' => $editorial->id,
            'title' => 'Matéria da redação',
        ]);
        $this->assertDatabaseHas('posts', [
            'id' => $sponsored->id,
            'title' => 'Matéria patrocinada',
            'supplier_id' => null,
        ]);
        $this->assertFalse(Cache::has(HomeController::CACHE_KEY));
    }
}
