<?php

namespace App\Services;

use App\Jobs\ChargeAdEvent;
use App\Models\Advertisement;
use Illuminate\Support\Facades\Cache;

/**
 * Registra eventos de anúncio (impressões/cliques) com:
 *  - Antifraude: deduplicação por (anúncio + tipo + IP + user-agent) dentro de
 *    uma janela de tempo, evitando que F5/bots drenem o crédito do anunciante.
 *  - Assíncrono: a cobrança é despachada para fora do caminho síncrono da
 *    resposta (afterResponse por padrão; fila quando ADS_TRACKING_QUEUE=true).
 */
class AdTracker
{
    public function record(Advertisement $ad, string $type): bool
    {
        if (! in_array($type, ['click', 'impression'], true)) {
            return false;
        }

        $request = request();
        $fingerprint = sha1(($request?->ip() ?? '') . '|' . ($request?->userAgent() ?? ''));
        $key = sprintf('adtrack:%d:%s:%s', $ad->id, $type, $fingerprint);

        $ttl = $type === 'click'
            ? now()->addHours(24)
            : now()->addMinutes(30);

        // Cache::add é atômico (put-if-absent): retorna false se a chave já
        // existe na janela — ou seja, evento duplicado, ignorado.
        if (! Cache::add($key, 1, $ttl)) {
            return false;
        }

        if (config('ads.tracking_queue')) {
            ChargeAdEvent::dispatch($ad->id, $type);
        } else {
            ChargeAdEvent::dispatchAfterResponse($ad->id, $type);
        }

        return true;
    }
}
