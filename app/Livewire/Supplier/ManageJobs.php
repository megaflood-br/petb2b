<?php

namespace App\Livewire\Supplier;

use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ManageJobs extends Component
{
    public $supplier;

    public $title, $description, $type = 'CLT', $city, $state, $salary, $how_to_apply;
    public bool $is_active = true;

    public $showForm = false;
    public $editingId = null;

    // Painel de candidaturas da vaga selecionada.
    public $selectedJobId = null;

    public function mount()
    {
        $this->supplier = Supplier::where('user_id', Auth::id())->first();

        if (! $this->supplier) {
            return $this->redirect(route('supplier.dashboard'), navigate: true);
        }

        // Pré-preenche localização com a da empresa.
        $this->city = $this->supplier->city;
        $this->state = $this->supplier->state;
    }

    protected function rules(): array
    {
        return [
            'title' => 'required|min:5|max:120',
            'description' => 'required|min:20',
            'type' => 'required|in:' . implode(',', JobPosting::TYPES),
            'city' => 'nullable|max:80',
            'state' => 'nullable|max:40',
            'salary' => 'nullable|max:60',
            'how_to_apply' => 'required|min:5|max:255',
        ];
    }

    public function toggleForm()
    {
        $this->reset(['title', 'description', 'salary', 'how_to_apply', 'editingId']);
        $this->type = 'CLT';
        $this->is_active = true;
        $this->city = $this->supplier->city;
        $this->state = $this->supplier->state;
        $this->showForm = ! $this->showForm;
    }

    public function edit($id)
    {
        $job = JobPosting::where('supplier_id', $this->supplier->id)->findOrFail($id);

        $this->editingId = $job->id;
        $this->title = $job->title;
        $this->description = $job->description;
        $this->type = $job->type;
        $this->city = $job->city;
        $this->state = $job->state;
        $this->salary = $job->salary;
        $this->how_to_apply = $job->how_to_apply;
        $this->is_active = $job->is_active;
        $this->showForm = true;
    }

    public function toggleStatus($id)
    {
        $job = JobPosting::where('supplier_id', $this->supplier->id)->findOrFail($id);
        $job->update(['is_active' => ! $job->is_active]);
        session()->flash('message', 'Status da vaga atualizado.');
    }

    public function delete($id)
    {
        JobPosting::where('supplier_id', $this->supplier->id)->findOrFail($id)->delete();
        session()->flash('message', 'Vaga removida com sucesso.');
    }

    public function save()
    {
        $data = $this->validate();

        if ($this->editingId) {
            $job = JobPosting::where('supplier_id', $this->supplier->id)->findOrFail($this->editingId);
            $job->update($data);
            session()->flash('message', 'Vaga atualizada!');
        } else {
            $data['supplier_id'] = $this->supplier->id;
            JobPosting::create($data);
            session()->flash('message', 'Vaga publicada!');
        }

        $this->reset(['title', 'description', 'salary', 'how_to_apply', 'editingId', 'showForm']);
        $this->type = 'CLT';
        $this->is_active = true;
    }

    public function viewApplications($id)
    {
        // Garante que a vaga é do fornecedor logado.
        JobPosting::where('supplier_id', $this->supplier->id)->findOrFail($id);
        $this->selectedJobId = $id;
    }

    public function closeApplications()
    {
        $this->selectedJobId = null;
    }

    public function render()
    {
        $jobs = JobPosting::where('supplier_id', $this->supplier->id)
            ->withCount('applications')
            ->latest()
            ->get();

        $applications = $this->selectedJobId
            ? JobApplication::where('job_posting_id', $this->selectedJobId)->latest()->get()
            : collect();

        return view('livewire.supplier.manage-jobs', [
            'jobs' => $jobs,
            'types' => JobPosting::TYPES,
            'applications' => $applications,
        ])->layout('layouts.supplier');
    }
}
