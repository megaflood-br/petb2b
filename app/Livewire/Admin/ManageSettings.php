<?php

namespace App\Livewire\Admin;

use App\Support\Settings;
use Illuminate\Support\Facades\Storage;
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

    public bool $maintenance_enabled = false;
    public string $maintenance_message = '';

    public string $seo_site_name = '';
    public string $seo_default_description = '';
    public string $seo_default_keywords = '';
    public string $seo_theme_color = '#ed258f';
    public string $seo_ga4_id = '';
    public string $seo_gtm_id = '';
    public string $seo_google_verification = '';
    public string $seo_bing_verification = '';
    public string $seo_twitter = '';
    public string $seo_facebook = '';
    public string $seo_instagram = '';
    public string $seo_linkedin = '';
    public string $seo_youtube = '';
    public string $seo_robots_extra = '';
    public $seo_favicon;
    public $seo_og_image;
    public ?string $existing_favicon = null;
    public ?string $existing_og_image = null;

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

        $this->maintenance_enabled = Settings::maintenanceEnabled();
        $this->maintenance_message = Settings::maintenanceMessage();

        $this->seo_site_name = Settings::siteName();
        $this->seo_default_description = Settings::seoDescription();
        $this->seo_default_keywords = Settings::seoKeywordsLine();
        $this->seo_theme_color = Settings::themeColor();
        $this->seo_ga4_id = (string) (Settings::ga4Id() ?? '');
        $this->seo_gtm_id = (string) (Settings::gtmId() ?? '');
        $this->seo_google_verification = (string) (Settings::googleVerification() ?? '');
        $this->seo_bing_verification = (string) (Settings::bingVerification() ?? '');
        $this->seo_twitter = ltrim((string) (Settings::twitterHandle() ?? ''), '@');
        $this->seo_facebook = (string) Settings::get('seo_facebook', '');
        $this->seo_instagram = (string) Settings::get('seo_instagram', '');
        $this->seo_linkedin = (string) Settings::get('seo_linkedin', '');
        $this->seo_youtube = (string) Settings::get('seo_youtube', '');
        $this->seo_robots_extra = Settings::robotsExtra();
        $this->existing_favicon = Settings::faviconPath();
        $this->existing_og_image = Settings::ogImagePath();
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
            'maintenance_enabled' => 'boolean',
            'maintenance_message' => 'nullable|string|max:500',
        ];
    }

    public function updatedMaintenanceEnabled(): void
    {
        Settings::set('maintenance_enabled', $this->maintenance_enabled ? '1' : '0');
        session()->flash('message', $this->maintenance_enabled
            ? 'O site público está em manutenção.'
            : 'O site público voltou ao ar.');
    }

    public function saveMaintenance(): void
    {
        $this->validate([
            'maintenance_message' => 'nullable|string|max:500',
        ]);

        Settings::set('maintenance_enabled', $this->maintenance_enabled ? '1' : '0');
        Settings::set('maintenance_message', $this->maintenance_message);
        session()->flash('message', 'Aviso de manutenção atualizado.');
    }

    public function saveSeo(): void
    {
        $this->validate([
            'seo_site_name' => 'required|string|min:3|max:70',
            'seo_default_description' => 'required|string|min:20|max:180',
            'seo_default_keywords' => 'nullable|string|max:255',
            'seo_theme_color' => 'nullable|string|max:20',
            'seo_ga4_id' => 'nullable|string|max:40',
            'seo_gtm_id' => 'nullable|string|max:40',
            'seo_google_verification' => 'nullable|string|max:120',
            'seo_bing_verification' => 'nullable|string|max:120',
            'seo_twitter' => 'nullable|string|max:40',
            'seo_facebook' => 'nullable|string|max:255',
            'seo_instagram' => 'nullable|string|max:255',
            'seo_linkedin' => 'nullable|string|max:255',
            'seo_youtube' => 'nullable|string|max:255',
            'seo_robots_extra' => 'nullable|string|max:2000',
            'seo_favicon' => 'nullable|mimes:ico,png,jpg,jpeg,webp,svg|max:1024',
            'seo_og_image' => 'nullable|image|max:2048',
        ]);

        Settings::set('seo_site_name', trim($this->seo_site_name));
        Settings::set('seo_default_description', trim($this->seo_default_description));
        Settings::set('seo_default_keywords', trim($this->seo_default_keywords));
        Settings::set('seo_theme_color', trim($this->seo_theme_color) ?: '#ed258f');
        Settings::set('seo_ga4_id', trim($this->seo_ga4_id));
        Settings::set('seo_gtm_id', trim($this->seo_gtm_id));
        Settings::set('seo_google_verification', trim($this->seo_google_verification));
        Settings::set('seo_bing_verification', trim($this->seo_bing_verification));
        Settings::set('seo_twitter', ltrim(trim($this->seo_twitter), '@'));
        Settings::set('seo_facebook', trim($this->seo_facebook));
        Settings::set('seo_instagram', trim($this->seo_instagram));
        Settings::set('seo_linkedin', trim($this->seo_linkedin));
        Settings::set('seo_youtube', trim($this->seo_youtube));
        Settings::set('seo_robots_extra', trim($this->seo_robots_extra));

        if ($this->seo_favicon) {
            if ($this->existing_favicon) {
                Storage::disk('public')->delete($this->existing_favicon);
            }
            $this->existing_favicon = $this->seo_favicon->store('site', 'public');
            Settings::set('seo_favicon', $this->existing_favicon);
            $this->reset('seo_favicon');
        }

        if ($this->seo_og_image) {
            if ($this->existing_og_image) {
                Storage::disk('public')->delete($this->existing_og_image);
            }
            $this->existing_og_image = $this->seo_og_image->store('site', 'public');
            Settings::set('seo_og_image', $this->existing_og_image);
            $this->reset('seo_og_image');
        }

        session()->flash('message', 'SEO e marca salvos. O Google leva um tempo para recrawlear — envie o sitemap no Search Console.');
    }

    public function save(): void
    {
        $this->validate();

        Settings::set('maintenance_enabled', $this->maintenance_enabled ? '1' : '0');
        Settings::set('maintenance_message', $this->maintenance_message);

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

    #[Layout('layouts.admin')]
    public function render()
    {
        return view('livewire.admin.manage-settings');
    }
}
