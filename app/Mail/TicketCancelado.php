<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketCancelado extends Mailable
{
    use Queueable, SerializesModels;

    public string $nombreSolicitante;

    public function __construct(
        public Ticket $ticket
    ) {
        $solicitante = $ticket->solicitante;
        $this->nombreSolicitante = $solicitante
            ? trim("{$solicitante->nombre} {$solicitante->ap_pat} {$solicitante->ap_mat}")
            : 'Solicitante';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Ticket #{$this->ticket->id_ticket} Cancelado - Departamento de Mantenimiento",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.ticket-cancelado',
        );
    }
}

