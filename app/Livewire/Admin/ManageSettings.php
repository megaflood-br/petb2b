<?php

namespace App\Livewire\Admin;

use App\Services\Wordpress\WordpressXmlImporter;
use App\Support\Settings;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

class ManageSettings extends Component
{
    use WithFileUploads;

    // Custos de anúncios
    public $ads_cost_per_click;
    public $ads_cost_per_impression;
    public $ads_recharge_min;
    public $ads_recharge_max;
    public $ads_sponsored_post_cost;

    // Asaas
    public $asaas_base_url;
    public $asaas_api_key = '';       // em branco = mantém o atual
    public $asaas_webhook_token = ''; // em branco = mantém o atual
    public bool $asaas_key_set = false;
    public bool $asaas_token_set = false;

    public $wordpressXml = null;
    public bool $wordpressDownloadImages = true;
    public ?string $wordpressImportSummary = null;
    /** @var list<string> */
    public array $wordpressImportErrors = [];

    public function mount(): void
    {
        $this->ads_cost_per_click = Settings::adsCostPerClick();
        $this->ads_cost_per_impression = Settings::adsCostPerImpression();
        $this->ads_recharge_min = Settings::adsRechargeMin();
        $this->ads_recharge_max = Settings::adsRechargeMax();
        $this->ads_sponsored_post_cost = Settings::sponsoredPostCost();

        $this->asaas_base_url = Settings::asaasBaseUrl();
        $this->asaas_key_set = ! empty(Settings::asaasKey());
        $this->asaas_token_set = ! empty(Settings::asaasWebhookToken());
    }

    protected function rules(): array
    {
        return [
            'ads_cost_per_click' => 'required|numeric|min:0',
            'ads_cost_per_impression' => 'required|numeric|min:0',
            'ads_recharge_min' => 'required|numeric|min:0',
            'ads_recharge_max' => 'required|numeric|gte:ads_recharge_min',
            'ads_sponsored_post_cost' => 'required|numeric|min:0',
            'asaas_base_url' => 'required|url',
            'asaas_api_key' => 'nullable|string',
            'asaas_webhook_token' => 'nullable|string|min:16',
        ];
    }

    public function save(): void
    {
        $this->validate();

        Settings::set('ads_cost_per_click', $this->ads_cost_per_click);
        Settings::set('ads_cost_per_impression', $this->ads_cost_per_impression);
        Settings::set('ads_recharge_min', $this->ads_recharge_min);
        Settings::set('ads_recharge_max', $this->ads_recharge_max);
        Settings::set('ads_sponsored_post_cost', $this->ads_sponsored_post_cost);
        Settings::set('asaas_base_url', $this->asaas_base_url);

        // Segredos: só atualizam se preenchidos (Settings ignora valor vazio).
        Settings::set('asaas_api_key', $this->asaas_api_key);
        Settings::set('asaas_webhook_token', $this->asaas_webhook_token);

        $this->reset(['asaas_api_key', 'asaas_webhook_token']);
        $this->asaas_key_set = ! empty(Settings::asaasKey());
        $this->asaas_token_set = ! empty(Settings::asaasWebhookToken());

        session()->flash('message', 'Configurações salvas com sucesso!');
    }

    public function importWordpress(WordpressXmlImporter $importer): void
    {
        $this->validate([
            'wordpressXml' => 'required|file|extensions:xml|max:20480',
        ], [
            'wordpressXml.required' => 'Envie o arquivo XML exportado do WordPress.',
            'wordpressXml.extensions' => 'O arquivo precisa ser um XML (.xml).',
            'wordpressXml.max' => 'O XML pode ter no máximo 20 MB.',
        ]);

        $this->wordpressImportSummary = null;
        $this->wordpressImportErrors = [];

        try {
            $result = $importer->import(
                $this->wordpressXml->getRealPath(),
                $this->wordpressDownloadImages
            );
        } catch (\InvalidArgumentException $e) {
            $this->addError('wordpressXml', $e->getMessage());

            return;
        }

        $this->wordpressImportSummary = $result->summary();
        $this->wordpressImportErrors = $result->errors;
        $this->reset('wordpressXml');

        session()->flash('message', $result->summary());
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        return view('livewire.admin.manage-settings');
    }
}
