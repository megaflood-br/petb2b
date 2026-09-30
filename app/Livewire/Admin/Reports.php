<?php

namespace App\Livewire\Admin;

use App\Models\Advertisement;
use App\Models\Supplier;
use App\Support\Reports\AdsReport;
use App\Support\Reports\ReportCatalog;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Reports extends Component
{
    public string $type = '';

    public string $search = '';

    public string $supplierId = '';

    public string $position = '';

    public string $status = '';

    public string $courtesy = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public string $groupBy = 'campaign';

    public string $sortBy = 'views';

    public string $sortDir = 'desc';

    public function mount(?string $type = null): void
    {
        abort_unless(auth()->user()?->can('access-admin'), 403);

        $this->type = (string) $type;

        if ($this->type === '') {
            return;
        }

        abort_unless(ReportCatalog::enabled($this->type), 404);
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'desc' ? 'asc' : 'desc';

            return;
        }

        $this->sortBy = $column;
        $this->sortDir = 'desc';
    }

    public function exportCsv(): StreamedResponse
    {
        abort_unless($this->type === 'anuncios', 404);

        return $this->adsReport()->downloadCsv();
    }

    public function render()
    {
        $catalog = ReportCatalog::all();
        $current = $this->type !== '' ? ReportCatalog::find($this->type) : null;

        $payload = [
            'catalog' => $catalog,
            'current' => $current,
        ];

        if ($this->type === 'anuncios') {
            $report = $this->adsReport();
            $payload['summary'] = $report->summary();
            $payload['rows'] = $report->rows();
            $payload['suppliers'] = Supplier::orderBy('name')->get(['id', 'name']);
            $payload['positions'] = Advertisement::getPositions();
            $payload['groups'] = AdsReport::GROUPS;
        }

        return view('livewire.admin.reports', $payload)->layout('layouts.admin');
    }

    private function adsReport(): AdsReport
    {
        return AdsReport::fromFilters(
            search: $this->search,
            supplierId: $this->supplierId,
            position: $this->position,
            status: $this->status,
            courtesy: $this->courtesy,
            dateFrom: $this->dateFrom,
            dateTo: $this->dateTo,
            groupBy: $this->groupBy,
            sortBy: $this->sortBy,
            sortDir: $this->sortDir,
        );
    }
}
