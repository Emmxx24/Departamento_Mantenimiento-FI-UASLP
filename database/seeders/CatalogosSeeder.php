<?php

namespace Database\Seeders;

use App\Models\Lugar;
use App\Models\Servicio;
use App\Models\Edificio;
use App\Models\Piso;
use App\Models\Aula;
use App\Models\Unidad;
use Illuminate\Database\Seeder;

class CatalogosSeeder extends Seeder
{
    public function run(): void
    {
        // =========================================================================
        // 1. CREACIÓN DE LUGARES (TIPOS DE ESPACIO)
        // =========================================================================
        $lugares = [
            ['nombre' => 'Salón'],
            ['nombre' => 'Laboratorio'],
            ['nombre' => 'Oficina'],
            ['nombre' => 'Baño'],
        ];

        foreach ($lugares as $lugar) {
            Lugar::firstOrCreate(['nombre' => $lugar['nombre']], $lugar);
        }

        // =========================================================================
        // 2. CREACIÓN DE EDIFICIOS, PISOS Y AULAS (DEFINICIÓN DIRECTA)
        // Catálogo oficial de salones de la Facultad de Ingeniería
        // =========================================================================
        $edificios = [
            [
                'nombre' => 'Edificio A',
                'activo' => 1,
                'pisos' => [
                    [
                        'piso' => 0,
                        'activo' => 1,
                        'aulas' => [
                            ['nombre' => 'A-01', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'A-02', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'A-03', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'A-04', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'A-05', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'A-06', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'A-07', 'id_lugar' => 1, 'activo' => 1],
                        ],
                    ],
                    [
                        'piso' => 1,
                        'activo' => 1,
                        'aulas' => [
                            ['nombre' => 'A-11', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'A-12', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'A-13', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'A-14', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'A-15', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'A-16', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'A-17', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'A-18-1', 'id_lugar' => 3, 'activo' => 1],
                            ['nombre' => 'A-18-2', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'A-18-3', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'A-18-4', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'A-18-5', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'A-18-6', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'A-18-7', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'A-18-8', 'id_lugar' => 1, 'activo' => 1],
                        ],
                    ],
                    [
                        'piso' => 2,
                        'activo' => 1,
                        'aulas' => [
                            ['nombre' => 'A-21', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'A-22', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'A-23', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'A-24', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'A-25', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'A-26-1', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'A-26-2', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'A-26-3', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'A-26-4', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'A-26-5', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'A-26-6', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'A-26-7', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'A-26-8', 'id_lugar' => 1, 'activo' => 1],
                        ],
                    ],
                ],
            ],
            [
                'nombre' => 'Edificio B',
                'activo' => 1,
                'pisos' => [
                    [
                        'piso' => 0,
                        'activo' => 1,
                        'aulas' => [
                            ['nombre' => 'B-01', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'B-02', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'B-03', 'id_lugar' => 1, 'activo' => 1],
                        ],
                    ],
                    [
                        'piso' => 1,
                        'activo' => 1,
                        'aulas' => [
                            ['nombre' => 'B-11', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'B-12', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'B-13', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'B-14', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'B-15', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'B-16', 'id_lugar' => 1, 'activo' => 1],
                        ],
                    ],
                    [
                        'piso' => 2,
                        'activo' => 1,
                        'aulas' => [
                            ['nombre' => 'B-21', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'B-22', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'B-23', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'B-24', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'B-25', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'B-26', 'id_lugar' => 1, 'activo' => 1],
                        ],
                    ],
                ],
            ],
            [
                'nombre' => 'Edificio C',
                'activo' => 1,
                'pisos' => [
                    [
                        'piso' => 0,
                        'activo' => 1,
                        'aulas' => [
                            ['nombre' => 'C-01', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'C-02', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'C-03', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'C-04', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'C-LCNC', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'C-LAB_MET', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'CCIM', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'C-05', 'id_lugar' => 1, 'activo' => 1],
                        ],
                    ],
                    [
                        'piso' => 1,
                        'activo' => 1,
                        'aulas' => [
                            ['nombre' => 'C-11', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'C-12', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'C-13', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'C-14', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'C-15', 'id_lugar' => 2, 'activo' => 1],
                        ],
                    ],
                ],
            ],
            [
                'nombre' => 'Edificio CCIM',
                'activo' => 1,
                'pisos' => [
                    [
                        'piso' => 0,
                        'activo' => 1,
                        'aulas' => [
                            ['nombre' => 'CCIM-01', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'CCIM-02', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'CCIM-03', 'id_lugar' => 1, 'activo' => 1],
                        ],
                    ],
                ],
            ],
            [
                'nombre' => 'Edificio D',
                'activo' => 1,
                'pisos' => [
                    [
                        'piso' => 0,
                        'activo' => 1,
                        'aulas' => [
                            ['nombre' => 'D-01', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'D-02', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'D-03', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'D-CUB', 'id_lugar' => 3, 'activo' => 1],
                            ['nombre' => 'D-LCA', 'id_lugar' => 2, 'activo' => 1],
                        ],
                    ],
                    [
                        'piso' => 1,
                        'activo' => 1,
                        'aulas' => [
                            ['nombre' => 'D-11', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'D-12', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'D-13', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'D-14', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'D-15', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'D-16', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'D-17', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'D-18', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'D-LCAG-1', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'D-LCAG-2', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'D-LCAG-3', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'D-LCAG-4', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'D-LCAG-5', 'id_lugar' => 2, 'activo' => 1],
                        ],
                    ],
                    [
                        'piso' => 2,
                        'activo' => 1,
                        'aulas' => [
                            ['nombre' => 'D-21', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'D-22', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'D-23', 'id_lugar' => 1, 'activo' => 1],
                        ],
                    ],
                ],
            ],
            [
                'nombre' => 'Edificio E',
                'activo' => 1,
                'pisos' => [
                    [
                        'piso' => 0,
                        'activo' => 1,
                        'aulas' => [
                            ['nombre' => 'E-01', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'E-AM', 'id_lugar' => 1, 'activo' => 1],
                        ],
                    ],
                    [
                        'piso' => 1,
                        'activo' => 1,
                        'aulas' => [
                            ['nombre' => 'E-11', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'E-12', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'E-13', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'E-14', 'id_lugar' => 1, 'activo' => 1],
                        ],
                    ],
                    [
                        'piso' => 2,
                        'activo' => 1,
                        'aulas' => [
                            ['nombre' => 'E-21', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'E-22', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'E-23', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'E-24', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'E-25', 'id_lugar' => 1, 'activo' => 1],
                        ],
                    ],
                ],
            ],
            [
                'nombre' => 'Edificio F',
                'activo' => 1,
                'pisos' => [
                    [
                        'piso' => 0,
                        'activo' => 1,
                        'aulas' => [
                            ['nombre' => 'F-001', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'F-002', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'F-003', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'F-004', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'F-005', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'F-006', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'F-007', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'F-008', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'F-009', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'F-010', 'id_lugar' => 1, 'activo' => 1],
                        ],
                    ],
                    [
                        'piso' => 1,
                        'activo' => 1,
                        'aulas' => [
                            ['nombre' => 'F-101', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'F-102', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'F-103', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'F-104', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'F-105', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'F-106', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'F-107', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'F-108', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'F-109', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'F-110', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'F-111', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'F-112', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'F-113', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'F-114', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'F-115', 'id_lugar' => 1, 'activo' => 1],
                        ],
                    ],
                    [
                        'piso' => 2,
                        'activo' => 1,
                        'aulas' => [
                            ['nombre' => 'F-201', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'F-202', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'F-203', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'F-204', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'F-205', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'F-206', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'F-207', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'F-208', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'F-209', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'F-210', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'F-211', 'id_lugar' => 1, 'activo' => 1],
                        ],
                    ],
                ],
            ],
            [
                'nombre' => 'Edificio G',
                'activo' => 1,
                'pisos' => [
                    [
                        'piso' => 0,
                        'activo' => 1,
                        'aulas' => [
                            ['nombre' => 'G-01', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'G-02', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'G-03', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'G-04', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'G-05', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'G-06', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'G-07', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'G-08', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'G-09', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'G-LCA', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'G-LCCT-1', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'G-LCCT-2', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'G-LPALEO', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'G-LPETRO', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'G-SACT', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'G-CAMPO', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'G-LAB_CR', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'G-LAB_MIN', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'G-LCA-3', 'id_lugar' => 2, 'activo' => 1],
                        ],
                    ],
                    [
                        'piso' => 1,
                        'activo' => 1,
                        'aulas' => [
                            ['nombre' => 'G-10', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'G-11', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'G-12', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'G-13', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'G-14', 'id_lugar' => 1, 'activo' => 1],
                        ],
                    ],
                ],
            ],
            [
                'nombre' => 'Edificio I',
                'activo' => 1,
                'pisos' => [
                    [
                        'piso' => 0,
                        'activo' => 1,
                        'aulas' => [
                            ['nombre' => 'I-01', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'I-02', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'I-03', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'I-04', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'I-05', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'I-06', 'id_lugar' => 1, 'activo' => 1],
                        ],
                    ],
                    [
                        'piso' => 1,
                        'activo' => 1,
                        'aulas' => [
                            ['nombre' => 'I-11', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'I-12', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'I-13', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'I-14', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'I-15', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'I-16', 'id_lugar' => 1, 'activo' => 1],
                        ],
                    ],
                    [
                        'piso' => 2,
                        'activo' => 1,
                        'aulas' => [
                            ['nombre' => 'I-CUB', 'id_lugar' => 3, 'activo' => 1],
                            ['nombre' => 'I-LABS', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'I-LCA-1', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'I-LCA-2', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'I-LCB-1', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'I-LCB-2', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'I-LCB-3', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'I-LESD', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'I-SALA_J', 'id_lugar' => 3, 'activo' => 1],
                            ['nombre' => 'I-LCA-1-1', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'I-SI', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'I-LCA-3', 'id_lugar' => 2, 'activo' => 1],
                        ],
                    ],
                ],
            ],
            [
                'nombre' => 'Edificio H',
                'activo' => 1,
                'pisos' => [
                    [
                        'piso' => 0,
                        'activo' => 1,
                        'aulas' => [
                            ['nombre' => 'H-LCA-1', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'H-LRT-1', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'H-CCIM', 'id_lugar' => 1, 'activo' => 1],
                        ],
                    ],
                    [
                        'piso' => 1,
                        'activo' => 1,
                        'aulas' => [
                            ['nombre' => 'H-13', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'H-12', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'H-14', 'id_lugar' => 1, 'activo' => 1],
                        ],
                    ],
                    [
                        'piso' => 2,
                        'activo' => 1,
                        'aulas' => [
                            ['nombre' => 'H-25', 'id_lugar' => 3, 'activo' => 1],
                        ],
                    ],
                ],
            ],
            [
                'nombre' => 'Edificio L',
                'activo' => 1,
                'pisos' => [
                    [
                        'piso' => 0,
                        'activo' => 1,
                        'aulas' => [
                            ['nombre' => 'L-02', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'L-03', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'L-05', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'L-07', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'L-AUD', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'L-BEN', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'L-CICTD', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'L-LESD', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'L-06', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'L-INV', 'id_lugar' => 3, 'activo' => 1],
                        ],
                    ],
                    [
                        'piso' => 1,
                        'activo' => 1,
                        'aulas' => [
                            ['nombre' => 'L-12', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'L-16', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'L-17-1', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'L-17-2', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'L-18', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'L-19', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'L-LMAT', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'L-TOP', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'L-AST', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'L-17', 'id_lugar' => 1, 'activo' => 1],
                        ],
                    ],
                    [
                        'piso' => 2,
                        'activo' => 1,
                        'aulas' => [
                            ['nombre' => 'L-21', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'L-22', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'L-23', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'L-23-1', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'L-23-2', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'L-26', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'L-27', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'L-28', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'L-29', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'L-24', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'L-25', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'L-CSCA-1', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'L-CSCA-2', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'L-CSCA-3', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'L-CSCA-4', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'L-CSCA-5', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'L-20', 'id_lugar' => 1, 'activo' => 1],
                        ],
                    ],
                    [
                        'piso' => 3,
                        'activo' => 1,
                        'aulas' => [
                            ['nombre' => 'L-30', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'L-31', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'L-33', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'L-LCAG-1', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'L-LCAG-2', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'L-LCAG-3', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'L-LCAG-4', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'L-LCAG-5', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'L-LCAAC-1', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'L-LCAAC-2', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'L-LCAAC-3', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'L-LCAAC-4', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'L-LCAAC-5', 'id_lugar' => 2, 'activo' => 1],
                        ],
                    ],
                    [
                        'piso' => 4,
                        'activo' => 1,
                        'aulas' => [
                            ['nombre' => 'L-43', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'L-45', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'L-46', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'L-49', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'L-42', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'L-41', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'L-44', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'L-402', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'L-48', 'id_lugar' => 1, 'activo' => 1],
                        ],
                    ],
                ],
            ],
            [
                'nombre' => 'Torre de Ingeniería',
                'activo' => 1,
                'pisos' => [
                    [
                        'piso' => 0,
                        'activo' => 1,
                        'aulas' => [
                            ['nombre' => 'T-01', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'T-02', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'T-CUB', 'id_lugar' => 3, 'activo' => 1],
                            ['nombre' => 'T-EDIF', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'T-LCI', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'T-LHIDR', 'id_lugar' => 2, 'activo' => 1],
                            ['nombre' => 'T-SALA-CC', 'id_lugar' => 3, 'activo' => 1],
                        ],
                    ],
                    [
                        'piso' => 1,
                        'activo' => 1,
                        'aulas' => [
                            ['nombre' => 'T-101', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'T-102', 'id_lugar' => 1, 'activo' => 1],
                        ],
                    ],
                    [
                        'piso' => 2,
                        'activo' => 1,
                        'aulas' => [
                            ['nombre' => 'T-201', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'T-202', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'T-203', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'T-204', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'T-206', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'T-21', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'T-22', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'T-23', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'T-24', 'id_lugar' => 1, 'activo' => 1],
                        ],
                    ],
                    [
                        'piso' => 3,
                        'activo' => 1,
                        'aulas' => [
                            ['nombre' => 'T-301', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'T-31', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'T-32', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'T-33', 'id_lugar' => 1, 'activo' => 1],
                        ],
                    ],
                    [
                        'piso' => 4,
                        'activo' => 1,
                        'aulas' => [
                            ['nombre' => 'T-401', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'T-402', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'T-403', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'T-404', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'T-41', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'T-413', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'T-414', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'T-415', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'T-42', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'T-43', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'T-44', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'T-514', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'T-A414', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'T-A415', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'T-L401', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'T-L402', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'T-S413', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'T-S414', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'T-S514', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'T-504', 'id_lugar' => 1, 'activo' => 1],
                        ],
                    ],
                    [
                        'piso' => 5,
                        'activo' => 1,
                        'aulas' => [
                            ['nombre' => 'T-501', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'T-502', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'T-51', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'T-513', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'T-515', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'T-52', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'T-A502', 'id_lugar' => 1, 'activo' => 1],
                            ['nombre' => 'T-S502', 'id_lugar' => 1, 'activo' => 1],
                        ],
                    ],
                ],
            ],
            [
                'nombre' => 'Edificio IMET (Instituto de Metalurgia)',
                'activo' => 1,
                'pisos' => [
                    [
                        'piso' => 0,
                        'activo' => 1,
                        'aulas' => [
                            ['nombre' => 'IMET-EDIF', 'id_lugar' => 1, 'activo' => 1],
                        ],
                    ],
                ],
            ],
            [
                'nombre' => 'Laboratorio LAMAC',
                'activo' => 1,
                'pisos' => [
                    [
                        'piso' => 0,
                        'activo' => 1,
                        'aulas' => [
                            ['nombre' => 'LAMAC', 'id_lugar' => 2, 'activo' => 1],
                        ],
                    ],
                ],
            ],
            [
                'nombre' => 'Laboratorio de Geotecnia',
                'activo' => 1,
                'pisos' => [
                    [
                        'piso' => 0,
                        'activo' => 1,
                        'aulas' => [
                            ['nombre' => 'LAB-GEOT', 'id_lugar' => 2, 'activo' => 1],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($edificios as $dataEdificio) {
            $nuevoEdificio = Edificio::create([
                'nombre' => $dataEdificio['nombre'],
                'activo' => $dataEdificio['activo'] ?? 1,
            ]);

            foreach ($dataEdificio['pisos'] as $dataPiso) {
                $nuevoPiso = Piso::create([
                    'piso'        => $dataPiso['piso'],
                    'id_edificio' => $nuevoEdificio->id_edificio,
                    'activo'      => $dataPiso['activo'] ?? 1,
                ]);

                foreach ($dataPiso['aulas'] as $dataAula) {
                    Aula::create([
                        'nombre'   => $dataAula['nombre'],
                        'id_piso'  => $nuevoPiso->id_piso,
                        'id_lugar' => $dataAula['id_lugar'],
                        'activo'   => $dataAula['activo'] ?? 1,
                    ]);
                }
            }
        }

        // =========================================================================
        // 3. CREACIÓN DE SERVICIOS DE MANTENIMIENTO
        // =========================================================================
        $servicios = [
            ['nombre' => 'Electricidad', 'descripcion' => 'Fallas eléctricas'],
            ['nombre' => 'Plomería',     'descripcion' => 'Fugas de agua'],
            ['nombre' => 'Cerrajería',   'descripcion' => 'Reparación y cambio de cerraduras o chapas'],
            ['nombre' => 'Carpintería',  'descripcion' => 'Reparación y mantenimiento de puertas o muebles'],
            ['nombre' => 'Pintura',      'descripcion' => 'Mantenimiento de pintura en muros y techos'],
        ];

        foreach ($servicios as $servicio) {
            Servicio::firstOrCreate(['nombre' => $servicio['nombre']], $servicio);
        }

        // =========================================================================
        // 4. CREACIÓN DE UNIDADES DE MEDIDA
        // =========================================================================
        $unidades = [
            ['nombre' => 'Pieza',       'abreviatura' => 'pza'],
            ['nombre' => 'Metro',       'abreviatura' => 'm'],
            ['nombre' => 'Centímetro',  'abreviatura' => 'cm'],
            ['nombre' => 'Mililitro',   'abreviatura' => 'ml'],
            ['nombre' => 'Litro',       'abreviatura' => 'lt'],
            ['nombre' => 'Kilogramo',   'abreviatura' => 'kg'],
            ['nombre' => 'Gramo',       'abreviatura' => 'g'],
            ['nombre' => 'Juego',       'abreviatura' => 'jgo'],
            ['nombre' => 'Rollo',       'abreviatura' => 'rollo'],
            ['nombre' => 'Caja',        'abreviatura' => 'caja'],
        ];

        foreach ($unidades as $u) {
            Unidad::firstOrCreate(
                ['abreviatura' => $u['abreviatura']],
                ['nombre' => $u['nombre'], 'activo' => 1]
            );
        }
    }
}