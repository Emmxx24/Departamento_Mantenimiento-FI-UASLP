<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Piso extends Model
{
    protected $table = 'CatalogoPiso';

    public $timestamps = false;

    protected $primaryKey = 'id_piso';

    protected $fillable = [
        'piso',
        'id_edificio',
        'activo'
    ];

    public function edificio()
    {
        return $this->belongsTo(Edificio::class, 'id_edificio');
    }

    public function aulas()
    {
        return $this->hasMany(Aula::class, 'id_piso');
    }

    /**
     * Nombre legible del piso para mostrar en el frontend.
     */
    public function getNombreDisplayAttribute()
    {
        return $this->piso === 0 ? 'Planta baja' : 'Piso ' . $this->piso;
    }
}
