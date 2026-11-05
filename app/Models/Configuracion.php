<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    use HasFactory;

    protected $table = 'configuraciones';

    public $timestamps = false;

    protected $fillable = [
        'sistema_inicializado',
        'login_institucional',
        'max_superadmins',
        'max_admins',
        'correo_contacto',
        'telefono_contacto',
        'clave_superadmin_origen',
        'minutos_expiracion_otp',
    ];

    protected $casts = [
        'sistema_inicializado'   => 'boolean',
        'login_institucional'    => 'boolean',
        'max_superadmins'        => 'integer',
        'max_admins'             => 'integer',
        'minutos_expiracion_otp' => 'integer',
    ];

    /**
     * Retorna la configuración singleton del sistema.
     */
    public static function obtener(): ?self
    {
        return self::first();
    }

    /**
     * Indica si el sistema ya fue inicializado por el Superadministrador.
     */
    public function isInitialized(): bool
    {
        return (bool) $this->sistema_inicializado;
    }

    /**
     * Indica si el modo de autenticación es institucional UASLP (true) o local (false).
     */
    public function isInstitucional(): bool
    {
        return (bool) $this->login_institucional;
    }

    /**
     * Indica si el sistema permite la autogestión y recuperación de contraseñas locales.
     * Solo es válido si el sistema ya fue inicializado y el login institucional está desactivado.
     */
    public function permiteGestionPassword(): bool
    {
        return (bool) ($this->sistema_inicializado && !$this->login_institucional);
    }

    /**
     * Comprueba si una clave dada corresponde al Superadministrador raíz original.
     */
    public function isSuperadminRaiz(?string $clave): bool
    {
        return !empty($this->clave_superadmin_origen) && $this->clave_superadmin_origen === trim((string) $clave);
    }
}
