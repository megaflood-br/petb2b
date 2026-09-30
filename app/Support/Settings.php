<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * Configurações editáveis pelo painel admin, com fallback para config()/.env.
 *
 * Valores ficam no banco (tabela `settings`); chaves sensíveis (API key,
 * webhook token) são armazenadas criptografadas. Um cache evita consultar o
 * banco a cada leitura e é invalidado ao salvar.
 */
class Settings
{
    private const CACHE_KEY = 'settings.all';

    /** Chaves cujo valor é criptografado no banco. */
    public const ENCRYPTED = ['asaas_api_key', 'asaas_webhook_token'];

    public static function get(string $key, $default = null)
    {
        $raw = self::all()[$key] ?? null;

        if ($raw === null || $raw === '') {
            return $default;
        }

        if (in_array($key, self::ENCRYPTED, true)) {
            try {
                return decrypt($raw);
            } catch (\Throwable $e) {
                return $default;
            }
        }

        return $raw;
    }

    public static function set(string $key, $value): void
    {
        // Campo vazio não sobrescreve segredo existente (mantém o atual).
        if (in_array($key, self::ENCRYPTED, true)) {
            if ($value === null || $value === '') {
                return;
            }
            $value = encrypt($value);
        }

        Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget(self::CACHE_KEY);
    }

    public static function has(string $key): bool
    {
        $raw = self::all()[$key] ?? null;

        return $raw !== null && $raw !== '';
    }

    /** @return array<string,mixed> */
    private static function all(): array
    {
        try {
            return Cache::rememberForever(self::CACHE_KEY, function () {
                return Setting::query()->pluck('value', 'key')->all();
            });
        } catch (\Throwable $e) {
            // Ex.: tabela ainda não migrada (durante setup) — usa só defaults.
            return [];
        }
    }

    // ----- Getters tipados (com fallback para config/.env) -----

    public static function adsCostPerClick(): float
    {
        return (float) self::get('ads_cost_per_click', config('ads.cost_per_click'));
    }

    public static function adsCostPerImpression(): float
    {
        return (float) self::get('ads_cost_per_impression', config('ads.cost_per_impression'));
    }

    public static function adsRechargeMin(): float
    {
        return (float) self::get('ads_recharge_min', config('ads.recharge_min'));
    }

    public static function adsRechargeMax(): float
    {
        return (float) self::get('ads_recharge_max', config('ads.recharge_max'));
    }

    public static function sponsoredPostCost(): float
    {
        return (float) self::get('ads_sponsored_post_cost', config('ads.sponsored_post_cost'));
    }

    public static function asaasKey(): ?string
    {
        return self::get('asaas_api_key', config('services.asaas.key'));
    }

    public static function asaasWebhookToken(): ?string
    {
        return self::get('asaas_webhook_token', config('services.asaas.webhook_token'));
    }

    public static function asaasBaseUrl(): string
    {
        return (string) self::get('asaas_base_url', config('services.asaas.base_url'));
    }

    public static function maintenanceEnabled(): bool
    {
        return filter_var(self::get('maintenance_enabled', false), FILTER_VALIDATE_BOOLEAN);
    }

    public static function maintenanceMessage(): string
    {
        $default = 'Estamos em manutenção para melhorar o portal. Voltaremos em breve.';
        $custom = trim((string) self::get('maintenance_message', ''));

        return $custom !== '' ? $custom : $default;
    }

    public static function siteName(): string
    {
        $custom = trim((string) self::get('seo_site_name', ''));

        return $custom !== '' ? $custom : 'Revista Negócios Pet';
    }

    public static function seoDescription(): string
    {
        $custom = trim((string) self::get('seo_default_description', ''));

        return $custom !== ''
            ? $custom
            : 'Portal B2B da Revista Negócios Pet: notícias, fornecedores e conteúdo do mercado pet brasileiro.';
    }

    /** @return array<int, string> */
    public static function seoKeywords(): array
    {
        $raw = trim((string) self::get('seo_default_keywords', ''));
        if ($raw === '') {
            return ['negócios pet', 'mercado pet', 'fornecedores pet', 'revista pet'];
        }

        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }

    public static function seoKeywordsLine(): string
    {
        return implode(', ', self::seoKeywords());
    }

    public static function themeColor(): string
    {
        $custom = trim((string) self::get('seo_theme_color', ''));

        return $custom !== '' ? $custom : '#ed258f';
    }

    public static function ga4Id(): ?string
    {
        $value = trim((string) self::get('seo_ga4_id', ''));

        return $value !== '' ? $value : null;
    }

    public static function gtmId(): ?string
    {
        $value = trim((string) self::get('seo_gtm_id', ''));

        return $value !== '' ? $value : null;
    }

    public static function googleVerification(): ?string
    {
        $value = trim((string) self::get('seo_google_verification', ''));

        return $value !== '' ? $value : null;
    }

    public static function bingVerification(): ?string
    {
        $value = trim((string) self::get('seo_bing_verification', ''));

        return $value !== '' ? $value : null;
    }

    public static function twitterHandle(): ?string
    {
        $value = ltrim(trim((string) self::get('seo_twitter', '')), '@');

        return $value !== '' ? '@'.$value : null;
    }

    public static function robotsExtra(): string
    {
        return trim((string) self::get('seo_robots_extra', ''));
    }

    public static function faviconPath(): ?string
    {
        $path = trim((string) self::get('seo_favicon', ''));

        return $path !== '' ? $path : null;
    }

    public static function ogImagePath(): ?string
    {
        $path = trim((string) self::get('seo_og_image', ''));

        return $path !== '' ? $path : null;
    }

    public static function faviconUrl(): ?string
    {
        return self::publicStorageUrl(self::faviconPath());
    }

    public static function ogImageUrl(): ?string
    {
        return self::publicStorageUrl(self::ogImagePath());
    }

    /** @return array<int, string> */
    public static function socialProfiles(): array
    {
        $urls = [
            self::get('seo_facebook'),
            self::get('seo_instagram'),
            self::get('seo_linkedin'),
            self::get('seo_youtube'),
        ];

        return array_values(array_filter(array_map(function ($url) {
            $url = trim((string) $url);

            return $url !== '' ? $url : null;
        }, $urls)));
    }

    private static function publicStorageUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset('storage/'.ltrim($path, '/'));
    }
}
