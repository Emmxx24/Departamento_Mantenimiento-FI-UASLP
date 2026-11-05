<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>POA DMC {{ $poa->id_poa }}/{{ \Carbon\Carbon::parse($poa->fecha_poa)->format('y') }}</title>
    <style>
        @page {
            margin: 25mm 20mm 20mm 20mm;
            size: letter portrait;
        }
        * { box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #212529;
            margin: 0;
            padding: 0;
        }

        /* Encabezado */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .header-table td { vertical-align: top; padding: 2px 0; }
        .dmc-ref {
            text-align: right;
            font-size: 11px;
            font-weight: bold;
        }
        .fecha-ref {
            text-align: left;
            font-size: 11px;
        }
        .titulo-doc {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin: 10px 0 3px;
        }
        .subtitulo-doc {
            text-align: center;
            font-size: 10px;
            margin-bottom: 16px;
            color: #495057;
        }

        /* Tabla principal */
        .doc-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .doc-table td, .doc-table th {
            border: 1px solid #000;
            padding: 6px 10px;
            font-size: 10.5px;
        }
        .doc-table .label {
            font-weight: bold;
            width: 32%;
            background-color: #f0f4f8;
            color: #000;
        }
        .doc-table .value {
            width: 68%;
        }
        .doc-table .label-sm {
            font-size: 9px;
            font-weight: normal;
            display: block;
            color: #555;
        }
        .monto-value {
            font-size: 13px;
            font-weight: bold;
            background-color: #ffff00; /* amarillo oficial institucional */
        }
        .inline-pair {
            width: 100%;
        }
        .inline-pair td {
            border: none;
            padding: 0;
        }

        /* Firmas */
        .firmas-table {
            width: 100%;
            margin-top: 50px;
            border-collapse: collapse;
        }
        .firmas-table td {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            padding: 0 20px;
        }
        .linea-firma {
            border-bottom: 1px solid #000;
            width: 80%;
            margin: 0 auto 4px;
        }
        .firma-nombre {
            font-size: 10px;
            font-weight: bold;
        }
        .firma-cargo {
            font-size: 9px;
        }
    </style>
</head>
<body>

    {{-- Referencia y fecha --}}
    <table class="header-table">
        <tr>
            <td class="fecha-ref">{{ \Carbon\Carbon::parse($poa->fecha_poa)->format('d/m/Y') }}</td>
            <td class="dmc-ref">DMC &nbsp; {{ $poa->id_poa }}/{{ \Carbon\Carbon::parse($poa->fecha_poa)->format('y') }}</td>
        </tr>
    </table>

    <div class="titulo-doc">SOLICITUD DE COMPRA/SERVICIO</div>
    <div class="subtitulo-doc">DEPARTAMENTO DE MANTENIMIENTO Y CONSERVACION</div>

    <table class="doc-table">
        <tr>
            <td class="label">
                AREA: <span class="label-sm">(APLICACIÓN DEL RECURSO)</span>
            </td>
            <td class="value" style="font-weight:bold; text-align:center;">
                {{ mb_strtoupper($poa->area, 'UTF-8') }}
            </td>
        </tr>
        <tr>
            <td class="label">LABORATORIO/SUBAREA</td>
            <td class="value" style="text-align:center;">{{ mb_strtoupper($poa->subarea, 'UTF-8') }}</td>
        </tr>
        <tr>
            <td class="label">DESCRIPCION</td>
            <td class="value" style="text-align:center;">{{ mb_strtoupper($poa->descripcion, 'UTF-8') }}</td>
        </tr>
        <tr>
            <td class="label">CON CARGO A:</td>
            <td class="value">POA {{ now()->year }}, SUBUNIDAD, &nbsp; PROYECTO 3, &nbsp; OBJETIVO 10315</td>
        </tr>
        <tr>
            <td class="label">META:</td>
            <td class="value" style="text-align:center;">1</td>
        </tr>
        <tr>
            <td class="label">ACCION:</td>
            <td class="value"></td>
        </tr>
        <tr>
            <td class="label">RUBRO:</td>
            <td class="value">
                <table style="width:100%; border-collapse:collapse;">
                    <tr>
                        <td style="width:60%;"></td>
                        <td style="width:20%; font-weight:bold;">FONDO:</td>
                        <td style="width:20%; text-align:center;">111</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="label">FUNCION:</td>
            <td class="value">
                <table style="width:100%; border-collapse:collapse;">
                    <tr>
                        <td style="width:20%; text-align:center;">6</td>
                        <td style="width:10%;"></td>
                        <td style="width:30%; font-weight:bold;">ACTIVIDAD:</td>
                        <td style="width:20%; text-align:center;">1</td>
                        <td style="width:20%;"></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="label">MONTO APROXIMADO:</td>
            <td class="value">
                <table style="width:100%; border-collapse:collapse;">
                    <tr>
                        <td class="monto-value" style="width:50%; border:1px solid #000; padding:4px 8px;">
                            ${{ number_format($poa->monto, 2) }}
                        </td>
                        <td style="width:10%; text-align:center; font-weight:bold;">$</td>
                        <td style="width:40%; border:1px solid #000;"></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="label">OBSERVACIONES:</td>
            <td class="value" style="white-space:pre-line;">{{ mb_strtoupper($poa->observaciones ?? '', 'UTF-8') }}</td>
        </tr>
        <tr>
            <td class="label">NOMBRE DEL SOLICITANTE:</td>
            <td class="value" style="font-weight:bold;">{{ mb_strtoupper($poa->requisicion->solicitante ?? '', 'UTF-8') }}</td>
        </tr>
        <tr>
            <td class="label">CARGO:</td>
            <td class="value">JEFE DEPARTAMENTO MANTENIMIENTO Y CONSERVACION</td>
        </tr>
    </table>

    {{-- Área Vo.Bo. --}}
    <div style="font-size:10px; font-weight:bold; margin-bottom:4px;">AREA: Vo.Bo</div>

    {{-- Firmas --}}
    <table class="firmas-table">
        <tr>
            <td>
                <div class="linea-firma"></div>
                <div class="firma-nombre">{{ mb_strtoupper($poa->requisicion->solicitante ?? '', 'UTF-8') }}</div>
                <div class="firma-cargo">DEPTO. MANTTO.</div>
            </td>
            <td>
                <div class="linea-firma"></div>
                <div class="firma-nombre"></div>
                <div class="firma-cargo">DIRECTOR DE LA FACULTAD</div>
            </td>
        </tr>
    </table>

</body>
</html>