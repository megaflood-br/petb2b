<?php

namespace App\Livewire\Admin;

use App\Models\Magazine;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ManageMagazines extends Component
{
    use WithFileUploads, WithPagination;

    // Propriedades do formulário
    public $title, $issue_period, $pdf, $cover, $editingMagazineId;
    public $showForm = false;

    #[Layout('layouts.admin')]
    public function render()
    {
        return view('livewire.admin.manage-magazines', [
            'magazines' => Magazine::latest()->paginate(10)
        ]);
    }

    // Função para abrir o formulário de edição
    public function edit($id)
    {
        $mag = Magazine::findOrFail($id);
        $this->editingMagazineId = $id;
        $this->title = $mag->title;
        $this->issue_period = $mag->issue_period;
        $this->showForm = true;
    }

    // Função que estava faltando e causava o erro
    public function delete($id)
    {
        $mag = Magazine::findOrFail($id);

        // Remove os arquivos físicos do seu PC antes de apagar do banco
        if ($mag->pdf_path) Storage::disk('public')->delete($mag->pdf_path);
        if ($mag->cover_path) Storage::disk('public')->delete($mag->cover_path);

        $mag->delete();
        session()->flash('message', 'Revista excluída com sucesso!');
    }

    public function save()
    {
        $this->validate([
            'title' => 'required|min:3',
            'issue_period' => 'required',
            'pdf' => $this->editingMagazineId ? 'nullable|mimes:pdf|max:51200' : 'required|mimes:pdf|max:51200',
            'cover' => $this->editingMagazineId ? 'nullable|image|max:2048' : 'required|image|max:2048',
        ]);

        $data = [
            'title' => $this->title,
            'issue_period' => $this->issue_period,
            'slug' => Str::slug($this->issue_period), // Gera a URL amigável
            'is_active' => true,
        ];

        // Upload de arquivos
        if ($this->pdf) {
            $data['pdf_path'] = $this->pdf->store('magazines/pdfs', 'public');
        }

        if ($this->cover) {
            $data['cover_path'] = $this->cover->store('magazines/covers', 'public');
        }

        if ($this->editingMagazineId) {
            Magazine::find($this->editingMagazineId)->update($data);
            session()->flash('message', 'Revista atualizada!');
        } else {
            Magazine::create($data);
            session()->flash('message', 'Revista publicada com sucesso!');
        }

        $this->reset(['title', 'issue_period', 'pdf', 'cover', 'editingMagazineId', 'showForm']);
    }
}
