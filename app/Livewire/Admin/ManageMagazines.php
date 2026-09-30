<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\WithInfiniteScroll;
use App\Models\Magazine;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ManageMagazines extends Component
{
    use WithFileUploads, WithPagination, WithInfiniteScroll;

    protected function infiniteIncrement(): int
    {
        return 10;
    }

    // Propriedades do formulário
    public $title, $issue_period, $pdf, $cover, $editingMagazineId;
    public string $created_at = '';
    public $showForm = false;

    public function toggleForm(): void
    {
        $this->reset(['title', 'issue_period', 'pdf', 'cover', 'editingMagazineId']);
        $this->created_at = now()->format('Y-m-d\TH:i');
        $this->showForm = ! $this->showForm;
        $this->resetErrorBag();
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        return view('livewire.admin.manage-magazines', [
            'magazines' => Magazine::latest()->paginate($this->perPage)
        ]);
    }

    // Função para abrir o formulário de edição
    public function edit($id)
    {
        $mag = Magazine::findOrFail($id);
        $this->editingMagazineId = $id;
        $this->title = $mag->title;
        $this->issue_period = $mag->issue_period;
        $this->created_at = $mag->created_at?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i');
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
            'created_at' => 'nullable|date',
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

        $magazine = $this->editingMagazineId
            ? Magazine::findOrFail($this->editingMagazineId)
            : new Magazine();
        $magazine->fill($data);
        $magazine->created_at = $this->created_at !== ''
            ? Carbon::parse($this->created_at)
            : ($magazine->created_at ?? now());
        $magazine->save();

        session()->flash('message', $this->editingMagazineId ? 'Revista atualizada!' : 'Revista publicada com sucesso!');

        $this->reset(['title', 'issue_period', 'pdf', 'cover', 'editingMagazineId', 'created_at', 'showForm']);
    }
}
