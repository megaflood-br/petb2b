<?php

namespace Tests\Feature;

use App\Livewire\Admin\ManageClaims;
use App\Livewire\ClaimCompany;
use App\Livewire\ClaimRegister;
use App\Mail\ClaimApprovedMail;
use App\Models\CompanyClaim;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class CompanyClaimFlowTest extends TestCase
{
    use RefreshDatabase;

    private function supplier(): Supplier
    {
        return Supplier::create([
            'name' => 'Empresa ' . uniqid(),
            'email' => 'emp_' . uniqid() . '@t.com',
            'description' => 'd',
            'category' => 'racas',
            'is_active' => true,
            'is_approved' => true,
        ]);
    }

    public function test_visitante_pode_enviar_reivindicacao_sem_login(): void
    {
        $supplier = $this->supplier();

        Livewire::test(ClaimCompany::class, ['supplierId' => $supplier->id])
            ->set('name', 'Carlos Visitante')
            ->set('email', 'carlos@empresa.com')
            ->set('message', 'Sou o proprietário desta empresa.')
            ->call('submitClaim');

        $this->assertDatabaseHas('company_claims', [
            'supplier_id' => $supplier->id,
            'user_id' => null,
            'claimant_name' => 'Carlos Visitante',
            'claimant_email' => 'carlos@empresa.com',
            'status' => 'pending',
        ]);
    }

    public function test_aprovar_reivindicacao_de_visitante_gera_token_e_envia_email(): void
    {
        Mail::fake();
        $supplier = $this->supplier();
        $claim = CompanyClaim::create([
            'supplier_id' => $supplier->id,
            'user_id' => null,
            'claimant_name' => 'João Dono',
            'claimant_email' => 'joao@empresa.com',
            'message' => 'Sou o proprietário desta empresa.',
            'status' => 'pending',
        ]);

        Livewire::test(ManageClaims::class)->call('approve', $claim->id);

        $claim->refresh();
        $this->assertEquals('approved', $claim->status);
        $this->assertNotNull($claim->approval_token);

        Mail::assertSent(ClaimApprovedMail::class, function ($mail) {
            return $mail->registrationLink !== null && $mail->hasTo('joao@empresa.com');
        });
    }

    public function test_cadastro_via_token_cria_usuario_supplier_e_vincula(): void
    {
        $supplier = $this->supplier();
        $token = Str::random(64);
        $claim = CompanyClaim::create([
            'supplier_id' => $supplier->id,
            'claimant_name' => 'Maria Dona',
            'claimant_email' => 'maria@empresa.com',
            'message' => 'Sou a dona.',
            'status' => 'approved',
            'approval_token' => $token,
            'approval_token_expires_at' => now()->addDays(7),
        ]);

        Livewire::test(ClaimRegister::class, ['token' => $token])
            ->set('name', 'Maria Dona')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register')
            ->assertRedirect(route('supplier.dashboard'));

        $user = User::where('email', 'maria@empresa.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('supplier', $user->role);
        $this->assertEquals($user->id, $supplier->fresh()->user_id);
        $this->assertNotNull($claim->fresh()->token_used_at);
    }

    public function test_token_invalido_retorna_404(): void
    {
        $this->get('/reivindicacao/cadastro/token-invalido')->assertNotFound();
    }

    public function test_token_usado_nao_pode_ser_reutilizado(): void
    {
        $supplier = $this->supplier();
        $token = Str::random(64);
        CompanyClaim::create([
            'supplier_id' => $supplier->id,
            'claimant_name' => 'Ana',
            'claimant_email' => 'ana@empresa.com',
            'message' => 'dona',
            'status' => 'approved',
            'approval_token' => $token,
            'approval_token_expires_at' => now()->addDays(7),
            'token_used_at' => now(),
        ]);

        $this->get('/reivindicacao/cadastro/' . $token)->assertNotFound();
    }

    public function test_admin_lista_reivindicacao_de_visitante_sem_erro(): void
    {
        $supplier = $this->supplier();
        CompanyClaim::create([
            'supplier_id' => $supplier->id,
            'user_id' => null,
            'claimant_name' => 'João Dono',
            'claimant_email' => 'joao@empresa.com',
            'message' => 'Sou o proprietário desta empresa.',
            'status' => 'pending',
        ]);

        Livewire::test(ManageClaims::class)
            ->assertOk()
            ->assertSee($supplier->name)
            ->assertSee('João Dono')
            ->assertSee('joao@empresa.com');
    }

    public function test_token_expirado_retorna_404(): void
    {
        $supplier = $this->supplier();
        $token = Str::random(64);
        CompanyClaim::create([
            'supplier_id' => $supplier->id,
            'claimant_name' => 'Pedro',
            'claimant_email' => 'pedro@empresa.com',
            'message' => 'dono',
            'status' => 'approved',
            'approval_token' => $token,
            'approval_token_expires_at' => now()->subDay(),
        ]);

        $this->get('/reivindicacao/cadastro/' . $token)->assertNotFound();
    }

    public function test_aprovar_reivindicacao_de_usuario_logado_promove_a_supplier(): void
    {
        Mail::fake();
        $user = User::create(['name' => 'Reader', 'email' => 'reader@t.com', 'password' => 'x']);
        $supplier = $this->supplier();
        $claim = CompanyClaim::create([
            'supplier_id' => $supplier->id,
            'user_id' => $user->id,
            'claimant_name' => $user->name,
            'claimant_email' => $user->email,
            'message' => 'sou dono',
            'status' => 'pending',
        ]);

        Livewire::test(ManageClaims::class)->call('approve', $claim->id);

        $this->assertEquals('supplier', $user->fresh()->role);
        $this->assertEquals($user->id, $supplier->fresh()->user_id);
        Mail::assertSent(ClaimApprovedMail::class);
    }
}
