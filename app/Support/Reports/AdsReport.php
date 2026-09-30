<?php

namespace App\Support\Reports;

use App\Models\Advertisement;
use App\Models\SupplierCreditTransaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdsReport
{
    public const GROUPS = [
        'campaign' => 'Por campanha',
        'supplier' => 'Por empresa',
        'position' => 'Por posição',
    ];

    public function __construct(
        public readonly string $search = '',
        public readonly ?int $supplierId = null,
        public readonly string $position = '',
        public readonly string $status = '',
        public readonly string $courtesy = '',
        public readonly string $dateFrom = '',
        public readonly string $dateTo = '',
        public readonly string $groupBy = 'campaign',
        public readonly string $sortBy = 'views',
        public readonly string $sortDir = 'desc',
    ) {}

    public static function fromFilters(
        string $search = '',
        string $supplierId = '',
        string $position = '',
        string $status = '',
        string $courtesy = '',
        string $dateFrom = '',
        string $dateTo = '',
        string $groupBy = 'campaign',
        string $sortBy = 'views',
        string $sortDir = 'desc',
    ): self {
        $groupBy = array_key_exists($groupBy, self::GROUPS) ? $groupBy : 'campaign';
        $sortDir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

        return new self(
            search: trim($search),
            supplierId: is_numeric($supplierId) ? (int) $supplierId : null,
            position: $position,
            status: $status,
            courtesy: $courtesy,
            dateFrom: $dateFrom,
            dateTo: $dateTo,
            groupBy: $groupBy,
            sortBy: $sortBy,
            sortDir: $sortDir,
        );
    }

    public function adsQuery(): Builder
    {
        $positions = array_keys(Advertisement::getPositions());

        return Advertisement::query()
            ->with('supplier')
            ->when($this->search !== '', function (Builder $query) {
                $term = '%'.$this->search.'%';
                $query->where(function (Builder $inner) use ($term) {
                    $inner->where('title', 'like', $term)
                        ->orWhereHas('supplier', fn (Builder $supplier) => $supplier->where('name', 'like', $term));
                });
            })
            ->when($this->supplierId, fn (Builder $query) => $query->where('supplier_id', $this->supplierId))
            ->when($this->position !== '' && in_array($this->position, $positions, true), fn (Builder $query) => $query->where('position', $this->position))
            ->when($this->status === 'active', fn (Builder $query) => $query->where('is_active', true))
            ->when($this->status === 'paused', fn (Builder $query) => $query->where('is_active', false))
            ->when($this->courtesy === 'yes', fn (Builder $query) => $query->where('skip_credits', true))
            ->when($this->courtesy === 'no', fn (Builder $query) => $query->where('skip_credits', false))
            ->when($this->dateFrom !== '', fn (Builder $query) => $query->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn (Builder $query) => $query->whereDate('created_at', '<=', $this->dateTo));
    }

    /**
     * @return array{campaigns: int, active: int, courtesy: int, views: int, clicks: int, ctr: float, billed: float}
     */
    public function summary(): array
    {
        $row = (clone $this->adsQuery())
            ->toBase()
            ->selectRaw('COUNT(*) as campaigns')
            ->selectRaw('COALESCE(SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END), 0) as active')
            ->selectRaw('COALESCE(SUM(CASE WHEN skip_credits = 1 THEN 1 ELSE 0 END), 0) as courtesy')
            ->selectRaw('COALESCE(SUM(views), 0) as views')
            ->selectRaw('COALESCE(SUM(clicks), 0) as clicks')
            ->first();

        $views = (int) ($row->views ?? 0);
        $clicks = (int) ($row->clicks ?? 0);

        return [
            'campaigns' => (int) ($row->campaigns ?? 0),
            'active' => (int) ($row->active ?? 0),
            'courtesy' => (int) ($row->courtesy ?? 0),
            'views' => $views,
            'clicks' => $clicks,
            'ctr' => self::ctr($clicks, $views),
            'billed' => $this->billedTotal(),
        ];
    }

    /**
     * @return Collection<int, object>
     */
    public function rows(): Collection
    {
        return match ($this->groupBy) {
            'supplier' => $this->supplierRows(),
            'position' => $this->positionRows(),
            default => $this->campaignRows(),
        };
    }

    public function csvFilename(): string
    {
        return 'relatorio-anuncios-'.now()->format('Y-m-d').'.csv';
    }

    public function downloadCsv(): StreamedResponse
    {
        $headers = $this->csvHeaders();
        $rows = $this->csvBody();
        $filename = $this->csvFilename();

        return response()->streamDownload(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $headers, ';');
            foreach ($rows as $row) {
                fputcsv($out, $row, ';');
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public static function ctr(int $clicks, int $views): float
    {
        if ($views <= 0) {
            return 0.0;
        }

        return round(($clicks / $views) * 100, 2);
    }

    public static function positionLabel(string $position): string
    {
        return Advertisement::getPositions()[$position] ?? str_replace('_', ' ', $position);
    }

    /**
     * @return Collection<int, Advertisement>
     */
    private function campaignRows(): Collection
    {
        $sort = in_array($this->sortBy, ['views', 'clicks', 'title', 'created_at'], true)
            ? $this->sortBy
            : 'views';

        $ads = $this->adsQuery()
            ->orderBy($sort, $this->sortDir)
            ->orderByDesc('id')
            ->get();

        $billed = $this->billedByAdvertisement($ads->pluck('id'));

        return $ads->map(function (Advertisement $ad) use ($billed) {
            $views = (int) $ad->views;
            $clicks = (int) $ad->clicks;

            return (object) [
                'id' => $ad->id,
                'supplier' => $ad->supplier->name ?? 'Fornecedor desconhecido',
                'title' => $ad->title,
                'position' => $ad->position,
                'position_label' => self::positionLabel((string) $ad->position),
                'is_active' => (bool) $ad->is_active,
                'skip_credits' => (bool) $ad->skip_credits,
                'views' => $views,
                'clicks' => $clicks,
                'ctr' => self::ctr($clicks, $views),
                'cost_per_click' => (float) $ad->cost_per_click,
                'cost_per_impression' => (float) $ad->cost_per_impression,
                'billed' => (float) ($billed[$ad->id] ?? 0),
                'created_at' => $ad->created_at,
            ];
        });
    }

    /**
     * @return Collection<int, object>
     */
    private function supplierRows(): Collection
    {
        $ads = $this->adsQuery()->get();
        $billed = $this->billedByAdvertisement($ads->pluck('id'));

        $grouped = $ads->groupBy('supplier_id')->map(function (Collection $group) use ($billed) {
            $supplier = $group->first()?->supplier;
            $views = (int) $group->sum('views');
            $clicks = (int) $group->sum('clicks');

            return (object) [
                'supplier' => $supplier->name ?? 'Fornecedor desconhecido',
                'campaigns' => $group->count(),
                'views' => $views,
                'clicks' => $clicks,
                'ctr' => self::ctr($clicks, $views),
                'billed' => (float) $group->sum(fn (Advertisement $ad) => (float) ($billed[$ad->id] ?? 0)),
            ];
        })->values();

        return $this->sortGrouped($grouped, ['views', 'clicks', 'campaigns', 'supplier']);
    }

    /**
     * @return Collection<int, object>
     */
    private function positionRows(): Collection
    {
        $ads = $this->adsQuery()->get();
        $billed = $this->billedByAdvertisement($ads->pluck('id'));

        $grouped = $ads->groupBy('position')->map(function (Collection $group, string $position) use ($billed) {
            $views = (int) $group->sum('views');
            $clicks = (int) $group->sum('clicks');

            return (object) [
                'position' => $position,
                'position_label' => self::positionLabel($position),
                'campaigns' => $group->count(),
                'views' => $views,
                'clicks' => $clicks,
                'ctr' => self::ctr($clicks, $views),
                'billed' => (float) $group->sum(fn (Advertisement $ad) => (float) ($billed[$ad->id] ?? 0)),
            ];
        })->values();

        return $this->sortGrouped($grouped, ['views', 'clicks', 'campaigns', 'position_label']);
    }

    /**
     * @param  list<string>  $allowed
     * @return Collection<int, object>
     */
    private function sortGrouped(Collection $rows, array $allowed): Collection
    {
        $sort = in_array($this->sortBy, $allowed, true) ? $this->sortBy : 'views';

        return $rows->sortBy(
            fn (object $row) => $row->{$sort},
            SORT_REGULAR,
            $this->sortDir === 'desc'
        )->values();
    }

    /**
     * @param  Collection<int, int|string>  $adIds
     * @return Collection<int|string, float>
     */
    private function billedByAdvertisement(Collection $adIds): Collection
    {
        if ($adIds->isEmpty()) {
            return collect();
        }

        return $this->expenseQuery()
            ->whereIn('advertisement_id', $adIds->all())
            ->selectRaw('advertisement_id, SUM(amount) as billed')
            ->groupBy('advertisement_id')
            ->pluck('billed', 'advertisement_id')
            ->map(fn ($value) => (float) $value);
    }

    private function billedTotal(): float
    {
        $ids = (clone $this->adsQuery())->pluck('id');

        if ($ids->isEmpty()) {
            return 0.0;
        }

        return (float) $this->expenseQuery()
            ->whereIn('advertisement_id', $ids->all())
            ->sum('amount');
    }

    private function expenseQuery(): Builder
    {
        return SupplierCreditTransaction::query()
            ->whereIn('type', ['expense_click', 'expense_impression'])
            ->when($this->dateFrom !== '', fn (Builder $query) => $query->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn (Builder $query) => $query->whereDate('created_at', '<=', $this->dateTo));
    }

    /**
     * @return list<string>
     */
    private function csvHeaders(): array
    {
        return match ($this->groupBy) {
            'supplier' => ['Empresa', 'Campanhas', 'Views', 'Cliques', 'CTR %', 'Gasto debitado (R$)'],
            'position' => ['Posição', 'Campanhas', 'Views', 'Cliques', 'CTR %', 'Gasto debitado (R$)'],
            default => [
                'Empresa',
                'Campanha',
                'Posição',
                'Status',
                'Cortesia',
                'Views',
                'Cliques',
                'CTR %',
                'Custo clique (R$)',
                'Custo view (R$)',
                'Gasto debitado (R$)',
                'Cadastro',
            ],
        };
    }

    /**
     * @return list<list<string>>
     */
    private function csvBody(): array
    {
        return $this->rows()->map(function (object $row) {
            $billed = number_format((float) $row->billed, 4, ',', '.');
            $ctr = number_format((float) $row->ctr, 2, ',', '.');

            return match ($this->groupBy) {
                'supplier' => [
                    (string) $row->supplier,
                    (string) $row->campaigns,
                    (string) $row->views,
                    (string) $row->clicks,
                    $ctr,
                    $billed,
                ],
                'position' => [
                    (string) $row->position_label,
                    (string) $row->campaigns,
                    (string) $row->views,
                    (string) $row->clicks,
                    $ctr,
                    $billed,
                ],
                default => [
                    (string) $row->supplier,
                    (string) $row->title,
                    (string) $row->position_label,
                    $row->is_active ? 'Ativo' : 'Pausado',
                    $row->skip_credits ? 'Sim' : 'Não',
                    (string) $row->views,
                    (string) $row->clicks,
                    $ctr,
                    number_format((float) $row->cost_per_click, 4, ',', '.'),
                    number_format((float) $row->cost_per_impression, 4, ',', '.'),
                    $billed,
                    optional($row->created_at)?->format('d/m/Y H:i') ?? '',
                ],
            };
        })->all();
    }
}
