<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Supplier;
use App\Models\Event;
use App\Models\Classified;
use App\Models\Post;
use Livewire\Attributes\Layout; // Importação essencial para definir o layout do portal

class GeneralSearch extends Component
{
    public $search = '';
    public $term = '';

    /**
     * Captura os parâmetros da URL na primeira carga (?search=termo)
     */
    public function mount()
    {
        $this->search = request('search', '');
        $this->term = $this->search;
    }

    /**
     * Monitora atualizações em tempo real no input de busca
     */
    public function updatedSearch()
    {
        $this->term = $this->search;
    }

    // CORREÇÃO CRÍTICA: Força o Livewire a renderizar dentro do seu layout real (resources/views/layouts/app.blade.php)
    #[Layout('layouts.app')]
    public function render()
    {
        // 1. Busca em Fornecedores Ativos
        $suppliers = Supplier::where('is_active', true)
            ->where('is_approved', true)
            ->search($this->term, ['name', 'description'])
            ->take(6)->get();

        // 2. Busca em Eventos Futuros Ativos
        $events = Event::where('is_active', true)
            ->where('start_date', '>=', now())
            ->where(function($query) {
                $query->where('title', 'like', '%' . $this->term . '%')
                      ->orWhere('description', 'like', '%' . $this->term . '%')
                      ->orWhere('city', 'like', '%' . $this->term . '%');
            })
            ->orderBy('start_date', 'asc')
            ->take(6)->get();

        // 3. Busca em Classificados Oportunidades Ativas
        $classifieds = Classified::where('is_active', true)
            ->where(function($query) {
                $query->where('title', 'like', '%' . $this->term . '%')
                      ->orWhere('description', 'like', '%' . $this->term . '%');
            })
            ->latest()->take(6)->get();

        // 4. Busca em Notícias
        $posts = Post::where('is_active', true)
            ->search($this->term, ['title', 'content'])
            ->latest()
            ->take(6)->get();

        return view('livewire.general-search', [
            'suppliers'   => $suppliers,
            'events'      => $events,
            'classifieds' => $classifieds,
            'posts'       => $posts,
        ]);
    }
}
