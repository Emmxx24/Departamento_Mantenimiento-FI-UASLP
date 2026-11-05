@extends('layouts.app')

@section('title', 'Requisición #' . $req->id_requisicion)

@section('content')

<div class="container my-4">

    {{-- Barra de acciones superior --}}
    <x-page-title titulo="Detalle Requisición #{{ $req->id_requisicion }}" subtitulo="Revisa los artículos y genera el P.O.A." />

    <div class="d-flex justify-content-between align-items-start align-items-md-center flex-wrap gap-2 mb-4 d-print-none">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('requisicion.admin', $req->ticket->id_ticket) }}"
               class="btn btn-outline-secondary rounded-pill px-4 fw-bold shadow-sm">
                <i class="bi bi-arrow-left me-1"></i> Regresar
            </a>

            @if($req->es_borrador)
                <span class="badge bg-warning text-dark rounded-pill px-3 py-2 fw-semibold">
                    <i class="bi bi-pencil-fill me-1"></i> Borrador
                </span>
            @endif
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('requisicion.pdf', $req->id_requisicion) }}"
               class="btn btn-sm text-white rounded-pill px-3 fw-bold shadow-sm" style="background-color:#E3000F;"
               target="_blank">
                <i class="bi bi-file-earmark-pdf me-1"></i> PDF
            </a>
            <a href="{{ route('requisicion.excel', $req->id_requisicion) }}"
               class="btn btn-sm text-white rounded-pill px-3 fw-bold shadow-sm" style="background-color:#217346;">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Excel
            </a>


            {{-- Ver POA si existe --}}
            @if($req->poa)
                <a href="{{ route('poa.show', $req->poa->id_poa) }}"
                   class="btn btn-sm btn-primary text-white rounded-pill px-3 fw-bold shadow-sm">
                    <i class="bi bi-file-earmark-check me-1"></i> Ver POA
                </a>
            @elseif(!isset($ticket) || ($ticket->estado != 'Cerrado' && $ticket->estado != 'Terminado'))
                <form autocomplete="off" action="{{ route('requisicion.generarPoa', $req->id_requisicion) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success text-white rounded-pill px-3 fw-bold shadow-sm">
                        <i class="bi bi-plus-circle me-1"></i> Generar POA
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4">

            {{-- ===== Encabezado del documento ===== --}}
            <div class="row mb-3 align-items-start">
                <div class="col-md-6">
                    <div class="fw-bold fs-6">Departamento de Mantenimiento</div>
                    <div class="fw-bold fs-6">Facultad de Ingeniería</div>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="fw-bold fs-5">Requisición</div>
                    <div class="text-muted" style="font-size:0.9rem;">Fecha: <strong>{{ \Carbon\Carbon::parse($req->fecha_req)->format('d/m/Y') }}</strong></div>
                    <div class="text-muted" style="font-size:0.9rem;">ID: <strong>#{{ $req->id_requisicion }}</strong></div>
                    @if($req->poa)
                        <div class="text-muted" style="font-size:0.9rem;">POA: <strong>DMC {{ $req->poa->id_poa }}/{{ now()->year % 100 }}</strong></div>
                    @endif
                </div>
            </div>

            {{-- ===== Tabla de partidas (16 renglones fijos con altura institucional) ===== --}}
            <div class="table-responsive mb-3">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light text-center small align-middle">
                        <tr>
                            <th style="width:40px;">No.</th>
                            <th style="width:60px;">Cant.</th>
                            <th style="width:60px;">Un.</th>
                            <th style="min-width:140px; text-align:left;">Nombre</th>
                            <th style="width:70px;">Clave</th>
                            <th style="min-width:180px; text-align:left;">Especificaciones</th>
                            <th style="width:90px;">Marca</th>
                            <th style="width:70px;">Rubro</th>
                            <th style="width:85px;">Unitario</th>
                            <th style="width:85px;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody style="font-size:0.84rem;">
                        @foreach($req->materiales as $item)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td class="text-center">{{ $item->cantidad }}</td>
                                <td class="text-center">{{ $item->unidad }}</td>
                                <td>{{ $item->material ? $item->material->nombre : $item->nombre_libre }}</td>
                                <td class="text-center text-muted fst-italic" style="font-size:0.75rem;">—</td>
                                <td>{{ $item->especificaciones }}</td>
                                <td class="text-center">{{ $item->marca_texto }}</td>
                                <td class="text-center text-muted fst-italic" style="font-size:0.75rem;">—</td>
                                <td class="text-center">${{ number_format($item->unitario, 2) }}</td>
                                <td class="text-center">${{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                        @endforeach

                        {{-- Renglones vacíos hasta completar 16 --}}
                        @for($i = $req->materiales->count(); $i < 16; $i++)
                            <tr class="text-muted">
                                <td class="text-center">{{ $i + 1 }}</td>
                                <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                                <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                                <td class="text-center">0.00</td>
                                <td class="text-center">0.00</td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>

            {{-- ===== Pie del documento ===== --}}
            <div class="table-responsive">
            <table class="table table-bordered mb-3" style="font-size:0.85rem; min-width:600px;">
                <tbody>
                    <tr>
                        <th style="width:120px; background:#f8f9fa;">Proveedor:</th>
                        <td colspan="2">{{ $req->proveedor }}</td>
                        <th style="width:100px; background:#f8f9fa;">Solicita:</th>
                        <td colspan="2" class="fw-bold">{{ $req->admin ? ($req->admin->nombre . ' ' . $req->admin->ap_pat . ' ' . $req->admin->ap_mat) : ($req->solicitante ?? '—') }}</td>
                        <th style="width:90px; background:#f8f9fa;">Subtotal:</th>
                        <td class="fw-bold text-end">${{ number_format($req->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <th style="background:#f8f9fa;">Cotización:</th>
                        <td colspan="2" class="fw-bold">{{ $req->cotizacion }}</td>
                        <th style="background:#f8f9fa;">Ticket:</th>
                        <td colspan="2">#{{ $req->ticket->id_ticket ?? '—' }}</td>
                        <th style="background:#f8f9fa;">I.V.A. 16%:</th>
                        <td class="fw-bold text-end">${{ number_format($req->subtotal * 0.16, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="6"></td>
                        <th style="background:#f8f9fa;">Total:</th>
                        <td class="fw-bold text-end">${{ number_format($req->total, 2) }}</td>
                    </tr>
                </tbody>
            </table>
            </div>{{-- /table-responsive --}}

            <div class="text-muted" style="font-size:0.78rem;">
                Uso: OFICINA DE MANTENIMIENTO Y CONSERVACIÓN
            </div>

        </div>
    </div>
</div>
@endsection

