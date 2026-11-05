@extends('layouts.app')

@section('title', 'Gestión de Usuarios')

@push('css')
    {{-- DataTables Bootstrap 5 CSS --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <style>
        .dataTables_wrapper .dataTables_paginate .page-link {
            border-radius: 0.375rem;
            margin: 0 2px;
        }
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 0.375rem;
            padding: 0.375rem 0.75rem;
        }
        .dataTables_wrapper .dataTables_length select {
            border-radius: 0.375rem;
            padding: 0.375rem 2rem 0.375rem 0.75rem;
        }
    </style>
@endpush

@section('content')
<div class="container mt-4 mb-5">

    {{-- TÍTULO --}}
    <x-page-title titulo="Gestión de Usuarios del Sistema" subtitulo="Administración unificada de cuentas, niveles de acceso y estado en la plataforma" />

    {{-- ALERTAS DE SESIÓN --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-0 mb-4" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    {{-- BARRA SUPERIOR: ACCIONES, CUPOS Y FILTROS RÁPIDOS --}}
    <div class="card shadow-sm border-0 rounded-3 mb-4">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                
                {{-- Botones de acción masiva y creación --}}
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <a href="{{ route('usuarios.create') }}" class="btn btn-primary text-white fw-bold rounded-pill px-3 shadow-sm">
                        <i class="bi bi-person-plus me-1"></i> Nuevo usuario
                    </a>

                    <button type="button" class="btn btn-success text-white fw-bold rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalImportarExcel">
                        <i class="bi bi-file-earmark-excel me-1"></i> Importar desde Excel
                    </button>

                    <a href="{{ route('usuarios.plantilla') }}" class="btn btn-outline-success fw-bold rounded-pill px-3 shadow-sm" title="Descargar plantilla oficial Excel">
                        <i class="bi bi-download me-1"></i> Descargar Plantilla
                    </a>
                </div>

                {{-- Contadores de cupos dinámicos --}}
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 fw-semibold"
                          title="Límite configurable en el Panel Maestro">
                        <i class="bi bi-shield-fill-check me-1"></i>
                        Superadmins: <strong>{{ $totalSuperadmins }} / {{ $maxSuperadmins }}</strong>
                        @if($limiteSuperadminAlcanzado)
                            <span class="badge bg-warning text-dark ms-1">Lleno</span>
                        @endif
                    </span>

                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill px-3 py-2 fw-semibold"
                          title="Límite configurable en el Panel Maestro">
                        <i class="bi bi-shield-check me-1"></i>
                        Admins: <strong>{{ $totalAdmins }} / {{ $maxAdmins }}</strong>
                        @if($limiteAdminAlcanzado)
                            <span class="badge bg-warning text-dark ms-1">Lleno</span>
                        @endif
                    </span>

                    <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle rounded-pill px-3 py-2 fw-semibold">
                        <i class="bi bi-people me-1"></i>
                        Solicitantes: <strong>{{ $totalSolicitantes }}</strong>
                    </span>

                    @if($totalInactivos > 0)
                        <span class="badge bg-light text-muted border rounded-pill px-3 py-2 fw-semibold">
                            <i class="bi bi-slash-circle me-1"></i>
                            Inactivos: <strong>{{ $totalInactivos }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            {{-- Filtros rápidos de tabla --}}
            <div class="d-flex align-items-center gap-2 mt-3 pt-3 border-top flex-wrap">
                <span class="small text-muted fw-bold me-1">Filtrar:</span>
                <button type="button" class="btn btn-sm btn-primary rounded-2 btn-filtro-tabla active px-3" data-filtro="todos">
                    Todos ({{ $usuarios->count() }})
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-2 btn-filtro-tabla px-3" data-filtro="Superadministrador">
                    Superadministradores
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-2 btn-filtro-tabla px-3" data-filtro="Administrador">
                    Administradores
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-2 btn-filtro-tabla px-3" data-filtro="Solicitante">
                    Solicitantes
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-2 btn-filtro-tabla px-3" data-filtro="Inactivo">
                    Inactivos
                </button>
            </div>
        </div>
    </div>

    {{-- TABLA UNIFICADA DE USUARIOS --}}
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-4">

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tablaUsuarios" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th class="fw-bold">Clave</th>
                            <th class="fw-bold">Nombre Completo</th>
                            <th class="fw-bold">Correo Institucional</th>
                            <th class="fw-bold text-center">Rol</th>
                            <th class="fw-bold text-center">Estado</th>
                            <th class="fw-bold text-center text-nowrap" style="min-width: 170px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($usuarios as $usr)
                            @php
                                $esPropio = (Auth::id() === $usr->id);
                                $esSuperadminRaiz = $config->isSuperadminRaiz($usr->clave);
                                $esUltimoSuperadmin = ($usr->rol === 'Superadministrador' && $totalSuperadmins <= 1 && $usr->activo);
                            @endphp
                            <tr class="{{ !$usr->activo ? 'table-light text-muted' : '' }}">
                                
                                {{-- Clave --}}
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="badge bg-light text-dark border font-monospace px-2 py-1 rounded-2">
                                            {{ $usr->clave }}
                                        </span>
                                        @if($esSuperadminRaiz)
                                            <i class="bi bi-shield-lock text-primary" title="Superadministrador principal inmutable"></i>
                                        @endif
                                    </div>
                                </td>

                                {{-- Nombre completo --}}
                                <td>
                                    <div class="fw-semibold text-dark">
                                        {{ $usr->nombre_completo }}
                                        @if($esPropio)
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill ms-1 small">Tú</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Correo Institucional --}}
                                <td>
                                    <span class="text-muted font-monospace small">{{ $usr->email }}</span>
                                </td>

                                {{-- Rol --}}
                                <td class="text-center" data-search="{{ $usr->rol }}" data-filter="{{ $usr->rol }}">
                                    @if($usr->rol === 'Superadministrador')
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1.5 fw-semibold shadow-sm">
                                            <i class="bi bi-shield-fill-check me-1"></i>Superadministrador
                                        </span>
                                    @elseif($usr->rol === 'Administrador')
                                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill px-3 py-1.5 fw-semibold">
                                            <i class="bi bi-shield-check me-1"></i>Administrador
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle rounded-pill px-3 py-1.5 fw-semibold">
                                            <i class="bi bi-person me-1"></i>Solicitante
                                        </span>
                                    @endif
                                </td>

                                {{-- Estado --}}
                                <td class="text-center" data-search="{{ $usr->activo ? 'Activo' : 'Inactivo' }}" data-filter="{{ $usr->activo ? 'Activo' : 'Inactivo' }}">
                                    @if($usr->activo)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 fw-semibold">
                                            <i class="bi bi-check-circle me-1"></i>Activo
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border rounded-pill px-3 py-1.5 fw-semibold">
                                            <i class="bi bi-slash-circle me-1"></i>Inactivo
                                        </span>
                                    @endif
                                </td>

                                {{-- Acciones: Solo iconos, rounded-2, con title --}}
                                <td class="text-center text-nowrap">
                                    <div class="d-inline-flex align-items-center justify-content-center gap-1">

                                        {{-- 1. Modificar --}}
                                        <a href="{{ route('usuarios.edit', $usr->id) }}"
                                           class="btn btn-sm btn-outline-primary rounded-2"
                                           title="Modificar datos del usuario">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        {{-- 2. Cambiar Rol Rápido --}}
                                        @if(!$esSuperadminRaiz && !$esPropio)
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-secondary rounded-2 btn-cambiar-rol"
                                                    data-id="{{ $usr->id }}"
                                                    data-nombre="{{ $usr->nombre_completo }}"
                                                    data-rol="{{ $usr->rol }}"
                                                    data-activo="{{ $usr->activo ? '1' : '0' }}"
                                                    data-es-ultimo-superadmin="{{ $esUltimoSuperadmin ? '1' : '0' }}"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalCambiarRol"
                                                    title="Cambiar nivel de rol">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>
                                        @else
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-secondary rounded-2"
                                                    disabled
                                                    title="{{ $esSuperadminRaiz ? 'El rol del Superadministrador principal no se puede modificar' : 'No puedes modificar tu propio rol' }}">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>
                                        @endif

                                        {{-- 3. Desactivar / Reactivar (Acción directa e independiente) --}}
                                        @if($esSuperadminRaiz || $esPropio)
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-secondary rounded-2"
                                                    disabled
                                                    title="No es posible desactivar esta cuenta">
                                                <i class="bi bi-person-slash"></i>
                                            </button>
                                        @elseif($usr->activo)
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-warning rounded-2 btn-toggle-usuario"
                                                    data-id="{{ $usr->id }}"
                                                    data-nombre="{{ $usr->nombre_completo }}"
                                                    data-activo="1"
                                                    data-es-ultimo-superadmin="{{ $esUltimoSuperadmin ? '1' : '0' }}"
                                                    title="Desactivar usuario">
                                                <i class="bi bi-person-slash"></i>
                                            </button>
                                        @else
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-success rounded-2 btn-toggle-usuario"
                                                    data-id="{{ $usr->id }}"
                                                    data-nombre="{{ $usr->nombre_completo }}"
                                                    data-activo="0"
                                                    data-rol="{{ $usr->rol }}"
                                                    title="Reactivar usuario">
                                                <i class="bi bi-person-check"></i>
                                            </button>
                                        @endif

                                        {{-- 4. Eliminar --}}
                                        @if($esSuperadminRaiz || $esPropio)
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-secondary rounded-2"
                                                    disabled
                                                    title="No es posible eliminar esta cuenta">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        @else
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger rounded-2 btn-eliminar-usuario"
                                                    data-id="{{ $usr->id }}"
                                                    data-nombre="{{ $usr->nombre_completo }}"
                                                    data-es-ultimo-superadmin="{{ $esUltimoSuperadmin ? '1' : '0' }}"
                                                    title="Eliminar usuario">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        @endif

                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>

{{-- FORMULARIOS OCULTOS PARA OPERACIONES ATÓMICAS VÍA JS --}}
<form id="formToggleActivo" method="POST" action="" class="d-none">
    @csrf
</form>

<form id="formEliminar" method="POST" action="" class="d-none">
    @csrf
    @method('DELETE')
</form>

{{-- MODAL CAMBIO RÁPIDO DE ROL --}}
<div class="modal fade" id="modalCambiarRol" tabindex="-1" aria-labelledby="modalCambiarRolLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <form id="formCambiarRol" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark" id="modalCambiarRolLabel">
                        <i class="bi bi-shield-lock me-2 text-primary"></i>Cambiar Rol de Usuario
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body py-4">
                    <p class="text-muted mb-3">
                        Actualiza el nivel de permisos para <strong id="modalNombreUsuario" class="text-dark"></strong>:
                    </p>

                    <div class="mb-3">
                        <label for="selectRol" class="form-label fw-bold small text-muted">Selecciona el nuevo rol:</label>
                        <select name="rol" id="selectRol" class="form-select rounded-2 py-2" required>
                            <option value="Superadministrador" id="optSuperadmin"
                                data-label-original="Superadministrador (Acceso total al sistema y catálogos)">
                                Superadministrador (Acceso total al sistema y catálogos)
                            </option>
                            <option value="Administrador" id="optAdmin"
                                data-label-original="Administrador (Gestión de tickets, inventario y solicitudes)">
                                Administrador (Gestión de tickets, inventario y solicitudes)
                            </option>
                            <option value="Solicitante" id="optSolicitante"
                                data-label-original="Solicitante (Creación y consulta de solicitudes propias)">
                                Solicitante (Creación y consulta de solicitudes propias)
                            </option>
                        </select>
                    </div>

                    <div id="avisoUltimoSuperadmin" class="alert alert-warning border-0 rounded-2 d-none mb-0 small">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        Este usuario es el único Superadministrador activo del sistema. No es posible cambiarle el rol sin antes asignar otro Superadministrador.
                    </div>

                    <div id="avisoLimiteRol" class="alert alert-danger border-0 rounded-2 d-none mb-0 small mt-2">
                        <i class="bi bi-x-circle-fill me-1"></i>
                        <span id="textoAvisoLimite"></span>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-2 px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btnGuardarRol" class="btn btn-primary text-white fw-bold rounded-2 px-3">
                        <i class="bi bi-check-lg me-1"></i> Guardar Rol
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL DE IMPORTACIÓN MASIVA DESDE EXCEL --}}
<div class="modal fade" id="modalImportarExcel" tabindex="-1" aria-labelledby="modalImportarExcelLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold" id="modalImportarExcelLabel">
                    <i class="bi bi-file-earmark-excel me-2"></i> Importación Masiva de Usuarios
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            
            <form action="{{ route('usuarios.importarExcel') }}" method="POST" enctype="multipart/form-data" id="formImportarExcel">
                @csrf
                <div class="modal-body p-4">

                    {{-- Tarjeta informativa con instrucciones clave --}}
                    <div class="alert alert-light border border-success-subtle rounded-3 p-3 mb-4">
                        <div class="d-flex align-items-start gap-3">
                            <div class="bg-success-subtle text-success p-2 rounded-circle">
                                <i class="bi bi-info-circle-fill fs-5"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold text-success-emphasis mb-1">Instrucciones para la Carga de Usuarios</h6>
                                <p class="small text-muted mb-2">
                                    Utiliza la plantilla prediseñada para garantizar que las cabeceras coincidan con la estructura requerida por el sistema.
                                </p>
                                <ul class="small text-muted ps-3 mb-2">
                                    <li>Formatos permitidos: <strong>.xlsx</strong> o <strong>.xls</strong> (máximo 10 MB).</li>
                                    <li>Cabeceras en la 1.ª hoja: <code>clave</code>, <code>nombre</code>, <code>ap_pat</code>, <code>ap_mat</code>, <code>email</code>, <code>rol</code>.</li>
                                    <li>Dominio obligatorio: estrictamente correos institucionales terminados en <strong>@uaslp.mx</strong>.</li>
                                    <li>Clave: valor numérico institucional (RPE o Clave UASLP).</li>
                                    <li>Roles admitidos: <strong>Superadministrador</strong>, <strong>Administrador</strong>, <strong>Solicitante</strong>.</li>
                                </ul>
                                <div class="mt-2 pt-2 border-top">
                                    <a href="{{ route('usuarios.plantilla') }}" class="btn btn-sm btn-outline-success fw-bold rounded-pill px-3">
                                        <i class="bi bi-download me-1"></i> Descargar Plantilla Oficial (.xlsx)
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Selector de Archivo --}}
                    <div class="mb-3">
                        <label for="archivo_excel" class="form-label fw-bold text-dark">
                            Selecciona el archivo Excel completado <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-success"><i class="bi bi-file-earmark-spreadsheet fs-5"></i></span>
                            <input class="form-control form-control-lg" type="file" name="archivo_excel" id="archivo_excel" accept=".xlsx, .xls" required>
                        </div>
                        <div class="form-text text-muted small mt-1">
                            Asegúrate de que no existan duplicados ni filas vacías intermedias en el archivo.
                        </div>
                    </div>

                    {{-- Indicador de progreso / estado de carga --}}
                    <div id="importLoadingState" class="d-none text-center py-3">
                        <div class="spinner-border text-success" role="status">
                            <span class="visually-hidden">Procesando...</span>
                        </div>
                        <p class="small text-muted mt-2 fw-semibold mb-0">
                            Validando datos e importando usuarios por lotes. Por favor, espera...
                        </p>
                    </div>

                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal" id="btnCancelarImport">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-success text-white fw-bold rounded-pill px-4 shadow-sm" id="btnSubmitImport">
                        <i class="bi bi-upload me-1"></i> Iniciar Importación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    {{-- Dependencias de DataTables Bootstrap 5 --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    {{-- Métricas y configuración para el módulo externo de usuarios --}}
    <script>
        const METRICAS_CUPOS = @json($metricas);

        window.USUARIOS_CONFIG = {
            baseUrl: @json(url('/usuarios')),
            metricas: METRICAS_CUPOS,
            importResumen: @json(session('import_resumen')),
            sessionFailure: @json(session('failure')),
            validationError: @json($errors->any() ? $errors->first() : null),
        };
    </script>
    <script src="{{ asset('js/usuarios.js') }}"></script>
@endpush

@endsection
