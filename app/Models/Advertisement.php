<?php

namespace App\Models;

use App\Services\AdTracker;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Advertisement extends Model
{
    protected $fillable = [
        'supplier_id',
        'title',
        'image_path',
        'link',
        'position',
        'is_active',
        'clicks',
        'views',
        'cost_per_click',
        'cost_per_impression'
    ];

    protected $casts = [
        'views' => 'integer',
        'clicks' => 'integer',
        'is_active' => 'boolean',
        'cost_per_click' => 'decimal:4',
        'cost_per_impression' => 'decimal:4',
    ];

    /**
     * Catálogo comercial de cada posição: rótulo, onde aparece, formato e medida.
     * Fonte única para formulários, tabela de gerenciamento e validações.
     *
     * @return array<string, array{label: string, location: string, format: string, width: int, height: int}>
     */
    public static function getPositionSpecs(): array
    {
        return [
            'banner_topo' => [
                'label' => 'Topo do Site (Geral)',
                'location' => 'Home e páginas gerais',
                'format' => 'Leaderboard',
                'width' => 1200,
                'height' => 160,
            ],
            'sidebar_guia' => [
                'label' => 'Barra Lateral (Guia de Fornecedores)',
                'location' => 'Guia de Fornecedores (sidebar)',
                'format' => 'Retângulo médio',
                'width' => 300,
                'height' => 250,
            ],
            'meio_blog' => [
                'label' => 'Meio do Blog (Entre os Artigos)',
                'location' => 'Listagem do blog',
                'format' => 'Banner',
                'width' => 1200,
                'height' => 200,
            ],
            'post_top' => [
                'label' => 'Topo do Artigo (Interno)',
                'location' => 'Página interna do artigo',
                'format' => 'Leaderboard',
                'width' => 1200,
                'height' => 160,
            ],
            'post_footer' => [
                'label' => 'Rodapé do Artigo (Interno)',
                'location' => 'Página interna do artigo',
                'format' => 'Leaderboard',
                'width' => 1200,
                'height' => 160,
            ],
            'banner_mobile_footer' => [
                'label' => 'Banner Fixo Mobile (Rodapé Celular)',
                'location' => 'Rodapé fixo em todas as páginas (celular)',
                'format' => 'Sticky mobile',
                'width' => 320,
                'height' => 50,
            ],
            'setor_vagas' => [
                'label' => 'Guia de Vagas',
                'location' => 'Página /vagas',
                'format' => 'Leaderboard',
                'width' => 1200,
                'height' => 160,
            ],
            'setor_racas' => [
                'label' => 'Guia de Raças',
                'location' => 'Página /racas',
                'format' => 'Leaderboard',
                'width' => 1200,
                'height' => 160,
            ],
            'setor_analises' => [
                'label' => 'Análises de Produtos',
                'location' => 'Página /analises-produtos',
                'format' => 'Leaderboard',
                'width' => 1200,
                'height' => 160,
            ],
            'setor_eventos' => [
                'label' => 'Agenda / Eventos',
                'location' => 'Página /feiras-pet-2026',
                'format' => 'Leaderboard',
                'width' => 1200,
                'height' => 160,
            ],
            'setor_canis' => [
                'label' => 'Guia de Canis',
                'location' => 'Página /canis',
                'format' => 'Leaderboard',
                'width' => 1200,
                'height' => 160,
            ],
            'setor_classificados' => [
                'label' => 'Classificados',
                'location' => 'Página /classificados',
                'format' => 'Leaderboard',
                'width' => 1200,
                'height' => 160,
            ],
            'setor_revistas' => [
                'label' => 'Estante de Revistas',
                'location' => 'Página /revistas',
                'format' => 'Leaderboard',
                'width' => 1200,
                'height' => 160,
            ],
        ];
    }

    /**
     * Retorna a lista unificada de posições válidas (chave => rótulo).
     */
    public static function getPositions(): array
    {
        $labels = [];

        foreach (static::getPositionSpecs() as $key => $spec) {
            $labels[$key] = $spec['label'];
        }

        return $labels;
    }

    /**
     * Medida recomendada no formato "1200 × 160 px".
     */
    public static function dimensionFor(string $position): string
    {
        $spec = static::getPositionSpecs()[$position] ?? null;

        if (! $spec) {
            return '—';
        }

        return $spec['width'] . ' × ' . $spec['height'] . ' px';
    }

    /**
     * Sorteia um anúncio ativo da posição e registra impressão.
     * Retorna null quando não há campanha rodando (a view mostra "Anuncie aqui").
     */
    public static function pickRandom(string $position): ?self
    {
        $ad = static::query()
            ->where('position', $position)
            ->where('is_active', true)
            ->inRandomOrder()
            ->first();

        if ($ad) {
            $ad->trackImpression();
        }

        return $ad;
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Ponto de entrada de impressão chamado nas views/rotas.
     *
     * Aplica antifraude (dedup por IP+user-agent dentro da janela) e despacha
     * a cobrança para fora do caminho síncrono da resposta. Retorna true quando
     * o evento foi contabilizado (não duplicado).
     */
    public function trackImpression(): bool
    {
        return app(AdTracker::class)->record($this, 'impression');
    }

    /**
     * Ponto de entrada de clique chamado na rota de redirecionamento.
     */
    public function trackClick(): bool
    {
        return app(AdTracker::class)->record($this, 'click');
    }

    /**
     * Cobrança atômica de uma impressão (executada pelo Job de fila).
     *
     * @return bool true quando a cobrança ocorreu; false quando não houve
     *              saldo (e o anúncio foi pausado) ou não há fornecedor.
     */
    public function chargeImpression(): bool
    {
        return $this->charge(
            (float) $this->cost_per_impression,
            'views',
            'expense_impression',
            "Visualização do banner: #{$this->id} - {$this->title}"
        );
    }

    /**
     * Cobrança atômica de um clique (executada pelo Job de fila).
     */
    public function chargeClick(): bool
    {
        return $this->charge(
            (float) $this->cost_per_click,
            'clicks',
            'expense_click',
            "Clique no anúncio: {$this->title}"
        );
    }

    /**
     * Cobrança atômica e segura contra concorrência.
     *
     * Toda a operação (checagem de saldo, incremento do contador, débito e
     * registro da transação) roda dentro de uma transação com trava de linha
     * no fornecedor (lockForUpdate), garantindo que requisições simultâneas
     * não gastem além do saldo disponível nem deixem o saldo negativo.
     */
    protected function charge(float $cost, string $counterColumn, string $type, string $description): bool
    {
        if (! $this->supplier_id) {
            return false;
        }

        return DB::transaction(function () use ($cost, $counterColumn, $type, $description) {
            $supplier = Supplier::whereKey($this->supplier_id)->lockForUpdate()->first();

            if (! $supplier) {
                return false;
            }

            // Sem saldo suficiente: pausa a campanha e não cobra nada.
            if ((float) $supplier->credit_balance < $cost) {
                if ($this->is_active) {
                    $this->forceFill(['is_active' => false])->save();
                }

                return false;
            }

            // Incrementa o contador físico (views/clicks) de forma atômica.
            $this->increment($counterColumn);

            // Debita o saldo — nunca fica negativo por causa da checagem acima.
            $supplier->decrement('credit_balance', $cost);

            SupplierCreditTransaction::create([
                'supplier_id' => $supplier->id,
                'type' => $type,
                'amount' => $cost,
                'description' => $description,
                'advertisement_id' => $this->id,
            ]);

            return true;
        });
    }
}
