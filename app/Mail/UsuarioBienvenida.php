<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UsuarioBienvenida extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $usuario,
        public ?string $passwordInicial = null,
        public bool $esInstitucional = false
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bienvenido al Sistema de Mantenimiento - Facultad de Ingeniería UASLP',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.usuario-bienvenida',
        );
    }
}

