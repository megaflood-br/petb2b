<?php

namespace App\Support\Reports;

/**
 * Catálogo de relatórios do admin. Novos tipos entram aqui; só os
 * `enabled` ficam navegáveis. Os demais aparecem como "Em breve".
 */
class ReportCatalog
{
    /**
     * @return list<array{key: string, label: string, description: string, enabled: bool}>
     */
    public static function all(): array
    {
        return [
            [
                'key' => 'anuncios',
                'label' => 'Anúncios',
                'description' => 'Performance das campanhas: views, cliques, CTR e gastos debitados.',
                'enabled' => true,
            ],
            [
                'key' => 'leads',
                'label' => 'Leads',
                'description' => 'Contatos gerados no guia de fornecedores.',
                'enabled' => false,
            ],
            [
                'key' => 'fornecedores',
                'label' => 'Fornecedores',
                'description' => 'Cadastros, aprovações e atividade no guia.',
                'enabled' => false,
            ],
        ];
    }

    /**
     * @return array{key: string, label: string, description: string, enabled: bool}|null
     */
    public static function find(string $key): ?array
    {
        foreach (static::all() as $report) {
            if ($report['key'] === $key) {
                return $report;
            }
        }

        return null;
    }

    public static function enabled(string $key): bool
    {
        $report = static::find($key);

        return (bool) ($report['enabled'] ?? false);
    }
}
