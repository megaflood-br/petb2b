<?php

namespace Tests\Feature;

use App\Imports\SuppliersImport;
use App\Livewire\Supplier\EditProfile;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SuppliersImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_cidade_vazia_nao_vira_atibaia(): void
    {
        $supplier = (new SuppliersImport)->model([
            'column1' => 'Rações',
            'column2' => 'RJ',
            'column3' => '',
            'column4' => 'Empresa Sem Cidade',
            'column5' => '21999999999',
            'column6' => 'semcidade@t.com',
        ]);

        $this->assertInstanceOf(Supplier::class, $supplier);
        $this->assertNull($supplier->city);
        $this->assertEquals('RJ', $supplier->state);
        $this->assertEquals('Empresa Sem Cidade', $supplier->name);
    }

    public function test_coluna_de_cidade_ausente_fica_vazia(): void
    {
        $supplier = (new SuppliersImport)->model([
            'column1' => 'Higiene',
            'column2' => 'SP',
            'column4' => 'Só com Estado',
            'column6' => 'soestado@t.com',
        ]);

        $this->assertNull($supplier->city);
        $this->assertEquals('SP', $supplier->state);
        $this->assertNotEquals('Atibaia', $supplier->city);
    }

    public function test_cidade_preenchida_e_preservada(): void
    {
        $supplier = (new SuppliersImport)->model([
            'column1' => 'Brinquedos',
            'column2' => 'MG',
            'column3' => 'Belo Horizonte',
            'column4' => 'Empresa Com Cidade',
            'column6' => 'comcidade@t.com',
        ]);

        $this->assertEquals('Belo Horizonte', $supplier->city);
        $this->assertEquals('MG', $supplier->state);
    }

    public function test_aceita_cabecalho_cidade_e_nao_inventa_atibaia(): void
    {
        $supplier = (new SuppliersImport)->model([
            'categoria' => 'Medicamentos',
            'uf' => 'BA',
            'cidade' => '   ',
            'nome' => 'Pet Nordeste',
            'email' => 'nordeste@t.com',
        ]);

        $this->assertNull($supplier->city);
        $this->assertEquals('BA', $supplier->state);
    }

    public function test_reimportar_com_cidade_vazia_nao_grava_atibaia(): void
    {
        Supplier::create([
            'name' => 'Já Existia',
            'email' => 'existe@t.com',
            'description' => 'd',
            'category' => 'racas',
            'city' => 'Atibaia',
            'state' => 'SP',
            'is_active' => true,
            'is_approved' => false,
        ]);

        $result = (new SuppliersImport)->model([
            'column1' => 'Rações',
            'column2' => 'PR',
            'column3' => '',
            'column4' => 'Já Existia Atualizada',
            'column6' => 'existe@t.com',
        ]);

        $this->assertNull($result);

        $supplier = Supplier::where('email', 'existe@t.com')->first();
        $this->assertEquals('Já Existia Atualizada', $supplier->name);
        $this->assertNull($supplier->city);
        $this->assertEquals('PR', $supplier->state);
        $this->assertNotEquals('Atibaia', $supplier->city);
    }

    public function test_perfil_do_dono_nao_preenche_cidade_com_atibaia(): void
    {
        $user = User::factory()->create(['name' => 'Dono Novo', 'email' => 'dono_novo@t.com']);
        $user->forceFill(['role' => 'supplier'])->save();

        Livewire::actingAs($user)
            ->test(EditProfile::class)
            ->assertSet('city', null)
            ->assertSet('state', null);

        $this->assertDatabaseHas('suppliers', [
            'user_id' => $user->id,
            'city' => null,
            'state' => null,
        ]);
        $this->assertDatabaseMissing('suppliers', [
            'user_id' => $user->id,
            'city' => 'Atibaia',
        ]);
    }
}
