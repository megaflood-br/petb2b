<?php

namespace Tests\Feature;

use App\Livewire\Admin\ManageSettings;
use App\Services\Pix\AsaasPixGateway;
use App\Services\Pix\FakePixGateway;
use App\Services\Pix\PixGateway;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_retorna_default_quando_nao_definido(): void
    {
        $this->assertEquals('valor-padrao', Settings::get('inexistente', 'valor-padrao'));
    }

    public function test_set_e_get_roundtrip(): void
    {
        Settings::set('ads_cost_per_click', 1.25);
        $this->assertEquals(1.25, Settings::adsCostPerClick());
    }

    public function test_segredo_e_armazenado_criptografado(): void
    {
        Settings::set('asaas_api_key', 'chave-secreta-123');

        $raw = DB::table('settings')->where('key', 'asaas_api_key')->value('value');
        $this->assertNotEquals('chave-secreta-123', $raw); // criptografado no banco
        $this->assertEquals('chave-secreta-123', Settings::asaasKey()); // decripta na leitura
    }

    public function test_segredo_vazio_nao_sobrescreve(): void
    {
        Settings::set('asaas_webhook_token', 'token-super-secreto-1234');
        Settings::set('asaas_webhook_token', ''); // não deve apagar

        $this->assertEquals('token-super-secreto-1234', Settings::asaasWebhookToken());
    }

    public function test_gateway_pix_usa_configuracao_do_banco(): void
    {
        // Sem chave -> Fake.
        $this->assertInstanceOf(FakePixGateway::class, app(PixGateway::class));

        // Com chave definida no painel -> Asaas.
        Settings::set('asaas_api_key', 'aact_teste');
        $this->assertInstanceOf(AsaasPixGateway::class, app(PixGateway::class));
    }

    public function test_admin_salva_configuracoes(): void
    {
        Livewire::test(ManageSettings::class)
            ->set('ads_cost_per_click', 0.99)
            ->set('ads_sponsored_post_cost', 200)
            ->set('asaas_api_key', 'nova-chave-abc')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals(0.99, Settings::adsCostPerClick());
        $this->assertEquals(200.0, Settings::sponsoredPostCost());
        $this->assertEquals('nova-chave-abc', Settings::asaasKey());
    }
}
