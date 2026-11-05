<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsuariosPlantillaInstruccionesSheet implements FromArray, WithHeadings, ShouldAutoSize, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'Instrucciones';
    }

    public function headings(): array
    {
        return [
            'Campo',
            'Tipo de Dato',
            'Obligatorio',
            'Reglas y Restricciones Institucionales',
        ];
    }

    public function array(): array
    {
        return [
            [
                'clave',
                'Numérico UASLP',
                'SÍ',
                'Número institucional (solo dígitos numéricos, máximo 20 caracteres). No se permite modificar ni sobreescribir la clave del Superadministrador principal.',
            ],
            [
                'nombre',
                'Texto',
                'SÍ',
                'Nombre o nombres del usuario (máximo 50 caracteres).',
            ],
            [
                'ap_pat',
                'Texto',
                'SÍ',
                'Primer apellido del usuario (máximo 50 caracteres).',
            ],
            [
                'ap_mat',
                'Texto',
                'NO',
                'Segundo apellido del usuario (opcional, máximo 50 caracteres).',
            ],
            [
                'email',
                'Correo Institucional',
                'SÍ',
                'Debe pertenecer estrictamente al dominio "@uaslp.mx". No se admiten dominios comerciales (@gmail, @hotmail, etc.).',
            ],
            [
                'rol',
                'Texto (Rol)',
                'SÍ',
                'Debe ser exactamente uno de los roles válidos: "Superadministrador", "Administrador" o "Solicitante". Sujeto a disponibilidad de cupos en el sistema.',
            ],
            [
                '---',
                '---',
                '---',
                '--------------------------------------------------------------------------------------------------------------------',
            ],
            [
                'REGLA: Cupos',
                'Políticas de Acceso',
                'Informativo',
                'Si el archivo contiene más Superadministradores o Administradores de los cupos configurados en el sistema, los registros excedentes serán omitidos y reportados.',
            ],
            [
                'REGLA: Contraseñas',
                'Autenticación',
                'Informativo',
                'En modo local: se asigna automáticamente como contraseña temporal la misma clave institucional. En modo institucional: la autenticación es validada por LDAP central.',
            ],
            [
                'REGLA: Duplicados',
                'Integridad',
                'Informativo',
                'Si una clave o correo ya existe en el sistema o se repite dentro del mismo archivo, la fila se omite para proteger la integridad de los datos.',
            ],
            [
                'REGLA: Primera Hoja',
                'Importación',
                'Informativo',
                'El importador procesa automáticamente la primera hoja ("Usuarios"). Esta hoja de instrucciones se incluye como guía de referencia.',
            ],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Cabecera institucional (Fila 1)
        $sheet->getStyle('A1:D1')->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size'  => 11,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '002D5A'], // Azul obscuro institucional
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(28);

        // Bordes y alineación de datos (Filas 2-12)
        $sheet->getStyle('A2:D12')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => 'E1E4E8'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getStyle('A2:C12')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }
}
