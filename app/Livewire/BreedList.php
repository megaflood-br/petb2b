<?php

namespace App\Livewire;

use App\Models\Breed;
use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class BreedList extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $species = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSpecies(): void
    {
        $this->resetPage();
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $usingPosts = $this->usingPosts();

        return view('livewire.breed-list', [
            'breeds' => $usingPosts ? $this->postsQuery() : $this->breedsQuery(),
            'speciesList' => Breed::SPECIES,
            'usingPosts' => $usingPosts,
        ]);
    }

    /**
     * Sem raças oficiais no guia, lista os artigos importados da categoria
     * "racas" — o mesmo conteúdo que a home já mostra em "Tudo sobre Raças".
     */
    private function usingPosts(): bool
    {
        return ! Breed::query()->where('is_active', true)->exists();
    }

    private function breedsQuery(): LengthAwarePaginator
    {
        return Breed::query()
            ->where('is_active', true)
            ->when($this->search !== '', function (Builder $query): void {
                $query->where(function (Builder $inner): void {
                    $inner->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->species !== '', fn (Builder $query) => $query->where('species', $this->species))
            ->orderBy('name')
            ->paginate(12);
    }

    private function postsQuery(): LengthAwarePaginator
    {
        return Post::query()
            ->with('blogCategories')
            ->where('is_active', true)
            ->whereHas('blogCategories', fn (Builder $query) => $query->where('slug', 'racas'))
            ->when($this->search !== '', function (Builder $query): void {
                $query->where(function (Builder $inner): void {
                    $inner->where('title', 'like', '%'.$this->search.'%')
                        ->orWhere('content', 'like', '%'.$this->search.'%');
                });
            })
            ->latest()
            ->paginate(12);
    }
}
