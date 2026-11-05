<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aula extends Model
{
    protected $table = 'CatalogoAula';

    public $timestamps = false;

    protected $primaryKey = 'id_aula';

    protected $fillable = [
        'nombre',
        'id_piso',
        'id_lugar',
        'activo'
    ];

    public function piso()
    {
        return $this->belongsTo(Piso::class, 'id_piso');
    }

    public function lugar()
    {
        return $this->belongsTo(Lugar::class, 'id_lugar');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'id_aula');
    }
}
