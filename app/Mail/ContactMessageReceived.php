<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessageReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ContactMessage $message)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nuevo mensaje de contacto: '.$this->subjectLabel(),
            replyTo: [$this->message->cmsg_dsemai => $this->message->cmsg_nmname],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.contact-message-received',
            with: [
                'name' => $this->message->cmsg_nmname,
                'email' => $this->message->cmsg_dsemai,
                'subjectLabel' => $this->subjectLabel(),
                'body' => $this->message->cmsg_dsmess,
                'ip' => $this->message->cmsg_dsipad,
                'receivedAt' => $this->message->cmsg_dtcrea,
            ],
        );
    }

    protected function subjectLabel(): string
    {
        return match ($this->message->cmsg_cdsubj) {
            ContactMessage::SUBJECT_QUOTE => 'Cotización',
            ContactMessage::SUBJECT_SUPPORT => 'Soporte',
            ContactMessage::SUBJECT_OTHER => 'Otros',
            default => $this->message->cmsg_cdsubj,
        };
    }
}
