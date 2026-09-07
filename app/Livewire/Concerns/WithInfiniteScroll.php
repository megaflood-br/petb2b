<?php

namespace App\Livewire\Concerns;

trait WithInfiniteScroll
{
    public int $perPage = 12;

    public function mountWithInfiniteScroll(): void
    {
        $this->perPage = $this->infiniteIncrement();
    }

    public function loadMore(): void
    {
        $this->perPage += $this->infiniteIncrement();
    }

    protected function resetInfiniteScroll(): void
    {
        $this->perPage = $this->infiniteIncrement();

        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }

    protected function infiniteIncrement(): int
    {
        return 12;
    }
}
