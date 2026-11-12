<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unidad extends Model
{
    protected $table = 'CatalogoUnidad';
    protected $primaryKey = 'id_unidad';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'abreviatura',
        'activo',
    ];

    public function materiales()
    {
        return $this->hasMany(Material::class, 'id_unidad');
    }

    public function materialesRequisicion()
    {
        return $this->hasMany(MaterialRequisicion::class, 'unidad', 'abreviatura');
    }

    public function estaEnUso(): bool
    {
        return $this->materiales()->exists() || $this->materialesRequisicion()->exists();
    }
}
