@extends('layouts.app')

@section('title', 'Inventario')

@section('content')

    <div class="container my-5">

        <x-page-title titulo="Gestión de Materiales" subtitulo="Administración de herramientas e insumos del departamento" />

        {{-- Alertas de éxito o error --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @elseif (session('failure'))
            <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('failure') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        {{-- Barra de acciones y buscador interactivo --}}
        <div class="row align-items-center mb-4 g-3">
            <div class="col-12 col-md-auto">
                <a href="{{ route('inventario.create') }}" class="btn btn-primary text-white fw-bold rounded-pill px-4 shadow-sm text-nowrap">
                    <i class="bi bi-plus-lg me-1"></i> Nuevo material
                </a>
            </div>

            <div class="col-12 col-md">
                <div class="position-relative mx-auto" style="max-width: 480px;">
                    <input type="text" id="buscadorInventario" class="form-control rounded-pill ps-4 pe-5 shadow-sm"
                        placeholder="Buscar por nombre, marca o especificaciones..." style="border: 2px solid #004A98;">
                    <span class="position-absolute top-50 end-0 translate-middle-y me-3 text-primary">
                        <i class="bi bi-search"></i>
                    </span>
                </div>
            </div>

            @if($materialesInactivos->count() > 0)
                <div class="col-12 col-md-auto text-md-end">
                    <button class="btn btn-outline-secondary rounded-pill px-3 fw-bold text-nowrap"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#collapseInactivos"
                            aria-expanded="false"
                            aria-controls="collapseInactivos"
                            id="btnToggleInactivos">
                        <i class="bi bi-eye me-1"></i> Materiales desactivados
                        <span class="badge bg-secondary rounded-pill ms-1">{{ $materialesInactivos->count() }}</span>
                    </button>
                </div>
            @endif
        </div>

        {{-- Listado de Materiales Activos --}}
        <div class="row g-4" id="contenedorMaterialesActivos">
            <div id="sinResultadosActivos" class="col-12 d-none">
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center text-muted">
                    <i class="bi bi-search display-5 mb-3 text-secondary"></i>
                    <h5 class="fw-bold">No se encontraron materiales</h5>
                    <p class="mb-0">No hay ningún material en existencia que coincida con tu búsqueda.</p>
                </div>
            </div>

            @forelse($materiales as $material)
                <x-inventario-card :material="$material" />
            @empty
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4 p-5 text-center text-muted">
                        <i class="bi bi-box-seam display-4 mb-3 text-secondary"></i>
                        <h5 class="fw-bold">No hay materiales registrados</h5>
                        <p class="mb-0">Comienza agregando un nuevo material al inventario con el botón superior.</p>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Sección de Materiales Desactivados (colapsable) --}}
        @if($materialesInactivos->count() > 0)
            <div class="mt-5 pt-4 border-top">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-muted mb-0">
                        <i class="bi bi-archive me-1"></i> Materiales desactivados
                        <span class="badge bg-secondary rounded-pill ms-1">{{ $materialesInactivos->count() }}</span>
                    </h5>
                </div>

                <div class="collapse" id="collapseInactivos">
                    <div class="row g-4 pt-2" id="contenedorMaterialesInactivos">
                        <div id="sinResultadosInactivos" class="col-12 d-none">
                            <div class="card border-0 shadow-sm rounded-4 p-4 text-center text-muted">
                                <i class="bi bi-search fs-3 mb-2 text-secondary"></i>
                                <p class="mb-0 small fw-semibold">No se encontraron materiales desactivados con esa coincidencia.</p>
                            </div>
                        </div>

                        @foreach($materialesInactivos as $material)
                            <x-inventario-card :material="$material" />
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

    </div>

@endsection

@push('scripts')
    <script src="{{ asset('js/inventario.js') }}"></script>
@endpush
