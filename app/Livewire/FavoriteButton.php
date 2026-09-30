<?php

namespace App\Livewire;

use App\Support\FavoriteCatalog;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

class FavoriteButton extends Component
{
    public string $type;

    public int|string $favoritableId;

    public bool $favorited = false;

    public function mount(Model $favoritable): void
    {
        abort_unless(FavoriteCatalog::supports($favoritable), 404);

        $this->type = $favoritable->getMorphClass();
        $this->favoritableId = $favoritable->getKey();
        $this->favorited = auth()->user()?->hasFavorited($favoritable) ?? false;
    }

    public function toggle(): void
    {
        if (! auth()->check()) {
            $this->redirect(route('login'));

            return;
        }

        $model = $this->resolve();
        if (! $model) {
            return;
        }

        $this->favorited = auth()->user()->toggleFavorite($model);
        $this->dispatch('favorite-toggled', type: $this->type, id: $this->favoritableId, favorited: $this->favorited);
    }

    public function render()
    {
        return view('livewire.favorite-button');
    }

    private function resolve(): ?Model
    {
        if (! in_array($this->type, FavoriteCatalog::types(), true)) {
            return null;
        }

        /** @var class-string<Model> $type */
        $type = $this->type;

        return $type::query()->find($this->favoritableId);
    }
}
