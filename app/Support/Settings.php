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
}
