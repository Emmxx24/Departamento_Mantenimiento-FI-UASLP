<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketActualizado extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public string $campo,
        public mixed $valor
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Actualización en ticket #{$this->ticket->id_ticket}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.ticket-actualizado',
        );
    }
}
