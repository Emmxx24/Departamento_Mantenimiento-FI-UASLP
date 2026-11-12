<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Archivo extends Model
{
    protected $table = 'Archivo';

    public $timestamps = false;

    protected $primaryKey = 'id_archivo';

    protected $fillable = [
        'nombre_archivo',
        'ruta',
        'extension',
        'id_ticket',
        'id_mensaje',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'id_ticket', 'id_ticket');
    }

    public function mensaje(): BelongsTo
    {
        return $this->belongsTo(Mensaje::class, 'id_mensaje', 'id_mensaje');
    }

    /**
     * Resuelve el ticket padre al que pertenece este archivo,
     * ya sea directamente por id_ticket o a través de su mensaje asociado.
     */
    public function ticketAsociado(): ?Ticket
    {
        if ($this->id_ticket) {
            return $this->ticket ?? Ticket::find($this->id_ticket);
        }

        if ($this->id_mensaje) {
            $mensaje = $this->mensaje ?? Mensaje::find($this->id_mensaje);
            return $mensaje ? ($mensaje->ticket ?? Ticket::find($mensaje->id_ticket)) : null;
        }

        return null;
    }
}
