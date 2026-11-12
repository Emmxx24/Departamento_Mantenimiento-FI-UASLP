<?php

namespace App\Mail;

use App\Models\Mensaje;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NuevoMensajeChat extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Ticket $ticket, public Mensaje $mensaje)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Nuevo mensaje en ticket #{$this->ticket->id_ticket}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.nuevo-mensaje',
        );
    }
}