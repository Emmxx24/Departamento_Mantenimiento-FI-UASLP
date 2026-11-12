<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lugar extends Model
{
    protected $table = 'CatalogoLugar';

    public $timestamps = false;

    protected $primaryKey = 'id_lugar';

    protected $fillable = ['nombre', 'activo'];

    public function aulas()
    {
        return $this->hasMany(Aula::class, 'id_lugar');
    }
}
