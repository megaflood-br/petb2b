<?php

namespace App\Livewire;

use App\Models\Favorite;
use App\Support\FavoriteCatalog;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

class FavoritesIndex extends Component
{
    #[Url(as: 'pasta')]
    public string $folder = '';

    public function openFolder(string $folder): void
    {
        $this->folder = array_key_exists($folder, FavoriteCatalog::folders()) ? $folder : '';
    }

    public function closeFolder(): void
    {
        $this->folder = '';
    }

    public function remove(int $id): void
    {
        Favorite::query()
            ->where('user_id', auth()->id())
            ->whereKey($id)
            ->delete();
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $counts = Favorite::query()
            ->where('user_id', auth()->id())
            ->selectRaw('folder, count(*) as total')
            ->groupBy('folder')
            ->pluck('total', 'folder');

        $items = collect();
        if ($this->folder !== '') {
            $items = Favorite::query()
                ->with('favoritable')
                ->where('user_id', auth()->id())
                ->where('folder', $this->folder)
                ->latest()
                ->get()
                ->filter(fn (Favorite $favorite) => $favorite->favoritable !== null);
        }

        return view('livewire.favorites-index', [
            'folders' => FavoriteCatalog::folders(),
            'counts' => $counts,
            'items' => $items,
        ]);
    }
}
