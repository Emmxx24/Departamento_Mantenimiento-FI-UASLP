@extends('layouts.app')


@section('content')
        


        <div class="container mt-4 mb-5">
            
            <x-page-title titulo="Generación de Reportes" subtitulo="Filtra y exporta la información en PDF o Excel" />

            <div class="card shadow-sm border-0 mb-4 rounded-4">
                <div class="card-body p-4">

                    <div class="d-flex justify-content-between">
                        <h4 class="fw-bold mb-4">Generar reporte</h4>
                        <button type="button" class="btn btn-primary text-white btn-sm px-3 fw-bold rounded-pill shadow-sm" 
                        data-bs-toggle="modal" data-bs-target="#modalHistorialReportes">
                            <i class="bi bi-folder2-open me-2"></i> Reportes creados
                        </button>
                    </div>
                    
                    
                    <form autocomplete="off" id="formReporteManual">
                        @csrf

                        {{-- PERIODO --}}
                        <div class="mb-4">
                            <label class="fw-bold mb-2">Crear reporte del último:</label>
                            <select name="periodo" class="form-select w-auto">
                                <option value="">Seleccionar</option>
                                <option value="semestre">Semestre</option>
                                <option value="mes">Mes</option>
                                <option value="semana">Semana</option>
                            </select>
                        </div>

                        <h5 class="fw-bold mb-3">Filtros</h5>

                        <div class="row g-3">

                            {{-- EDIFICIO --}}
                            <div class="col-md-3">
                                <select name="id_edificio" class="form-select">
                                    <option value="">Edificio</option>
                                    @foreach($edificios as $edificio)
                                        <option value="{{ $edificio->id_edificio }}">
                                            {{ $edificio->nombre}}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- SERVICIO --}}
                            <div class="col-md-3">
                                <select name="id_servicio" class="form-select">
                                    <option value="">Servicio</option>
                                    @foreach($servicios as $servicio)
                                        <option value="{{ $servicio->id_servicio }}">
                                            {{ $servicio->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- FECHAS --}}
                            <div class="col-md-6 d-flex gap-2 align-items-center">
                                <input type="date" name="fecha_inicio" class="form-control">
                                <span>-</span>
                                <input type="date" name="fecha_fin" class="form-control">
                            </div>

                            {{-- LUGAR --}}
                            <div class="col-md-3">
                                <select name="id_lugar" class="form-select">
                                    <option value="">Lugar</option>
                                    @foreach($lugares as $lugar)
                                        <option value="{{ $lugar->id_lugar }}">
                                            {{ $lugar->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- IMPORTANCIA --}}
                            <div class="col-md-3">
                                <select name="prioridad" class="form-select">
                                    <option value="">Importancia</option>
                                    <option value="Alta">Alta</option>
                                    <option value="Media">Media</option>
                                    <option value="Baja">Baja</option>
                                </select>
                            </div>

                            {{-- ESTADO --}}
                            <div class="col-md-3">
                                <select name="estado" class="form-select">
                                    <option value="">Estado</option>
                                    <option value="Pendiente">Pendiente</option>
                                    <option value="Atendiendo">Atendiendo</option>
                                    <option value="Terminado">Terminado</option>
                                    <option value="Cerrado">Cerrado</option>
                                </select>
                            </div>

                            {{-- ADMINS --}}
                            <div class="col-md-3">
                                <select name="id_admin" class="form-select">
                                    <option value="">Administrador</option>
                                    @foreach($admins as $admin)
                                        <option value="{{ $admin->id }}">
                                            {{ $admin->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- BOTÓN --}}
                            <div class="col-md-3 d-grid">
                                <button type="submit" class="btn btn-primary text-white rounded-pill fw-bold shadow-sm">
                                    Generar reporte
                                </button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>

            {{-- EXPORTAR --}}
            <div id="seccionExportar" class="d-none d-flex justify-content-center align-items-center gap-3 mt-4 flex-wrap">
                <h5 class="fw-bold mb-0">Exportar:</h5>
                <button id="btnExportPDF" class="btn btn-outline-danger fw-bold rounded-pill px-4 shadow-sm" disabled>
                    <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                </button>
                <button id="btnExportExcel" class="btn btn-outline-success fw-bold rounded-pill px-4 shadow-sm" disabled>
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Excel
                </button>
            </div>
        <div id="resultadosReporte" class="d-none">
            

</div>

        </div>
        <x-alert-modal id="modalSuccess" title="Éxito" type="success"/>
        <x-alert-modal id="modalError" title="Error" type="error"/>
        <x-reportes-modal :reportes="$reportes" />
@push('scripts')
<script>
    window.routes = {
        generar: "{{ route('reportes.generar') }}",
        pdf: "{{ route('reportes.pdf') }}",
        excel: "{{ route('reportes.excel') }}"
    };
</script>
<script>
document.addEventListener('hidden.bs.modal', function (event) {
    const trigger = document.querySelector('[data-bs-toggle="modal"]');
    if (trigger) trigger.focus();
});
</script>
<script src="{{ asset('js/reportes.js') }}"></script>
@endpush
@endsection