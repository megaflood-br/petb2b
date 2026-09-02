<?php

namespace App\Mail;

use App\Models\CompanyClaim;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClaimApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public CompanyClaim $claim,
        public ?string $registrationLink = null,
    ) {
    }

    public function build()
    {
        $company = $this->claim->supplier->name ?? 'sua empresa';
        $name = $this->claim->claimant_name ?? optional($this->claim->user)->name ?? 'Parceiro';

        if ($this->registrationLink) {
            $cta = '<p>Para ativar o acesso e gerenciar <strong>' . e($company) . '</strong>, crie a conta de administrador da sua empresa:</p>'
                . '<p><a href="' . e($this->registrationLink) . '">Criar acesso da minha empresa</a></p>'
                . '<p style="font-size:12px;color:#666">Ou copie e cole no navegador:<br>' . e($this->registrationLink) . '</p>';
        } else {
            $panel = route('supplier.dashboard');
            $cta = '<p>Seu acesso já está ativo. Entre no painel da sua empresa:</p>'
                . '<p><a href="' . e($panel) . '">Acessar painel</a></p>';
        }

        return $this->subject('Reivindicação aprovada - ' . $company)
            ->html('<h2>Bem-vindo(a), ' . e($name) . '!</h2>'
                . '<p>Sua reivindicação da empresa <strong>' . e($company) . '</strong> foi aprovada no Pet Business Pro.</p>'
                . $cta
                . '<p>Equipe Pet Business Pro</p>');
    }
}
