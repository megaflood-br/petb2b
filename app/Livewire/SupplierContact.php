<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Lead;

class SupplierContact extends Component
{
    public $supplier_id;
    public $name, $email, $phone, $message;

    public function save()
    {
        // Validação rigorosa dos campos obrigatórios e tratamento opcional para o telefone/whatsapp
        $this->validate([
            'name' => 'required|min:3',
            'email' => 'required|email',
            'phone' => 'nullable|min:8', // Opcional: permite salvar vazio, mas valida o tamanho se digitarem
            'message' => 'required|min:10',
        ]);

        // Cria o registro na tabela leads vinculando o número digitado ao campo 'phone'
        Lead::create([
            'supplier_id' => $this->supplier_id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'message' => $this->message,
            'is_read' => false, // Garante que caia como "não lido" no painel do fornecedor
        ]);

        // Limpa todos os campos do formulário após o sucesso do insert
        $this->reset(['name', 'email', 'phone', 'message']);

        // Mensagem de feedback disparada instantaneamente para o Blade
        session()->flash('message', 'Mensagem enviada com sucesso!');
    }

    public function render()
    {
        return view('livewire.supplier-contact');
    }
}
