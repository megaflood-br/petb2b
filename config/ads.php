<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Custos padrão de anúncios (modelo estilo Google Ads)
    |--------------------------------------------------------------------------
    |
    | Valores em Reais (R$). São usados como padrão ao criar uma campanha.
    | Mantidos em config (e não via env() direto no código) para funcionarem
    | corretamente mesmo com `php artisan config:cache` em produção.
    |
    */

    'cost_per_click' => (float) env('ADS_COST_PER_CLICK', 0.50),

    'cost_per_impression' => (float) env('ADS_COST_PER_IMPRESSION', 0.0070),

    /*
    |--------------------------------------------------------------------------
    | Limites de recarga de saldo
    |--------------------------------------------------------------------------
    */

    'recharge_min' => (float) env('ADS_RECHARGE_MIN', 10),

    'recharge_max' => (float) env('ADS_RECHARGE_MAX', 5000),

    /*
    |--------------------------------------------------------------------------
    | Tracking de eventos (impressões/cliques)
    |--------------------------------------------------------------------------
    |
    | Quando true, a cobrança é enfileirada (exige um worker rodando, ex.:
    | `php artisan queue:work` via Supervisor). Quando false (padrão), a
    | cobrança roda com dispatchAfterResponse — assíncrona em relação à
    | resposta e sem exigir worker.
    |
    */

    'tracking_queue' => (bool) env('ADS_TRACKING_QUEUE', false),

    /*
    |--------------------------------------------------------------------------
    | Matéria patrocinada (publieditorial)
    |--------------------------------------------------------------------------
    |
    | Custo em créditos (R$) para o fornecedor publicar uma matéria patrocinada.
    |
    */

    'sponsored_post_cost' => (float) env('ADS_SPONSORED_POST_COST', 150),

];
