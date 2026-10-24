<?php

namespace Tests\Feature;

use App\Livewire\Admin\ApproveSuppliers;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_admin_cadastra_empresa_com_logo(): void
    {
        Storage::fake('public');
        $this->category();

        Livewire::actingAs($this->admin())
            ->test(ApproveSuppliers::class)
            ->call('openCreate')
            ->assertSee('Logo da empresa')
            ->set('editName', 'Marca Com Logo')
            ->set('editCategory', 'clinicas')
            ->set('editEmail', 'logo@empresa.com')
            ->set('editLogo', UploadedFile::fake()->image('marca.png', 200, 200))
            ->call('create')
            ->assertHasNoErrors();

        $supplier = Supplier::where('email', 'logo@empresa.com')->first();
        $this->assertNotNull($supplier);
        $this->assertNotEmpty($supplier->logo);
        $this->assertStringStartsWith('suppliers/logos/', $supplier->logo);
        Storage::disk('public')->assertExists($supplier->logo);
    }

    public function test_admin_atualiza_logo_ao_editar(): void
    {
        Storage::fake('public');
        $this->category();
        Storage::disk('public')->put('suppliers/logos/antigo.png', 'old');

        $supplier = Supplier::create([
            'name' => 'Empresa Editada',
            'email' => 'editar-logo@empresa.com',
            'description' => 'Clínica veterinária com atendimento completo.',
            'address' => 'Rua das Flores, 10',
            'city' => 'Campinas',
            'state' => 'SP',
            'phone' => '1933334444',
            'category' => 'clinicas',
            'logo' => 'suppliers/logos/antigo.png',
            'is_active' => true,
            'is_approved' => true,
        ]);

        Livewire::actingAs($this->admin())
            ->test(ApproveSuppliers::class)
            ->call('setStatus', 'approved')
            ->call('edit', $supplier->id)
            ->assertSet('existingLogo', 'suppliers/logos/antigo.png')
            ->assertSee('Logo da empresa')
            ->assertSee('Endereço (Rua, Nº, Bairro)')
            ->assertSee('Descrição')
            ->assertSee('CPF ou CNPJ')
            ->assertSee('Website')
            ->set('editLogo', UploadedFile::fake()->image('novo.jpg', 180, 180))
            ->call('update')
            ->assertHasNoErrors();

        $supplier->refresh();
        $this->assertNotEquals('suppliers/logos/antigo.png', $supplier->logo);
        $this->assertStringStartsWith('suppliers/logos/', $supplier->logo);
        Storage::disk('public')->assertMissing('suppliers/logos/antigo.png');
        Storage::disk('public')->assertExists($supplier->logo);
    }

    public function test_editar_sem_novo_logo_mantem_o_atual(): void
    {
        Storage::fake('public');
        $this->category();
        Storage::disk('public')->put('suppliers/logos/keep.png', 'keep');

        $supplier = Supplier::create([
            'name' => 'Empresa Mantem Logo',
            'email' => 'mantem-logo@empresa.com',
            'description' => 'Clínica veterinária com atendimento completo.',
            'address' => 'Rua das Flores, 10',
            'city' => 'Campinas',
            'state' => 'SP',
            'phone' => '1933334444',
            'category' => 'clinicas',
            'logo' => 'suppliers/logos/keep.png',
            'is_active' => true,
            'is_approved' => true,
        ]);

        Livewire::actingAs($this->admin())
            ->test(ApproveSuppliers::class)
            ->call('setStatus', 'approved')
            ->call('edit', $supplier->id)
            ->set('editName', 'Empresa Mantem Logo Atualizada')
            ->call('update')
            ->assertHasNoErrors();

        $this->assertEquals('suppliers/logos/keep.png', $supplier->fresh()->logo);
        Storage::disk('public')->assertExists('suppliers/logos/keep.png');
    }

    public function test_editar_salva_todos_os_campos_do_selo(): void
    {
        $this->category();

        $supplier = Supplier::create([
            'name' => 'WS Metalúrgica',
            'email' => 'vendas@wscorrentes.com.br',
            'description' => 'Empresa ainda não preencheu esta informação.',
            'address' => 'Endereço a completar',
            'category' => 'distribuidores',
            'city' => 'Ribeirão Pires',
            'state' => 'SP',
            'phone' => '1148273444',
            'whatsapp' => '5511997816098',
            'logo' => 'suppliers/logos/ws.png',
            'is_active' => true,
            'is_approved' => true,
        ]);

        $this->assertFalse($supplier->hasCompleteProfile());

        Livewire::actingAs($this->admin())
            ->test(ApproveSuppliers::class)
            ->call('setStatus', 'approved')
            ->call('edit', $supplier->id)
            ->assertSet('editDescription', '')
            ->assertSet('editAddress', '')
            ->assertSee('Endereço (Rua, Nº, Bairro)')
            ->set('editDescription', 'A WS Metalúrgica produz correntes e fixadores para o mercado pet.')
            ->set('editAddress', 'Rua das Indústrias, 100 - Centro')
            ->set('editWebsite', 'https://wscorrentes.com.br')
            ->set('editCnpj', '12.345.678/0001-95')
            ->call('update')
            ->assertHasNoErrors();

        $supplier->refresh();
        $this->assertEquals('A WS Metalúrgica produz correntes e fixadores para o mercado pet.', $supplier->description);
        $this->assertEquals('Rua das Indústrias, 100 - Centro', $supplier->address);
        $this->assertEquals('https://wscorrentes.com.br', $supplier->website);
        $this->assertNotEmpty($supplier->cnpj);
        $this->assertTrue($supplier->hasCompleteProfile());
    }

    public function test_editar_exige_descricao_endereco_cidade_e_contato(): void
    {
        $this->category();

        $supplier = Supplier::create([
            'name' => 'Empresa Incompleta',
            'email' => 'incompleta@empresa.com',
            'description' => 'Empresa ainda não preencheu esta informação.',
            'address' => 'Endereço a completar',
            'category' => 'clinicas',
            'is_active' => true,
            'is_approved' => true,
        ]);

        Livewire::actingAs($this->admin())
            ->test(ApproveSuppliers::class)
            ->call('setStatus', 'approved')
            ->call('edit', $supplier->id)
            ->set('editCity', '')
            ->set('editState', '')
            ->set('editPhone', '')
            ->set('editWhatsapp', '')
            ->call('update')
            ->assertHasErrors(['editDescription', 'editAddress', 'editCity', 'editState', 'editPhone', 'editWhatsapp']);
    }
}
