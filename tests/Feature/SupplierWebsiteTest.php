<?php

namespace Tests\Feature;

use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierWebsiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_website_sem_protocolo_vira_https(): void
    {
        $supplier = new Supplier(['website' => 'wscorrentes.com.br']);

        $this->assertSame('https://wscorrentes.com.br', $supplier->websiteUrl());
    }

    public function test_website_vazio_nao_gera_link(): void
    {
        $this->assertNull((new Supplier(['website' => null]))->websiteUrl());
        $this->assertNull((new Supplier(['website' => '  ']))->websiteUrl());
        $this->assertNull((new Supplier(['website' => 'javascript:alert(1)']))->websiteUrl());
    }

    public function test_perfil_mostra_botao_do_site(): void
    {
        $withSite = Supplier::create([
            'name' => 'WS Metalúrgica',
            'slug' => 'ws-metalurgica-site',
            'email' => 'vendas@wscorrentes.com.br',
            'description' => 'Correntes e fixadores.',
            'category' => 'distribuidores',
            'website' => 'https://wscorrentes.com.br',
            'is_active' => true,
            'is_approved' => true,
        ]);

        $withoutSite = Supplier::create([
            'name' => 'Empresa Sem Site',
            'slug' => 'empresa-sem-site',
            'email' => 'semsite@t.com',
            'description' => 'Sem website cadastrado.',
            'category' => 'distribuidores',
            'is_active' => true,
            'is_approved' => true,
        ]);

        $this->get(route('suppliers.show', $withSite->slug))
            ->assertOk()
            ->assertSee('Visitar Site')
            ->assertSee('href="https://wscorrentes.com.br"', false);

        $this->get(route('suppliers.show', $withoutSite->slug))
            ->assertOk()
            ->assertDontSee('Visitar Site');
    }
}
