<table>
    <thead>
        <tr>
            <th colspan="6" style="text-align: left; font-weight: bold; font-size: 13px;">Departamento de Mantenimiento</th>
            <th colspan="4" style="text-align: right; font-weight: bold; font-size: 14px;">Requisición</th>
        </tr>
        <tr>
            <th colspan="6" style="text-align: left; font-weight: bold; font-size: 11px;">Facultad de Ingeniería</th>
            <th colspan="4" style="text-align: right; font-weight: bold;">Fecha: {{ \Carbon\Carbon::parse($req->fecha_req)->format('d/m/Y') }}</th>
        </tr>
        <tr>
            <th colspan="6"></th>
            <th colspan="4" style="text-align: right; font-weight: bold;">ID: #{{ $req->id_requisicion }}</th>
        </tr>
        @if($req->poa)
        <tr>
            <th colspan="6"></th>
            <th colspan="4" style="text-align: right; font-weight: bold;">POA: DMC {{ $req->poa->id_poa }}/{{ \Carbon\Carbon::parse($req->fecha_req)->format('y') }}</th>
        </tr>
        @endif
        <tr>
            <th style="font-weight: bold; background-color: #f0f4f8; text-align: center; border: 1px solid #000;">No.</th>
            <th style="font-weight: bold; background-color: #f0f4f8; text-align: center; border: 1px solid #000;">Cant.</th>
            <th style="font-weight: bold; background-color: #f0f4f8; text-align: center; border: 1px solid #000;">Un.</th>
            <th style="font-weight: bold; background-color: #f0f4f8; text-align: left; border: 1px solid #000;">Nombre</th>
            <th style="font-weight: bold; background-color: #f0f4f8; text-align: center; border: 1px solid #000;">Clave</th>
            <th style="font-weight: bold; background-color: #f0f4f8; text-align: left; border: 1px solid #000;">Especificaciones</th>
            <th style="font-weight: bold; background-color: #f0f4f8; text-align: center; border: 1px solid #000;">Marca</th>
            <th style="font-weight: bold; background-color: #f0f4f8; text-align: center; border: 1px solid #000;">Rubro</th>
            <th style="font-weight: bold; background-color: #f0f4f8; text-align: right; border: 1px solid #000;">Unitario</th>
            <th style="font-weight: bold; background-color: #f0f4f8; text-align: right; border: 1px solid #000;">Subtotal</th>
        </tr>
    </thead>
    <tbody>
        @php $partida = 1; @endphp
        @foreach($req->materiales as $item)
            <tr>
                <td style="text-align: center; border: 1px solid #000;">{{ $partida++ }}</td>
                <td style="text-align: center; border: 1px solid #000;">{{ $item->cantidad }}</td>
                <td style="text-align: center; border: 1px solid #000;">{{ $item->unidad }}</td>
                <td style="border: 1px solid #000;">{{ $item->material ? $item->material->nombre : $item->nombre_libre }}</td>
                <td style="border: 1px solid #000;"></td>
                <td style="border: 1px solid #000;">{{ $item->especificaciones }}</td>
                <td style="text-align: center; border: 1px solid #000;">{{ $item->marca_texto }}</td>
                <td style="border: 1px solid #000;"></td>
                <td style="text-align: right; border: 1px solid #000;">${{ number_format($item->unitario, 2) }}</td>
                <td style="text-align: right; border: 1px solid #000;">${{ number_format($item->subtotal, 2) }}</td>
            </tr>
        @endforeach
        @for($i = $req->materiales->count(); $i < 16; $i++)
            <tr>
                <td style="text-align: center; border: 1px solid #000;">{{ $i + 1 }}</td>
                <td style="border: 1px solid #000;"></td>
                <td style="border: 1px solid #000;"></td>
                <td style="border: 1px solid #000;"></td>
                <td style="border: 1px solid #000;"></td>
                <td style="border: 1px solid #000;"></td>
                <td style="border: 1px solid #000;"></td>
                <td style="border: 1px solid #000;"></td>
                <td style="border: 1px solid #000;"></td>
                <td style="border: 1px solid #000;"></td>
            </tr>
        @endfor
    </tbody>
    <tfoot>
        <tr>
            <th style="font-weight: bold; background-color: #f0f4f8; border: 1px solid #000;">Proveedor:</th>
            <td colspan="3" style="border: 1px solid #000;">{{ $req->proveedor }}</td>
            <th style="font-weight: bold; background-color: #f0f4f8; border: 1px solid #000;">Solicita:</th>
            <td colspan="2" style="font-weight: bold; border: 1px solid #000;">{{ $req->admin ? ($req->admin->nombre . ' ' . $req->admin->ap_pat . ' ' . $req->admin->ap_mat) : ($req->solicitante ?? '—') }}</td>
            <th colspan="2" style="text-align: right; font-weight: bold; background-color: #f0f4f8; border: 1px solid #000;">Subtotal:</th>
            <td style="text-align: right; font-weight: bold; border: 1px solid #000;">${{ number_format($req->subtotal, 2) }}</td>
        </tr>
        <tr>
            <th style="font-weight: bold; background-color: #f0f4f8; border: 1px solid #000;">Cotización:</th>
            <td colspan="3" style="font-weight: bold; border: 1px solid #000;">{{ $req->cotizacion }}</td>
            <th style="font-weight: bold; background-color: #f0f4f8; border: 1px solid #000;">Ticket:</th>
            <td colspan="2" style="border: 1px solid #000;">#{{ $req->ticket->id_ticket ?? $req->id_ticket ?? '—' }}</td>
            <th colspan="2" style="text-align: right; font-weight: bold; background-color: #f0f4f8; border: 1px solid #000;">I.V.A. 16%:</th>
            <td style="text-align: right; font-weight: bold; border: 1px solid #000;">${{ number_format($req->subtotal * 0.16, 2) }}</td>
        </tr>
        <tr>
            <td colspan="7"></td>
            <th colspan="2" style="text-align: right; font-weight: bold; background-color: #f0f4f8; border: 1px solid #000;">Total:</th>
            <td style="text-align: right; font-weight: bold; border: 1px solid #000;">${{ number_format($req->total, 2) }}</td>
        </tr>
        <tr></tr>
        <tr>
            <td colspan="10" style="font-size: 10px; color: #555555;">
                Uso: OFICINA DE MANTENIMIENTO Y CONSERVACIÓN
            </td>
        </tr>
        <tr></tr>
        <tr></tr>
        <tr>
            <td colspan="3" style="text-align: center; font-weight: bold;">{{ $req->admin ? ($req->admin->nombre . ' ' . $req->admin->ap_pat . ' ' . $req->admin->ap_mat) : ($req->solicitante ?? '—') }}</td>
            <td></td>
            <td colspan="2" style="text-align: center;"></td>
            <td></td>
            <td colspan="3" style="text-align: center;"></td>
        </tr>
        <tr>
            <td colspan="3" style="text-align: center; border-top: 1px solid #000;">Solicitante</td>
            <td></td>
            <td colspan="2" style="text-align: center; border-top: 1px solid #000;">Vo. Bo.</td>
            <td></td>
            <td colspan="3" style="text-align: center; border-top: 1px solid #000;">Autorización</td>
        </tr>
    </tfoot>
</table>
