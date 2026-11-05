<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mensaje extends Model
{
    protected $table = 'Mensaje';

    public $timestamps = false;

    protected $primaryKey = 'id_mensaje';

    protected $fillable = [
        'texto',
        'fecha_men',
        'remitente',
        'id_ticket',
    ];

    public function archivos()
    {
        return $this->hasMany(Archivo::class, 'id_mensaje', 'id_mensaje');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'remitente');
    }
}
