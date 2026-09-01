<?php

namespace App\Livewire;

use App\Models\JobApplication;
use App\Models\JobPosting;
use Livewire\Component;
use Livewire\WithFileUploads;

class JobApply extends Component
{
    use WithFileUploads;

    public int $jobId;
    public $name, $email, $phone, $message, $resume;
    public bool $submitted = false;

    public function mount(int $jobId)
    {
        $this->jobId = $jobId;
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|min:3|max:120',
            'email' => 'required|email|max:150',
            'phone' => 'nullable|max:30',
            'message' => 'nullable|max:2000',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:4096',
        ];
    }

    public function apply()
    {
        $data = $this->validate();

        // Só aceita candidatura para vaga ativa.
        $job = JobPosting::where('id', $this->jobId)->where('is_active', true)->firstOrFail();

        $resumePath = $this->resume
            ? $this->resume->store('job-applications/resumes', 'public')
            : null;

        JobApplication::create([
            'job_posting_id' => $job->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'message' => $data['message'] ?? null,
            'resume_path' => $resumePath,
        ]);

        $this->reset(['name', 'email', 'phone', 'message', 'resume']);
        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.job-apply');
    }
}
