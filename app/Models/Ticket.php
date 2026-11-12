<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $table = 'Ticket';

    public $timestamps = false;

    protected $primaryKey = 'id_ticket';

    protected $fillable = [
        'fecha_creacion',
        'fecha_cierre',
        'estado',
        'descripcion',
        'asunto',
        'prioridad',
        'es_borrador',
        'id_solicitante',
        'id_admin_atiende',
        'id_edificio',
        'id_servicio',
        'id_aula',
        'piso',
    ];

    public function scopeVisiblesPara($query, $user)
    {
        if ($user->rol === 'Administrador') {
            $query->where('id_admin_atiende', $user->id);
        }
    }

    /**
     * Regla única de acceso a un ticket (detalle, archivos y chat):
     * - Superadministrador: todos.
     * - Cualquier usuario: los tickets que él creó.
     * - Administrador: además, los que tiene asignados.
     */
    public function puedeVer(User $user): bool
    {
        if ($user->rol === 'Superadministrador') {
            return true;
        }

        if ($this->id_solicitante == $user->id) {
            return true;
        }

        return $user->rol === 'Administrador' && $this->id_admin_atiende == $user->id;
    }

    /**
     * El chat queda en solo lectura cuando el ticket concluyó.
     */
    public function chatBloqueado(): bool
    {
        return in_array($this->estado, ['Cerrado', 'Terminado']);
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'id_servicio');
    }

    public function edificio()
    {
        return $this->belongsTo(Edificio::class, 'id_edificio');
    }

    public function lugar()
    {
        return $this->belongsTo(Aula::class, 'id_aula');
    }

    public function solicitante()
    {
        return $this->belongsTo(User::class, 'id_solicitante');
    }

    public function adminAtiende()
    {
        return $this->belongsTo(User::class, 'id_admin_atiende');
    }

    public function mensajes()
    {
        return $this->hasMany(Mensaje::class, 'id_ticket', 'id_ticket')->orderBy('fecha_men', 'asc');
    }

    public function archivos()
    {
        return $this->hasMany(Archivo::class, 'id_ticket', 'id_ticket');
    }

    public function requisiciones()
    {
        return $this->hasMany(Requisicion::class, 'id_ticket', 'id_ticket');
    }
}
