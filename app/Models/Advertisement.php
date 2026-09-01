<?php

namespace App\Models;

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

    // Força o Laravel a entender que 'views' e 'clicks' são inteiros sempre
    protected $casts = [
        'views' => 'integer',
        'clicks' => 'integer',
        'is_active' => 'boolean',
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
            'banner_mobile_footer' => 'Banner Fixo Mobile (Rodapé Celular - 320x50)',
        ];
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Registra uma visualização na coluna física 'views'
     */
    public function trackImpression()
    {
        $supplier = $this->supplier;

        if (!$supplier || $supplier->credit_balance < $this->cost_per_impression) {
            $this->pauseAdDueToNoCredits();
            return;
        }

        DB::transaction(function () use ($supplier) {
            // Incrementa na memória do PHP e no banco simultaneamente
            $this->views = ($this->views ?? 0) + 1;
            $this->save();

            // Deduz o saldo do fornecedor
            $supplier->decrement('credit_balance', $this->cost_per_impression);

            // Grava o histórico de consumo
            SupplierCreditTransaction::create([
                'supplier_id' => $supplier->id,
                'type' => 'expense_impression',
                'amount' => $this->cost_per_impression,
                'description' => "Visualização do banner: #{$this->id} - {$this->title}",
                'advertisement_id' => $this->id
            ]);
        });
    }

    /**
     * Registra o clique na coluna física 'clicks'
     */
    public function trackClick()
    {
        $supplier = $this->supplier;

        if (!$supplier || $supplier->credit_balance < $this->cost_per_click) {
            $this->pauseAdDueToNoCredits();
            return;
        }

        DB::transaction(function () use ($supplier) {
            // Incrementa na memória do PHP e no banco simultaneamente
            $this->clicks = ($this->clicks ?? 0) + 1;
            $this->save();

            // Deduz o custo do clique
            $supplier->decrement('credit_balance', $this->cost_per_click);

            // Grava o histórico
            SupplierCreditTransaction::create([
                'supplier_id' => $supplier->id,
                'type' => 'expense_click',
                'amount' => $this->cost_per_click,
                'description' => "Clique no anúncio: {$this->title}",
                'advertisement_id' => $this->id
            ]);
        });
    }

    private function pauseAdDueToNoCredits()
    {
        $this->update([
            'is_active' => false
        ]);
    }
}
