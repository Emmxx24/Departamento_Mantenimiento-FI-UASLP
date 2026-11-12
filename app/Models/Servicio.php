<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    protected $table = 'CatalogoServicio';

    public $timestamps = false;

    protected $primaryKey = 'id_servicio';

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo'
    ];

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'id_servicio');
    }
}
