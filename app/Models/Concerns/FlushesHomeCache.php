<?php

namespace App\Models\Concerns;

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Cache;

/**
 * Invalida o cache das seções da home quando o conteúdo exibido nela muda.
 * Fecha o trade-off do TTL: novo conteúdo aparece imediatamente, sem esperar
 * a expiração.
 */
trait FlushesHomeCache
{
    protected static function bootFlushesHomeCache(): void
    {
        static::saved(fn () => Cache::forget(HomeController::CACHE_KEY));
        static::deleted(fn () => Cache::forget(HomeController::CACHE_KEY));
    }
}
