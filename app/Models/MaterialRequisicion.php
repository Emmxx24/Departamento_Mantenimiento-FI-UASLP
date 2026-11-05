<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialRequisicion extends Model
{
    protected $table = 'MaterialRequisicion';

    protected $primaryKey = 'id_item';

    public $incrementing = true;

    public $timestamps = false;

    protected $fillable = [
        'id_requisicion',
        'id_material',    // nullable: null si el material no está en el inventario
        'nombre_libre',   // usado cuando id_material es null
        'especificaciones',
        'unidad',
        'marca_texto',    // texto plano, NO es FK a la tabla Marca
        'cantidad',
        'unitario',
        'subtotal',       // calculado: cantidad * unitario
        ];

    protected $casts = [
        'unitario' => 'float',
        'subtotal' => 'float',
        'cantidad' => 'integer',
    ];

    /**
     * Relación al material del inventario (nullable).
     */
    public function material()
    {
        return $this->belongsTo(Material::class, 'id_material', 'id_material');
    }

    /**
     * Relación a la requisición padre.
     */
    public function requisicion()
    {
        return $this->belongsTo(Requisicion::class, 'id_requisicion', 'id_requisicion');
    }

    /**
     * Helper: retorna el nombre a mostrar según si es de BD o libre.
     */
    public function getNombreDisplayAttribute(): string
    {
        return $this->material ? $this->material->nombre : ($this->nombre_libre ?? 'Sin nombre');
    }

    /**
     * Helper: retorna true si el material NO está en el inventario.
     */
    public function esLibreAttribute(): bool
    {
        return is_null($this->id_material);
    }
}
