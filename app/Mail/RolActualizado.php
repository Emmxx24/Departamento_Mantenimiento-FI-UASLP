<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RolActualizado extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $usuario,
        public string $nuevoRol
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Actualización de rol de usuario - Departamento de Mantenimiento',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.rol-actualizado',
        );
    }
}

