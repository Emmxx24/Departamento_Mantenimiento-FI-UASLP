<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $table = 'Material';

    protected $primaryKey = 'id_material';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'cantidad',
        'id_unidad',
        'imagen',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'cantidad' => 'integer',
        'id_unidad' => 'integer',
    ];

    public function marcas()
    {
        return $this->belongsToMany(Marca::class, 'MarcaMaterial', 'id_material', 'id_marca');
    }

    public function materialRequisiciones()
    {
        return $this->hasMany(MaterialRequisicion::class, 'id_material', 'id_material');
    }

    public function unidad()
    {
        return $this->belongsTo(Unidad::class, 'id_unidad');
    }

    public function unidadMedida()
    {
        return $this->unidad();
    }

    public function getMarcaNombreAttribute(): string
    {
        return $this->marcas->first()?->nombre ?? 'Sin marca';
    }

    public function getImagenUrlAttribute(): ?string
    {
        if (!$this->imagen) {
            return null;
        }

        return asset('storage/' . $this->imagen);
    }
}
