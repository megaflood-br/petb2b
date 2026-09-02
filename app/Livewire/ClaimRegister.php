<?php

namespace App\Livewire;

use App\Models\CompanyClaim;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Component;

class ClaimRegister extends Component
{
    public string $token = '';
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $companyName = '';

    public function mount(string $token)
    {
        $this->token = $token;
        $claim = $this->resolveClaim();

        $this->name = $claim->claimant_name ?? '';
        $this->email = (string) $claim->claimant_email;
        $this->companyName = $claim->supplier->name ?? 'sua empresa';
    }

    /**
     * Resolve o claim válido a partir do token (aprovado, não usado, não expirado).
     */
    private function resolveClaim(): CompanyClaim
    {
        $claim = CompanyClaim::with('supplier')
            ->where('approval_token', $this->token)
            ->where('status', 'approved')
            ->whereNull('token_used_at')
            ->where(function ($q) {
                $q->whereNull('approval_token_expires_at')
                  ->orWhere('approval_token_expires_at', '>', now());
            })
            ->first();

        abort_unless($claim, 404);

        return $claim;
    }

    public function register()
    {
        $claim = $this->resolveClaim();

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $email = (string) $claim->claimant_email;

        // Reaproveita conta existente com o mesmo e-mail; caso contrário cria.
        $user = User::where('email', $email)->first();
        if (! $user) {
            $user = User::create([
                'name' => $this->name,
                'email' => $email,
                'password' => Hash::make($this->password),
            ]);
        }

        // Promove a fornecedor e vincula à empresa.
        $user->forceFill(['role' => 'supplier'])->save();
        $claim->supplier->update(['user_id' => $user->id]);
        $claim->update(['user_id' => $user->id, 'token_used_at' => now()]);

        Auth::login($user);

        return redirect()->route('supplier.dashboard');
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.claim-register');
    }
}
