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
     * Retorna a lista unificada de posições válidas de anúncios do sistema
     * Centraliza a chamada para o formulário admin e validações de requests
     */
    public static function getPositions()
    {
        return [
            'banner_topo' => 'Topo do Site (Geral)',
            'sidebar_guia' => 'Barra Lateral (Guia de Fornecedores)',
            'meio_blog' => 'Meio do Blog (Entre os Artigos)',
            'post_top' => 'Topo do Artigo (Interno)',
            'post_footer' => 'Rodapé do Artigo (Interno)',
            'banner_mobile_footer' => 'Banner Fixo Mobile (Rodapé Celular - 320x50)',
        ];
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
