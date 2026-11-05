<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Poa extends Model
{
    protected $table = 'POA';

    public $timestamps = false;

    protected $primaryKey = 'id_poa';

    protected $fillable = [
        'fecha_poa',
        'area',
        'subarea',
        'descripcion',
        'observaciones', //NUEVO
        'monto',
        'es_borrador',
        'id_requisicion',
    ];

    // Agregar relación hacia requisicion
public function requisicion()
{
    return $this->belongsTo(Requisicion::class, 'id_requisicion', 'id_requisicion');
}
}
