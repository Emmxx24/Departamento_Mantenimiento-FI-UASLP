<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class UsuariosPlantillaExport implements WithMultipleSheets
{
    /**
     * Retorna las hojas que componen la plantilla oficial de usuarios:
     * 1. Hoja "Usuarios": Tabla con cabeceras y filas de ejemplo.
     * 2. Hoja "Instrucciones": Guía completa de restricciones y validaciones.
     */
    public function sheets(): array
    {
        return [
            new UsuariosPlantillaDataSheet(),
            new UsuariosPlantillaInstruccionesSheet(),
        ];
    }
}
