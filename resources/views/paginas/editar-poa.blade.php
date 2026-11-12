@extends('layouts.app')

@section('title', 'Editar POA #' . $poa->id_poa)

@section('content')

    <div class="container my-4">

        {{-- Barra superior de título y navegación --}}
        <x-page-title titulo="Editar P.O.A. #{{ $poa->id_poa }}"
            subtitulo="Derivado de la Requisición #{{ $poa->requisicion->id_requisicion }}" />

        <div class="d-flex align-items-center gap-2 mb-4 d-print-none">
            <a href="{{ route('poa.show', $poa->id_poa) }}"
                class="btn btn-outline-secondary rounded-pill px-4 fw-bold shadow-sm">
                <i class="bi bi-arrow-left me-1"></i> Regresar al Detalle
            </a>
        </div>

        {{-- Hoja del Documento editable --}}
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4 p-md-5">

                <form autocomplete="off" action="{{ route('poa.update', $poa->id_poa) }}" method="POST">
                    @csrf
                    @method('PUT')

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
                                Folio: <strong>DMC
                                    {{ $poa->id_poa }}/{{ \Carbon\Carbon::parse($poa->fecha_poa)->format('y') }}</strong>
                            </div>
                            <div class="text-muted" style="font-size:0.88rem;">
                                Fecha: <strong>{{ \Carbon\Carbon::parse($poa->fecha_poa)->format('d/m/Y') }}</strong>
                            </div>
                            <div class="text-muted" style="font-size:0.88rem;">
                                Requisición: <strong>#{{ $poa->id_requisicion }}</strong>
                            </div>
                        </div>
                    </div>

                    {{-- Tabla con los campos --}}
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle mb-0">
                            <tbody>
                                <tr>
                                    <td class="table-light fw-bold text-dark" style="width: 260px;">
                                        ÁREA: <br>
                                        <small class="fw-normal text-muted" style="font-size:0.75rem;">(Aplicación del
                                            recurso)</small>
                                    </td>
                                    <td class="fw-bold text-uppercase text-secondary">{{ $poa->area }}</td>
                                </tr>
                                <tr>
                                    <td class="table-light fw-bold text-dark">LABORATORIO / SUBÁREA</td>
                                    <td class="text-uppercase text-secondary">{{ $poa->subarea }}</td>
                                </tr>
                                <tr>
                                    <td class="table-light fw-bold text-dark">DESCRIPCIÓN</td>
                                    <td class="text-uppercase text-secondary">{{ $poa->descripcion }}</td>
                                </tr>
                                <tr>
                                    <td class="table-light fw-bold text-dark">CON CARGO A:</td>
                                    <td class="text-uppercase text-secondary">POA {{ now()->year }}, SUBUNIDAD, PROYECTO
                                        3, OBJETIVO 10315</td>
                                </tr>
                                <tr>
                                    <td class="table-light fw-bold text-dark">META:</td>
                                    <td class="text-secondary">1</td>
                                </tr>
                                <tr>
                                    <td class="table-light fw-bold text-dark">ACCIÓN:</td>
                                    <td class="text-muted fst-italic">—</td>
                                </tr>
                                <tr>
                                    <td class="table-light fw-bold text-dark">RUBRO:</td>
                                    <td class="p-0 text-secondary">
                                        <div class="d-flex justify-content-between align-items-center px-3 py-2">
                                            <span class="text-muted fst-italic">—</span>
                                            <span>FONDO: &nbsp;<strong>111</strong></span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="table-light fw-bold text-dark">FUNCIÓN:</td>
                                    <td class="p-0 text-secondary">
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
                                    <td class="table-light fw-bold text-dark">
                                        OBSERVACIONES: <br>
                                        <small class="fw-normal text-muted" style="font-size:0.75rem;">(Editable)</small>
                                    </td>
                                    <td class="p-2">
                                        <textarea name="observaciones" class="form-control text-uppercase shadow-none border rounded-3 p-2" rows="3"
                                            placeholder="EJ: SE ADJUNTA COTIZACIÓN. COMPRA URGENTE" maxlength="300" style="resize:vertical; font-size:0.88rem;">{{ old('observaciones', $poa->observaciones) }}</textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="table-light fw-bold text-dark">NOMBRE DEL SOLICITANTE:</td>
                                    <td class="fw-bold text-uppercase text-secondary">
                                        {{ $poa->requisicion->solicitante ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="table-light fw-bold text-dark">CARGO:</td>
                                    <td class="text-uppercase text-secondary">JEFE DEPARTAMENTO MANTENIMIENTO Y CONSERVACIÓN
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Firmas Institucionales (referenciales) --}}
                    <div class="row mt-5 pt-3 text-center mb-4">
                        <div class="col-md-6 mb-4 mb-md-0">
                            <div style="border-top:1px solid #000; width:75%; margin: 0 auto 8px;"></div>
                            <div class="fw-bold text-uppercase" style="font-size:0.85rem;">
                                {{ $poa->requisicion->solicitante ?? '—' }}</div>
                            <div class="text-muted" style="font-size:0.78rem;">DEPTO. MANTTO. Y CONSERVACIÓN</div>
                        </div>
                        <div class="col-md-6">
                            <div style="border-top:1px solid #000; width:75%; margin: 0 auto 8px;"></div>
                            <div class="fw-bold" style="font-size:0.85rem;">&nbsp;</div>
                            <div class="text-muted" style="font-size:0.78rem;">DIRECTOR DE LA FACULTAD</div>
                        </div>
                    </div>

                    {{-- Acciones Guardar y Cancelar --}}
                    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                        <a href="{{ route('poa.show', $poa->id_poa) }}"
                            class="btn btn-outline-danger fw-bold rounded-pill px-4 shadow-sm">
                            <i class="bi bi-x-circle me-1"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary text-white fw-bold rounded-pill px-5 shadow-sm">
                            <i class="bi bi-check2-circle me-1"></i> Guardar Observaciones
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endsection
