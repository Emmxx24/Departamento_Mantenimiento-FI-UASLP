<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportesExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $reporte;
    protected $datos;

    public function __construct($reporte, $datos)
    {
        $this->reporte = $reporte;
        $this->datos = $datos;
    }

    public function view(): View
    {
        return view(
            'paginas.reportes-excel',
            array_merge(
                $this->datos,
                [
                    'reporte' => $this->reporte
                ]
            )
        );
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true
                ]
            ],
        ];
    }
}