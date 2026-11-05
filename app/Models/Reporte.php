<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reporte extends Model
{
    protected $table = 'Reporte';

    public $timestamps = false;

    protected $primaryKey = 'id_reporte';

    protected $fillable = [
        'fecha_rep',
        'fecha_ini',
        'fecha_fin',
        'id_edificio',
        'id_servicio',
        'id_lugar',
        'id_admin',
        'prioridad',
        'estado',
        'ruta_pdf',
        'ruta_excel'
    ];
}
