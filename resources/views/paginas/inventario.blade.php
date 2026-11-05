@extends('layouts.app')

@section('title', 'Inventario')

@section('content')

    <div class="container my-5">

        <x-page-title titulo="Inventario de Materiales" subtitulo="Gestión de herramientas e insumos del departamento" />

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

        {{-- Barra de acciones --}}
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <a href="{{ route('inventario.create') }}" class="btn btn-primary text-white fw-bold rounded-pill px-4 shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Agregar material
            </a>

            @if($materialesInactivos->count() > 0)
                <button class="btn btn-outline-secondary rounded-pill px-3 fw-bold"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseInactivos"
                        aria-expanded="false"
                        aria-controls="collapseInactivos"
                        id="btnToggleInactivos">
                    <i class="bi bi-eye me-1"></i> Materiales desactivados
                    <span class="badge bg-secondary rounded-pill ms-1">{{ $materialesInactivos->count() }}</span>
                </button>
            @endif
        </div>

        {{-- Listado de Materiales Activos --}}
        <div class="row g-4">
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
                    <div class="row g-4 pt-2">
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
