<?php

namespace Tests\Feature;

use App\Livewire\Admin\ApproveSuppliers;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminCreateSupplierTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create(['email' => 'admin_create_supplier@t.com']);
        $user->forceFill(['role' => 'admin'])->save();

        return $user;
    }

    private function category(): Category
    {
        return Category::firstOrCreate(
            ['slug' => 'clinicas'],
            ['name' => 'Clínicas']
        );
    }

    public function test_tela_mostra_botao_de_nova_empresa(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.suppliers'))
            ->assertOk()
            ->assertSee('Nova empresa');
    }

    public function test_admin_cadastra_empresa_publicada_no_guia(): void
    {
        $this->category();

        Livewire::actingAs($this->admin())
            ->test(ApproveSuppliers::class)
            ->call('openCreate')
            ->assertSet('isCreating', true)
            ->assertSee('Nova Empresa')
            ->set('editName', 'Pet Saúde Campinas')
            ->set('editCategory', 'clinicas')
            ->set('editEmail', 'contato@petsaude.com')
            ->set('editPhone', '1933334444')
            ->set('editWhatsapp', '5519999999999')
            ->set('editCity', 'Campinas')
            ->set('editState', 'SP')
            ->set('editDescription', 'Clínica veterinária do interior.')
            ->set('editApproved', true)
            ->call('create')
            ->assertHasNoErrors()
            ->assertSet('isCreating', false)
            ->assertSet('status', 'approved');

        $supplier = Supplier::where('email', 'contato@petsaude.com')->first();
        $this->assertNotNull($supplier);
        $this->assertEquals('Pet Saúde Campinas', $supplier->name);
        $this->assertEquals('clinicas', $supplier->category);
        $this->assertNotNull($supplier->category_id);
        $this->assertEquals('Campinas', $supplier->city);
        $this->assertEquals('SP', $supplier->state);
        $this->assertTrue($supplier->is_active);
        $this->assertTrue($supplier->is_approved);
        $this->assertFalse($supplier->is_verified);
        $this->assertNotEmpty($supplier->slug);
        $this->assertNull($supplier->user_id);

        $this->get(route('suppliers.index'))
            ->assertOk()
            ->assertSee('Pet Saúde Campinas');
    }

    public function test_admin_pode_cadastrar_como_pendente(): void
    {
        $this->category();

        Livewire::actingAs($this->admin())
            ->test(ApproveSuppliers::class)
            ->call('openCreate')
            ->set('editName', 'Empresa Em Análise')
            ->set('editCategory', 'clinicas')
            ->set('editEmail', 'pendente@empresa.com')
            ->set('editApproved', false)
            ->call('create')
            ->assertHasNoErrors()
            ->assertSet('status', 'pending');

        $supplier = Supplier::where('email', 'pendente@empresa.com')->first();
        $this->assertFalse($supplier->is_approved);

        $this->get(route('suppliers.index'))
            ->assertOk()
            ->assertDontSee('Empresa Em Análise');
    }

    public function test_cidade_vazia_nao_vira_atibaia(): void
    {
        $this->category();

        Livewire::actingAs($this->admin())
            ->test(ApproveSuppliers::class)
            ->call('openCreate')
            ->set('editName', 'Distribuidor Sem Cidade')
            ->set('editCategory', 'clinicas')
            ->set('editEmail', 'semcidade@empresa.com')
            ->set('editCity', '')
            ->set('editState', 'RJ')
            ->call('create')
            ->assertHasNoErrors();

        $supplier = Supplier::where('email', 'semcidade@empresa.com')->first();
        $this->assertNull($supplier->city);
        $this->assertEquals('RJ', $supplier->state);
        $this->assertNotEquals('Atibaia', $supplier->city);
    }

    public function test_email_duplicado_nao_cadastra(): void
    {
        $this->category();
        Supplier::create([
            'name' => 'Já Existe',
            'email' => 'existe@empresa.com',
            'description' => 'd',
            'category' => 'clinicas',
            'is_active' => true,
            'is_approved' => true,
        ]);

        Livewire::actingAs($this->admin())
            ->test(ApproveSuppliers::class)
            ->call('openCreate')
            ->set('editName', 'Outra Empresa')
            ->set('editCategory', 'clinicas')
            ->set('editEmail', 'existe@empresa.com')
            ->call('create')
            ->assertHasErrors(['editEmail']);

        $this->assertEquals(1, Supplier::where('email', 'existe@empresa.com')->count());
    }

    public function test_nome_e_categoria_sao_obrigatorios(): void
    {
        Livewire::actingAs($this->admin())
            ->test(ApproveSuppliers::class)
            ->call('openCreate')
            ->set('editName', 'Ab')
            ->set('editCategory', '')
            ->set('editEmail', 'invalido')
            ->call('create')
            ->assertHasErrors(['editName', 'editCategory', 'editEmail']);

        $this->assertEquals(0, Supplier::count());
    }

    public function test_descricao_vazia_usa_placeholder(): void
    {
        $this->category();

        Livewire::actingAs($this->admin())
            ->test(ApproveSuppliers::class)
            ->call('openCreate')
            ->set('editName', 'Empresa Sem Texto')
            ->set('editCategory', 'clinicas')
            ->set('editEmail', 'semtexto@empresa.com')
            ->set('editDescription', '   ')
            ->call('create')
            ->assertHasNoErrors();

        $this->assertEquals(
            'Empresa ainda não preencheu esta informação.',
            Supplier::where('email', 'semtexto@empresa.com')->value('description')
        );
    }
}
