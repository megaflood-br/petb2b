<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeAdmin extends Command
{
    protected $signature = 'user:make-admin {email : E-mail da conta que vira admin}';

    protected $description = 'Define role=admin em um usuário existente';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));
        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            $this->error("Nenhuma conta com o e-mail {$email}.");

            return self::FAILURE;
        }

        $user->forceFill([
            'role' => 'admin',
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();

        $this->info("{$user->email} agora é admin. Saia e entre de novo no site.");

        return self::SUCCESS;
    }
}
