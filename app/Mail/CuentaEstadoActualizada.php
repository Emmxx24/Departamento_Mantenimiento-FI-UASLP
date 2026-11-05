<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CuentaEstadoActualizada extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $usuario,
        public bool $activo
    ) {}

    public function envelope(): Envelope
    {
        $estado = $this->activo ? 'reactivada' : 'desactivada';

        return new Envelope(
            subject: "Tu cuenta ha sido {$estado} - Departamento de Mantenimiento",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.cuenta-estado-actualizada',
        );
    }
}

