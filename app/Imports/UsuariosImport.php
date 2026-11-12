<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class UsuariosImport implements WithMultipleSheets
{
    /**
     * @var UsuariosSheetImport Instancia del importador para la hoja principal de usuarios
     */
    protected UsuariosSheetImport $sheetImport;

    public function __construct()
    {
        $this->sheetImport = new UsuariosSheetImport();
    }

    /**
     * Define qué hojas se procesarán.
     * Solo procesa la hoja 0 (la primera hoja, correspondiente a los datos de usuarios),
     * omitiendo deliberadamente la hoja de instrucciones si está presente.
     */
    public function sheets(): array
    {
        return [
            0 => $this->sheetImport,
        ];
    }

    /**
     * Obtiene el reporte y resumen final tras procesar la hoja de usuarios.
     */
    public function getResumen(): array
    {
        return $this->sheetImport->getResumen();
    }
}
