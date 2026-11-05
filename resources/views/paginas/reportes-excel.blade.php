<table>
    <thead>
        <tr>
            <th colspan="12" style="text-align:center; font-weight:bold;">
                Departamento de Mantenimiento
            </th>
        </tr>
        <tr>
            <th colspan="12" style="text-align:center; font-size:16px;">
                Reporte #{{ $reporte->id_reporte }}
            </th>
        </tr>
        <tr>
            <th colspan="6">
                Fecha: {{ \Carbon\Carbon::parse($reporte->fecha_rep)->format('d/m/Y') }}
            </th>
            <th colspan="6" style="text-align:right;">
                Total tickets: {{ $tickets->count() }}
            </th>
        </tr>
        <tr>
    <th colspan="12">Total: {{ $tickets->count() }}</th>
</tr>
<tr>
    <th colspan="12">Tiempo promedio: {{ round($promedio,2) }} hrs</th>
</tr>
        <tr>
            <th>ID</th>
            <th>Asunto</th>
            <th>Estado</th>
            <th>Prioridad</th>
            <th>Solicitante</th>
            <th>Administrador</th>
            <th>Edificio</th>
            <th>Aula</th>
            <th>Lugar</th>
            <th>Servicio</th>
            <th>Creación</th>
            <th>Cierre</th>
        </tr>
    </thead>

    <tbody>
        @foreach($tickets as $t)
            <tr>
                <td>{{ $t->id_ticket }}</td>
                <td>{{ $t->asunto }}</td>
                <td>{{ $t->estado }}</td>
                <td>{{ $t->prioridad }}</td>
                <td>{{ $t->solicitante->nombre ?? '' }}</td>
                <td>{{ $t->adminAtiende->nombre ?? 'Sin asignar' }}</td>
                <td>{{ $t->edificio->nombre ?? '' }}</td>
                <td>{{ $t->lugar->nombre ?? 'N/A' }}</td>
                <td>{{ $t->lugar->lugar->nombre ?? '' }}</td>
                <td>{{ $t->servicio->nombre ?? '' }}</td>
                <td>{{ $t->fecha_creacion }}</td>
                <td>{{ $t->fecha_cierre }}</td>
            </tr>
        @endforeach
    </tbody>
</table>