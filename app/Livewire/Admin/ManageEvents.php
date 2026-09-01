<?php

namespace App\Livewire\Admin;

use App\Models\Event;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ManageEvents extends Component
{
    use WithFileUploads;

    public $title, $start_date, $end_date, $location, $city, $state, $description, $external_link, $image, $existingImage;
    public $editingEventId = null;
    public $showForm = false;

    public function toggleForm() {
        $this->reset(['title', 'start_date', 'end_date', 'location', 'city', 'state', 'description', 'external_link', 'image', 'editingEventId', 'existingImage']);
        $this->showForm = !$this->showForm;
    }

    public function save() {
        $this->validate([
            'title' => 'required|min:3',
            'start_date' => 'required|date',
            'location' => 'required',
            'city' => 'required',
            'state' => 'required',
            'image' => 'nullable|image|max:2048', // Validação para imagem
        ]);

        $data = [
            'title' => $this->title,
            'slug' => Str::slug($this->title) . '-' . rand(10, 99),
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'location' => $this->location,
            'city' => $this->city,
            'state' => $this->state,
            'description' => $this->description,
            'external_link' => $this->external_link,
            'is_active' => true,
        ];

        if ($this->image) {
            // Remove imagem antiga se estiver editando
            if ($this->editingEventId && $this->existingImage) {
                Storage::disk('public')->delete($this->existingImage);
            }
            $data['image'] = $this->image->store('events', 'public');
        }

        if ($this->editingEventId) {
            Event::find($this->editingEventId)->update($data);
            session()->flash('message', 'Evento atualizado com sucesso!');
        } else {
            Event::create($data);
            session()->flash('message', 'Evento cadastrado com sucesso!');
        }

        $this->toggleForm();
    }

    public function edit($id) {
        $event = Event::findOrFail($id);
        $this->editingEventId = $event->id;
        $this->title = $event->title;
        $this->start_date = $event->start_date;
        $this->end_date = $event->end_date;
        $this->location = $event->location;
        $this->city = $event->city;
        $this->state = $event->state;
        $this->description = $event->description;
        $this->external_link = $event->external_link;
        $this->existingImage = $event->image;
        $this->showForm = true;
    }

    public function delete($id) {
        $event = Event::find($id);
        if($event->image) Storage::disk('public')->delete($event->image);
        $event->delete();
        session()->flash('message', 'Evento excluído!');
    }

    public function render() {
        return view('livewire.admin.manage-events', [
            'events' => Event::latest()->get()
        ]);
    }
}
