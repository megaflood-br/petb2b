<?php

namespace Tests\Feature;

use App\Livewire\JobApply;
use App\Livewire\Supplier\ManageJobs;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class JobApplicationTest extends TestCase
{
    use RefreshDatabase;

    private function makeJob(?User $user = null): JobPosting
    {
        $supplier = Supplier::create([
            'name' => 'Empresa ' . uniqid(),
            'email' => 'e_' . uniqid() . '@t.com',
            'description' => 'd',
            'category' => 'racas',
            'user_id' => $user?->id,
            'is_active' => true,
            'is_approved' => true,
        ]);

        return JobPosting::create([
            'supplier_id' => $supplier->id,
            'title' => 'Vaga Teste',
            'description' => 'Descrição completa da vaga.',
            'type' => 'CLT',
            'how_to_apply' => 'rh@empresa.com',
            'is_active' => true,
        ]);
    }

    public function test_candidatura_cria_registro(): void
    {
        $job = $this->makeJob();

        Livewire::test(JobApply::class, ['jobId' => $job->id])
            ->set('name', 'Maria Candidata')
            ->set('email', 'maria@teste.com')
            ->set('phone', '11999998888')
            ->set('message', 'Tenho experiência na área.')
            ->call('apply')
            ->assertSet('submitted', true);

        $this->assertDatabaseHas('job_applications', [
            'job_posting_id' => $job->id,
            'email' => 'maria@teste.com',
            'name' => 'Maria Candidata',
        ]);
    }

    public function test_candidatura_valida_campos_obrigatorios(): void
    {
        $job = $this->makeJob();

        Livewire::test(JobApply::class, ['jobId' => $job->id])
            ->call('apply')
            ->assertHasErrors(['name', 'email']);

        $this->assertEquals(0, JobApplication::count());
    }

    public function test_fornecedor_ve_candidaturas_apenas_das_proprias_vagas(): void
    {
        $userA = User::create(['name' => 'A', 'email' => 'a_' . uniqid() . '@t.com', 'password' => 'secret']);
        $jobA = $this->makeJob($userA);
        JobApplication::create(['job_posting_id' => $jobA->id, 'name' => 'Cand A', 'email' => 'ca@t.com']);

        $jobB = $this->makeJob(); // outro fornecedor

        $this->actingAs($userA);

        Livewire::test(ManageJobs::class)
            ->call('viewApplications', $jobA->id)
            ->assertSet('selectedJobId', $jobA->id)
            ->assertSee('Cand A');

        // Não consegue abrir candidaturas de vaga de outro fornecedor.
        try {
            Livewire::test(ManageJobs::class)->call('viewApplications', $jobB->id);
            $this->fail('Deveria lançar ModelNotFoundException.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // esperado
        }
    }
}
