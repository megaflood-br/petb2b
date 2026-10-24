<?php

namespace Tests\Feature;

use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierCompleteProfileTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function completeAttrs(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Alfa Pet Foods',
            'email' => 'contato@alfapet.com.br',
            'category' => 'distribuidores',
            'description' => 'Distribuidora de alimentos e acessórios para o mercado pet.',
            'address' => 'Rua das Indústrias, 100',
            'city' => 'Atibaia',
            'state' => 'SP',
            'phone' => '11999999999',
            'whatsapp' => '11999999999',
            'logo' => 'suppliers/logos/alfa.png',
            'is_active' => true,
            'is_approved' => true,
        ], $overrides);
    }

    public function test_perfil_preenchido_e_completo(): void
    {
        $this->assertTrue((new Supplier($this->completeAttrs()))->hasCompleteProfile());
    }

    public function test_descricao_e_endereco_de_importacao_nao_sao_completos(): void
    {
        $supplier = new Supplier($this->completeAttrs([
            'description' => 'Empresa ainda não preencheu esta informação.',
            'address' => 'Endereço a completar',
        ]));

        $this->assertFalse($supplier->hasCompleteProfile());
    }

    public function test_admin_completa_o_perfil_e_ganha_selo(): void
    {
        $supplier = new Supplier($this->completeAttrs([
            'name' => 'WS Metalúrgica',
            'email' => 'vendas@wscorrentes.com.br',
            'category' => 'distribuidores',
            'city' => 'Ribeirão Pires',
            'state' => 'SP',
            'phone' => '1148273444',
            'whatsapp' => '5511997816098',
            'logo' => 'suppliers/logos/ws.png',
            'description' => 'Empresa ainda não preencheu esta informação.',
            'address' => 'Endereço a completar',
        ]));

        $this->assertFalse($supplier->hasCompleteProfile());

        $supplier->fill([
            'description' => 'A WS Metalúrgica produz correntes e fixadores para o mercado pet.',
            'address' => 'Rua das Indústrias, 100 - Ribeirão Pires',
        ]);

        $this->assertTrue($supplier->hasCompleteProfile());
    }

    public function test_email_temporario_nao_e_completo(): void
    {
        $supplier = new Supplier($this->completeAttrs([
            'email' => 'alfa-pet@temporario-ab12.com.br',
        ]));

        $this->assertFalse($supplier->hasCompleteProfile());
    }

    public function test_sem_logo_ou_contato_nao_e_completo(): void
    {
        $this->assertFalse((new Supplier($this->completeAttrs([
            'logo' => null,
        ])))->hasCompleteProfile());

        $this->assertFalse((new Supplier($this->completeAttrs([
            'phone' => null,
            'whatsapp' => '',
        ])))->hasCompleteProfile());
    }

    public function test_guia_mostra_selo_azul_somente_com_perfil_completo(): void
    {
        $complete = Supplier::create($this->completeAttrs([
            'name' => 'Alfa Completa',
            'email' => 'alfa_completa_' . uniqid() . '@alfapet.com.br',
            'slug' => 'alfa-completa-' . uniqid(),
        ]));

        $incomplete = Supplier::create($this->completeAttrs([
            'name' => 'Beta Incompleta',
            'email' => 'beta_incompleta_' . uniqid() . '@t.com',
            'slug' => 'beta-incompleta-' . uniqid(),
            'logo' => null,
            'phone' => null,
            'whatsapp' => null,
        ]));

        $this->get('/fornecedores')
            ->assertOk()
            ->assertSee('Alfa Completa')
            ->assertSee('Beta Incompleta')
            ->assertSee('title="Perfil completo"', false)
            ->assertSee('data-profile-complete="' . $complete->id . '"', false)
            ->assertDontSee('data-profile-complete="' . $incomplete->id . '"', false)
            ->assertDontSee('Fornecedor Verificado Oficial');
    }
}
