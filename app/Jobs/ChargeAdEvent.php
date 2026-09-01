<?php

namespace App\Jobs;

use App\Models\Advertisement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Cobra (debita créditos) um evento de anúncio fora do caminho síncrono da
 * requisição. Despachado por App\Services\AdTracker após passar pelo
 * antifraude de deduplicação.
 */
class ChargeAdEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $advertisementId,
        public string $type,
    ) {
    }

    public function handle(): void
    {
        $ad = Advertisement::find($this->advertisementId);

        if (! $ad) {
            return;
        }

        if ($this->type === 'click') {
            $ad->chargeClick();
        } else {
            $ad->chargeImpression();
        }
    }
}
