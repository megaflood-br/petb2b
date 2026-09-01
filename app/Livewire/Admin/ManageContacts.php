<?php

namespace App\Livewire\Admin;

use App\Models\ContactMessage;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactResponseMail;

class ManageContacts extends Component
{
    use WithPagination;

    public $replyingTo = null; // Armazena ID da mensagem sendo respondida
    public $replyText = '';

    #[Layout('layouts.admin')]
    public function render()
    {
        return view('livewire.admin.manage-contacts', [
            'messages' => ContactMessage::latest()->paginate(10)
        ]);
    }

    public function startReply($id)
    {
        $this->replyingTo = $id;
        $this->replyText = '';
    }

    public function sendReply()
    {
        $this->validate(['replyText' => 'required|min:5']);

        $msg = ContactMessage::findOrFail($this->replyingTo);

        // Dispara o e-mail real
        Mail::to($msg->email)->send(new ContactResponseMail($this->replyText, $msg->subject));

        // Marca como lida automaticamente ao responder
        $msg->update(['is_read' => true]);

        $this->replyingTo = null;
        session()->flash('message', 'Resposta enviada com sucesso para ' . $msg->email);
    }

    public function markAsRead($id)
    {
        ContactMessage::findOrFail($id)->update(['is_read' => true]);
    }

    public function delete($id)
    {
        ContactMessage::findOrFail($id)->delete();
        session()->flash('message', 'Mensagem removida.');
    }
}
