<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Event;
use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Support\Str;

class EventList extends Component
{
    public $selectedEventSlug = null;

    /**
     * Captura o slug diretamente da URL quando a página é carregada pela primeira vez
     */
    public function mount($slug = null)
    {
        if ($slug) {
            $event = Event::where('slug', $slug)->where('is_active', true)->first();
            if ($event) {
                $this->selectedEventSlug = $event->slug;
            } else {
                abort(404);
            }
        }
    }

    /**
     * Disparado ao clicar no card. Altera o estado e atualiza a URL do navegador nativamente
     */
    public function selectEvent($slug)
    {
        $this->selectedEventSlug = $slug;

        // Empurra a nova URL limpa para o histórico do navegador sem recarregar a tela
        $this->js("history.pushState({}, '', '/feiras-pet-2026/{$slug}')");
    }

    /**
     * Retorna para a listagem geral e limpa a URL para o padrão limpo
     */
    public function clearSelection()
    {
        $this->selectedEventSlug = null;
        $this->js("history.pushState({}, '', '/feiras-pet-2026')");
    }

    public function render()
    {
        $selectedEvent = null;

        if ($this->selectedEventSlug) {
            $selectedEvent = Event::where('slug', $this->selectedEventSlug)
                ->where('is_active', true)
                ->firstOrFail();

            // INJEÇÃO COMPATÍVEL COM YOAST NO DETALHE DO EVENTO
            SEOTools::setTitle($selectedEvent->title . ' | Agenda Pet 2026');
            SEOTools::setDescription(Str::limit(strip_tags($selectedEvent->description), 150));
            if ($selectedEvent->image) {
                SEOTools::opengraph()->addImage(asset('storage/' . $selectedEvent->image));
            }
        } else {
            // SEO Padrão da Listagem de Eventos
            SEOTools::setTitle('Agenda Pet 2026 | Feiras e Congressos do Setor');
            SEOTools::setDescription('Confira o calendário completo com as principais feiras, congressos e eventos do mercado pet.');
        }

        // Busca a lista de eventos futuros ativos
        $events = Event::where('is_active', true)
            ->where('start_date', '>=', now())
            ->orderBy('start_date', 'asc')
            ->get();

        return view('livewire.event-list', compact('events', 'selectedEvent'))
            ->layout('layouts.app');
    }
}
