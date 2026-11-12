<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'clave',
        'nombre',
        'ap_pat',
        'ap_mat',
        'email',
        'password',
        'rol',
        'activo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombre} {$this->ap_pat} " . ($this->ap_mat ?? ''));
    }

    public function ticketsSolicitados()
    {
        return $this->hasMany(Ticket::class, 'id_solicitante');
    }

    public function ticketsAtendidos()
    {
        return $this->hasMany(Ticket::class, 'id_admin_atiende');
    }

    public function mensajes()
    {
        return $this->hasMany(Mensaje::class, 'remitente');
    }

    public function reportes()
    {
        return $this->hasMany(Reporte::class, 'id_admin');
    }

    public function tieneRegistrosAsociados(): bool
    {
        return $this->ticketsSolicitados()->exists()
            || $this->ticketsAtendidos()->exists()
            || $this->mensajes()->exists()
            || $this->reportes()->exists();
    }
}
