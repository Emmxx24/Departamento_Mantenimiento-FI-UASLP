<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordActualizada extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $usuario,
        public string $nuevaPassword
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu contraseña ha sido actualizada - Departamento de Mantenimiento',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.password-actualizada',
        );
    }
}

