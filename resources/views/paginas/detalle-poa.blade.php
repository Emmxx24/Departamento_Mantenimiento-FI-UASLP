@extends('layouts.app')

@section('title', 'POA #' . $poa->id_poa)

@section('content')

<div class="container my-4">

    {{-- Barra superior de título y navegación --}}
    <x-page-title titulo="Detalle P.O.A. #{{ $poa->id_poa }}" subtitulo="Plan Operativo Anual vinculado a la Requisición #{{ $poa->id_requisicion }}" />

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 d-print-none">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('requisicion.show', $poa->id_requisicion) }}"
               class="btn btn-outline-secondary rounded-pill px-4 fw-bold shadow-sm">
                <i class="bi bi-arrow-left me-1"></i> Regresar
            </a>

        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('poa.edit', $poa->id_poa) }}"
               class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold shadow-sm">
                <i class="bi bi-pencil-square me-1"></i> Editar Observaciones
            </a>
            <a href="{{ route('poa.pdf', $poa->id_poa) }}"
               class="btn btn-sm text-white rounded-pill px-3 fw-bold shadow-sm" style="background-color:#E3000F;"
               target="_blank">
                <i class="bi bi-file-earmark-pdf me-1"></i> PDF
            </a>
        </div>
    </div>

    {{-- Hoja del Documento --}}
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4 p-md-5">

            {{-- Encabezado del documento --}}
            <div class="row mb-4 align-items-start pb-3 border-bottom">
                <div class="col-md-7">
                    <div class="fw-bold fs-6">Departamento de Mantenimiento y Conservación</div>
                    <div class="fw-bold fs-5 text-dark mt-1">Solicitud de Compra / Servicio</div>
                    <div class="text-muted small">Facultad de Ingeniería — UASLP</div>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0">
                    <div class="fw-bold fs-5">P.O.A.</div>
                    <div class="text-muted" style="font-size:0.88rem;">
                        Folio: <strong>DMC {{ $poa->id_poa }}/{{ \Carbon\Carbon::parse($poa->fecha_poa)->format('y') }}</strong>
                    </div>
                    <div class="text-muted" style="font-size:0.88rem;">
                        Fecha: <strong>{{ \Carbon\Carbon::parse($poa->fecha_poa)->format('d/m/Y') }}</strong>
                    </div>
                    <div class="text-muted" style="font-size:0.88rem;">
                        Requisición: <strong>#{{ $poa->id_requisicion }}</strong>
                    </div>
                </div>
            </div>

            {{-- Tabla con los campos del documento --}}
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle mb-0">
                    <tbody>
                        <tr>
                            <td class="table-light fw-bold text-dark" style="width: 260px;">
                                ÁREA: <br>
                                <small class="fw-normal text-muted" style="font-size:0.75rem;">(Aplicación del recurso)</small>
                            </td>
                            <td class="fw-bold text-uppercase">{{ $poa->area }}</td>
                        </tr>
                        <tr>
                            <td class="table-light fw-bold text-dark">LABORATORIO / SUBÁREA</td>
                            <td class="text-uppercase">{{ $poa->subarea }}</td>
                        </tr>
                        <tr>
                            <td class="table-light fw-bold text-dark">DESCRIPCIÓN</td>
                            <td class="text-uppercase">{{ $poa->descripcion }}</td>
                        </tr>
                        <tr>
                            <td class="table-light fw-bold text-dark">CON CARGO A:</td>
                            <td class="text-uppercase">POA {{ now()->year }}, SUBUNIDAD, PROYECTO 3, OBJETIVO 10315</td>
                        </tr>
                        <tr>
                            <td class="table-light fw-bold text-dark">META:</td>
                            <td>1</td>
                        </tr>
                        <tr>
                            <td class="table-light fw-bold text-dark">ACCIÓN:</td>
                            <td class="text-muted fst-italic">—</td>
                        </tr>
                        <tr>
                            <td class="table-light fw-bold text-dark">RUBRO:</td>
                            <td class="p-0">
                                <div class="d-flex justify-content-between align-items-center px-3 py-2">
                                    <span class="text-muted fst-italic">—</span>
                                    <span>FONDO: &nbsp;<strong>111</strong></span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="table-light fw-bold text-dark">FUNCIÓN:</td>
                            <td class="p-0">
                                <div class="d-flex justify-content-between align-items-center px-3 py-2">
                                    <span><strong>6</strong></span>
                                    <span>ACTIVIDAD: &nbsp;<strong>1</strong></span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="table-light fw-bold text-dark">MONTO APROXIMADO:</td>
                            <td class="fw-bold text-dark" style="font-size:1.05rem;">
                                ${{ number_format($poa->monto, 2) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="table-light fw-bold text-dark">OBSERVACIONES:</td>
                            <td class="text-uppercase" style="white-space:pre-line;">{{ $poa->observaciones ?: '—' }}</td>
                        </tr>
                        <tr>
                            <td class="table-light fw-bold text-dark">NOMBRE DEL SOLICITANTE:</td>
                            <td class="fw-bold text-uppercase">{{ $poa->requisicion->solicitante ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="table-light fw-bold text-dark">CARGO:</td>
                            <td class="text-uppercase">JEFE DEPARTAMENTO MANTENIMIENTO Y CONSERVACIÓN</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Firmas Institucionales --}}
            <div class="row mt-5 pt-3 text-center">
                <div class="col-md-6 mb-4 mb-md-0">
                    <div style="border-top: 1px solid #000; width:75%; margin: 0 auto 8px;"></div>
                    <div class="fw-bold text-uppercase" style="font-size:0.85rem;">{{ $poa->requisicion->solicitante ?? '—' }}</div>
                    <div class="text-muted" style="font-size:0.78rem;">DEPTO. MANTTO. Y CONSERVACIÓN</div>
                </div>
                <div class="col-md-6">
                    <div style="border-top: 1px solid #000; width:75%; margin: 0 auto 8px;"></div>
                    <div class="fw-bold" style="font-size:0.85rem;">&nbsp;</div>
                    <div class="text-muted" style="font-size:0.78rem;">DIRECTOR DE LA FACULTAD</div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection