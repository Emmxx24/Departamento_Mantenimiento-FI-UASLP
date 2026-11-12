<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Requisición #{{ $req->id_requisicion }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        
        .header-table {
            width: 100%;
            margin-bottom: 60px;
        }
        .header-table td {
            vertical-align: top;
        }
        
        .req-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 60px;
        }
        .req-table th, .req-table td {
            height: 20px;
            border: 1px solid #000;
            padding: 5px 4px;
            text-align: center;
            height: 22px;
        }
        .req-table th {
            background-color: #f0f4f8;
            color: #000;
            font-size: 10px;
            font-weight: bold;
        }
        .req-table .td-left {
            text-align: left;
        }
        .req-table .fila-vacia td {
            color: transparent;
        }
        
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .footer-table th, .footer-table td {
            border: 1px solid #000;
            padding: 5px;
        }
        .footer-table th {
            background-color: #f0f4f8;
            text-align: right;
            font-size: 10px;
        }
        
        .firmas-table {
            width: 100%;
            margin-top: 50px;
            text-align: center;
        }
        .firmas-table td {
            width: 33%;
            vertical-align: bottom;
        }
        .linea-firma {
            border-bottom: 1px solid #000;
            width: 80%;
            margin: 0 auto 5px auto;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td style="width: 50%;">
                <h3 class="font-bold" style="margin:0 0 5px 0;">Departamento de Mantenimiento</h3>
                <h4 class="font-bold" style="margin:0;">Facultad de Ingeniería</h4>
            </td>
            <td style="width: 50%;" class="text-right">
                <h2 class="font-bold" style="margin:0 0 5px 0;">Requisición</h2>
                <div>Fecha: <span class="font-bold">{{ \Carbon\Carbon::parse($req->fecha_req)->format('d/m/Y') }}</span></div>
                <div>ID: <span class="font-bold">#{{ $req->id_requisicion }}</span></div>
                @if($req->poa)
                    <div>POA: <span class="font-bold">DMC {{ $req->poa->id_poa }}/{{ \Carbon\Carbon::parse($req->fecha_req)->format('y') }}</span></div>
                @endif
            </td>
        </tr>
    </table>

    <table class="req-table">
        <thead>
            <tr>
                <th style="width: 5%;">No.</th>
                <th style="width: 6%;">Cant.</th>
                <th style="width: 6%;">Un.</th>
                <th style="width: 18%;">Nombre</th>
                <th style="width: 8%;">Clave</th>
                <th style="width: 25%;">Especificaciones</th>
                <th style="width: 10%;">Marca</th>
                <th style="width: 8%;">Rubro</th>
                <th style="width: 7%;">Unitario</th>
                <th style="width: 7%;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php $partida = 1; @endphp
            @foreach($req->materiales as $item)
                <tr>
                    <td>{{ $partida++ }}</td>
                    <td>{{ $item->cantidad }}</td>
                    <td>{{ $item->unidad }}</td>
                    <td class="td-left">{{ $item->material ? $item->material->nombre : $item->nombre_libre }}</td>
                    <td></td>
                    <td class="td-left">{{ $item->especificaciones }}</td>
                    <td>{{ $item->marca_texto }}</td>
                    <td></td>
                    <td>${{ number_format($item->unitario, 2) }}</td>
                    <td>${{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach

            @for($i = $req->materiales->count(); $i < 16; $i++)
                <tr class="fila-vacia">
                    <td>{{ $i + 1 }}</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            @endfor
        </tbody>
    </table>

    <table class="footer-table">
        <tr>
            <th style="width: 12%;">Proveedor:</th>
            <td style="width: 33%;">{{ $req->proveedor }}</td>
            <th style="width: 10%;">Solicita:</th>
            <td style="width: 25%;" class="font-bold">{{ $req->admin->nombre . " " . $req->admin->ap_pat . " " . $req->admin->ap_mat}}</td>
            <th style="width: 10%;">Subtotal:</th>
            <td style="width: 10%;" class="text-right font-bold">${{ number_format($req->subtotal, 2) }}</td>
        </tr>
        <tr>
            <th>Cotización:</th>
            <td class="font-bold">{{ $req->cotizacion }}</td>
            <th>Ticket:</th>
            <td>#{{ $req->ticket->id_ticket ?? '—' }}</td>
            <th>I.V.A. 16%:</th>
            <td class="text-right font-bold">${{ number_format($req->subtotal * 0.16, 2) }}</td>
        </tr>
        <tr>
            <td colspan="4" style="border: none;"></td>
            <th>Total:</th>
            <td class="text-right font-bold">${{ number_format($req->total, 2) }}</td>
        </tr>
    </table>

    <div style="font-size: 10px; color: #555;">
        Uso: OFICINA DE MANTENIMIENTO Y CONSERVACIÓN
    </div>

    <table class="firmas-table">
        <tr>
            <td>
                <div class="linea-firma"></div>
                <strong>{{ $req->solicitante }}</strong><br>
                Solicitante
            </td>
            <td>
                <div class="linea-firma"></div>
                <br>
                Vo. Bo.
            </td>
            <td>
                <div class="linea-firma"></div>
                <br>
                Autorización
            </td>
        </tr>
    </table>

</body>
</html>
