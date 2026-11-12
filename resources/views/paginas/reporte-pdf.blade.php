<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">

<style>
    body {
        font-family: DejaVu Sans, sans-serif;
        color: #2b2b2b;
        margin: 30px;
        font-size: 12px;
    }

    .header-table {
        width: 100%;
        border: none;
        margin-bottom: 20px;
    }

    .header-table td {
        border: none;
        vertical-align: middle;
    }

    .titulo {
        text-align: center;
    }

    .titulo h1 {
        margin: 0;
        font-size: 20px;
        color: #004A98;
    }

    .titulo h2 {
        margin: 4px 0 0;
        font-size: 15px;
        font-weight: normal;
        color: #555;
    }

    .info-box {
        width: 100%;
        margin-top: 10px;
        margin-bottom: 20px;
        border: 1px solid #d9d9d9;
        border-radius: 6px;
    }

    .info-box td {
        border: none;
        padding: 8px 12px;
        font-size: 10px;
    }

    .info-label {
        font-weight: bold;
        color: #004A98;
    }

    .resumen {
        margin-bottom: 20px;
    }

    .resumen table {
        width: 100%;
        border-collapse: collapse;
    }

    .resumen td {
        width: 25%;
        text-align: center;
        padding: 12px;
        border: 1px solid #ddd;
    }

    .resumen .numero {
        display: block;
        font-size: 18px;
        font-weight: bold;
        color: #004A98;
        margin-top: 4px;
    }

    .tabla-tickets {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    .tabla-tickets th {
        background-color: #004A98;
        color: white;
        padding: 10px 6px;
        font-size: 11px;
        text-transform: uppercase;
        border: 1px solid #00376f;
    }

    .tabla-tickets td {
        border: 1px solid #ddd;
        padding: 8px 6px;
        font-size: 10px;
    }

    .tabla-tickets tr:nth-child(even) {
        background-color: #f5f7fa;
    }

    .estado {
        font-weight: bold;
    }

    .footer {
        margin-top: 25px;
        text-align: center;
        font-size: 10px;
        color: #777;
    }

    hr {
        border: none;
        border-top: 2px solid #004A98;
        margin-top: 10px;
        margin-bottom: 20px;
    }
</style>
</head>

<body>

{{-- ENCABEZADO --}}
<table class="header-table">
    <tr>
        <td width="20%" style="text-align:left;">
            <img src="{{ public_path('Images/uaslp.png') }}" width="85">
        </td>

        <td width="60%" class="titulo">
            <h1>Universidad Autónoma de San Luis Potosí</h1>
            <h2>Reporte General de Tickets</h2>
        </td>

        <td width="20%" style="text-align:right;">
            <img src="{{ public_path('Images/escudo-fac-ing.png') }}" width="85">
        </td>
    </tr>
</table>

<hr>

{{-- INFORMACIÓN GENERAL --}}
<table class="info-box">
    <tr>
        <td>
            <span class="info-label">Fecha de generación:</span><br>
            {{ now()->format('d/m/Y H:i') }}
        </td>

        <td>
            <span class="info-label">Periodo:</span><br>
            {{ $periodoInicio }}
            -
            {{ $periodoFin }}
        </td>
    </tr>
</table>

{{-- RESUMEN --}}
<div class="resumen">
    <table>
        <tr>
            <td>
                Total
                <span class="numero">{{ $total }}</span>
            </td>

            <td>
                Atendidos
                <span class="numero">{{ $atendidos }}</span>
            </td>

            <td>
                Cerrados
                <span class="numero">{{ $cerrados }}</span>
            </td>

            <td>
                En proceso
                <span class="numero">{{ $enProceso }}</span>
            </td>
        </tr>
    </table>
</div>

{{-- TABLA PRINCIPAL --}}
<table class="tabla-tickets">
    <thead>
        <tr>
            <th width="5%">ID</th>
            <th width="22%">Asunto</th>
            <th width="11%">Estado</th>
            <th width="10%">Prioridad</th>
            <th width="12%">Aula</th>
            <th width="14%">Solicitante</th>
            <th width="14%">Admin</th>
            <th width="12%">Fecha</th>
        </tr>
    </thead>

    <tbody>
        @foreach($tickets as $t)
        <tr>
            <td>{{ $t->id_ticket }}</td>

            <td>
                {{ $t->asunto }}
            </td>

            <td class="estado">
                {{ $t->estado }}
            </td>

            <td>
                {{ $t->prioridad }}
            </td>

            <td>
                {{ $t->lugar->nombre ?? 'N/A' }}
            </td>

            <td>
                {{ $t->solicitante->nombre ?? 'N/A' }}
            </td>

            <td>
                {{ $t->adminAtiende->nombre ?? 'Sin asignar' }}
            </td>

            <td>
                {{ $t->fecha_creacion
                    ? \Carbon\Carbon::parse($t->fecha_creacion)->format('d/m/Y')
                    : 'N/A' }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="footer">
    Departamento de Mantenimiento · Facultad de Ingeniería UASLP
</div>

</body>
</html>