<?php

namespace App\Livewire;

use App\Models\ContactMessage;
use Livewire\Component;

class ContactForm extends Component
{
    public $name, $email, $subject, $message;

    protected $rules = [
        'name' => 'required|min:3',
        'email' => 'required|email',
        'subject' => 'required',
        'message' => 'required|min:5',
    ];

    public function send()
    {
        // Força a validação antes de qualquer coisa
        $this->validate();

        // Salva no banco de dados
        ContactMessage::create([
            'name' => $this->name,
            'email' => $this->email,
            'subject' => $this->subject,
            'message' => $this->message,
        ]);

        // Limpa os campos após o envio
        $this->reset(['name', 'email', 'subject', 'message']);

        // Emite a mensagem de sucesso
        session()->flash('success', 'Sua mensagem foi enviada com sucesso!');
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
