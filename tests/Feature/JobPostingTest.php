<?php

namespace Tests\Feature;

use App\Livewire\Supplier\ManageJobs;
use App\Models\JobPosting;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class JobPostingTest extends TestCase
{
    use RefreshDatabase;

    private function makeSupplier(?User $user = null): Supplier
    {
        return Supplier::create([
            'name' => 'Empresa ' . uniqid(),
            'email' => 'e_' . uniqid() . '@t.com',
            'description' => 'd',
            'category' => 'racas',
            'city' => 'Atibaia',
            'state' => 'SP',
            'user_id' => $user?->id,
            'is_active' => true,
            'is_approved' => true,
        ]);
    }

    private function makeJob(Supplier $s, array $overrides = []): JobPosting
    {
        return JobPosting::create(array_merge([
            'supplier_id' => $s->id,
            'title' => 'Vendedor Pet',
            'description' => 'Descrição completa da vaga com requisitos.',
            'type' => 'CLT',
            'city' => 'Atibaia',
            'state' => 'SP',
            'how_to_apply' => 'rh@empresa.com',
            'is_active' => true,
        ], $overrides));
    }

    public function test_listagem_publica_mostra_ativas_e_esconde_inativas(): void
    {
        $s = $this->makeSupplier();
        $this->makeJob($s, ['title' => 'Vaga Ativa Visivel']);
        $this->makeJob($s, ['title' => 'Vaga Pausada Oculta', 'is_active' => false]);

        $this->get('/vagas')
            ->assertOk()
            ->assertSee('Vaga Ativa Visivel')
            ->assertDontSee('Vaga Pausada Oculta');
    }

    public function test_detalhe_ativa_carrega_inativa_retorna_404(): void
    {
        $s = $this->makeSupplier();
        $ativa = $this->makeJob($s, ['title' => 'Analista de Marketing']);
        $inativa = $this->makeJob($s, ['is_active' => false]);

        $this->get('/vagas/' . $ativa->slug)->assertOk()->assertSee('Analista de Marketing');
        $this->get('/vagas/' . $inativa->slug)->assertNotFound();
    }

    public function test_fornecedor_cria_vaga(): void
    {
        $user = User::create(['name' => 'F', 'email' => 'f_' . uniqid() . '@t.com', 'password' => 'secret']);
        $s = $this->makeSupplier($user);
        $this->actingAs($user);

        Livewire::test(ManageJobs::class)
            ->set('title', 'Auxiliar de Banho e Tosa')
            ->set('description', 'Vaga para auxiliar com experiência mínima.')
            ->set('type', 'CLT')
            ->set('how_to_apply', 'https://wa.me/5511999999999')
            ->call('save');

        $job = JobPosting::where('supplier_id', $s->id)->first();
        $this->assertNotNull($job);
        $this->assertEquals('Auxiliar de Banho e Tosa', $job->title);
        $this->assertNotEmpty($job->slug);
    }

    public function test_fornecedor_so_ve_e_gerencia_as_proprias_vagas(): void
    {
        $userA = User::create(['name' => 'A', 'email' => 'a_' . uniqid() . '@t.com', 'password' => 'secret']);
        $supplierA = $this->makeSupplier($userA);
        $this->makeJob($supplierA, ['title' => 'Vaga da Empresa A']);

        $supplierB = $this->makeSupplier();
        $jobB = $this->makeJob($supplierB, ['title' => 'Vaga da Empresa B']);

        $this->actingAs($userA);

        // A vê só as suas.
        Livewire::test(ManageJobs::class)
            ->assertSee('Vaga da Empresa A')
            ->assertDontSee('Vaga da Empresa B');

        // A não consegue excluir a vaga da B.
        try {
            Livewire::test(ManageJobs::class)->call('delete', $jobB->id);
            $this->fail('Deveria ter lançado ModelNotFoundException.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // esperado
        }

        $this->assertDatabaseHas('job_postings', ['id' => $jobB->id]);
    }
}
