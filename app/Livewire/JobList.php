<?php

namespace App\Livewire;

use App\Livewire\Concerns\WithInfiniteScroll;
use App\Models\JobPosting;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class JobList extends Component
{
    use WithPagination;
    use WithInfiniteScroll;

    public $search = '';
    public $type = '';
    public $state = '';

    public function updatingSearch()
    {
        $this->resetInfiniteScroll();
    }

    public function updatingType()
    {
        $this->resetInfiniteScroll();
    }

    public function updatingState()
    {
        $this->resetInfiniteScroll();
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $states = JobPosting::query()
            ->where('is_active', true)
            ->whereNotNull('state')
            ->distinct()
            ->orderBy('state')
            ->pluck('state');

        $jobs = JobPosting::query()
            ->where('is_active', true)
            ->whereHas('supplier', fn ($q) => $q->where('is_active', true))
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->type, fn ($q) => $q->where('type', $this->type))
            ->when($this->state, fn ($q) => $q->where('state', $this->state))
            ->with('supplier')
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.job-list', [
            'jobs' => $jobs,
            'states' => $states,
            'types' => JobPosting::TYPES,
        ]);
    }
}
