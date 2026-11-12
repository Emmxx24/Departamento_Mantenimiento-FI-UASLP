<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Edificio extends Model
{
    protected $table = 'CatalogoEdificio';

    public $timestamps = false;

    protected $primaryKey = 'id_edificio';

    protected $fillable = [
        'nombre',
        'activo'
    ];

    public function pisos()
    {
        return $this->hasMany(Piso::class, 'id_edificio')->orderBy('piso', 'asc');
    }

    public function aulas()
    {
        return $this->hasManyThrough(Aula::class, Piso::class, 'id_edificio', 'id_piso', 'id_edificio', 'id_piso');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'id_edificio');
    }
}
