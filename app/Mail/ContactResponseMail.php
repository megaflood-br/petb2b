<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactResponseMail extends Mailable
{
    use Queueable, SerializesModels;

    public $replyMessage;
    public $originalSubject;

    public function __construct($replyMessage, $originalSubject)
    {
        $this->replyMessage = $replyMessage;
        $this->originalSubject = $originalSubject;
    }

    public function build()
    {
        return $this->subject('RE: ' . $this->originalSubject)
                    ->html("<h3>Resposta do Pet Business Pro</h3><p>{$this->replyMessage}</p>");
    }
}
