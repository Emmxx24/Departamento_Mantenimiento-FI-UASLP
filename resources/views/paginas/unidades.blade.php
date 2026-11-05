@extends('layouts.app')

@section('title', 'Gestión de Unidades de Medida')

@section('content')
<div class="container mt-4 mb-5">

    {{-- TÍTULO --}}
    <x-page-title titulo="Gestión de Unidades de Medida" subtitulo="Administración de las unidades utilizadas en el inventario y requisiciones" />

    {{-- ALERTAS --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @elseif(session('failure'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('failure') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    {{-- BARRA DE ACCIONES --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <a href="{{ route('unidades.create') }}"
           class="btn btn-primary text-white fw-bold rounded-pill px-4 shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Nueva unidad
        </a>

        @if($unidadesInactivas->count() > 0)
            <button class="btn btn-outline-secondary rounded-pill px-3 fw-bold"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseInactivos"
                    aria-expanded="false"
                    aria-controls="collapseInactivos"
                    id="btnToggleInactivos">
                <i class="bi bi-eye me-1"></i> Unidades desactivadas
                <span class="badge bg-secondary rounded-pill ms-1">{{ $unidadesInactivas->count() }}</span>
            </button>
        @endif
    </div>

    {{-- TABLA ACTIVOS --}}
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4">

            <div class="table-responsive">

                <table class="table align-middle text-center">

                    <thead class="table-light">
                        <tr>
                            <th class="fw-bold">Nombre de la unidad</th>
                            <th class="fw-bold">Abreviatura</th>
                            <th class="fw-bold">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($unidades as $unidad)

                            <tr>
                                <td>{{ $unidad->nombre }}</td>

                                <td>
                                    {{ $unidad->abreviatura }}
                                </td>

                                <td>
                                    <div class="d-flex justify-content-center gap-2 flex-wrap">

                                        <a href="{{ route('unidades.edit', $unidad->id_unidad) }}"
                                           class="btn btn-sm btn-outline-primary fw-bold rounded-pill px-3">
                                            <i class="bi bi-pencil-square me-1"></i>Modificar
                                        </a>

                                        <form autocomplete="off" action="{{ route('unidades.destroy', $unidad->id_unidad) }}"
                                              method="POST"
                                              data-nombre="{{ $unidad->nombre }}"
                                              data-abreviatura="{{ $unidad->abreviatura }}"
                                              data-tiene-materiales="{{ $unidad->en_uso ? '1' : '0' }}">

                                            @csrf
                                            @method('DELETE')

                                            <button type="button" class="btn-eliminar-unidad btn btn-sm btn-outline-danger fw-bold rounded-pill px-3">
                                                <i class="bi bi-trash3 me-1"></i>Eliminar
                                            </button>

                                        </form>

                                    </div>
                                </td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="3" class="text-muted py-4">
                                    No hay unidades de medida registradas.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>
    </div>

    {{-- TABLA INACTIVOS (colapsable) --}}
    @if($unidadesInactivas->count() > 0)
        <div class="mt-5 pt-4 border-top">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-muted mb-0">
                    <i class="bi bi-archive me-1"></i> Unidades desactivadas
                    <span class="badge bg-secondary rounded-pill ms-1">{{ $unidadesInactivas->count() }}</span>
                </h5>
            </div>

            <div class="collapse" id="collapseInactivos">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-4">

                        <div class="table-responsive">

                            <table class="table align-middle text-center">

                                <thead class="table-light">
                                    <tr>
                                        <th class="fw-bold">Nombre de la unidad</th>
                                        <th class="fw-bold">Abreviatura</th>
                                        <th class="fw-bold">Acciones</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    @foreach($unidadesInactivas as $unidad)
                                        <tr class="table-secondary">
                                            <td>
                                                {{ $unidad->nombre }}
                                                <span class="badge bg-warning text-dark ms-1">Inactivo</span>
                                            </td>

                                            <td>
                                                {{ $unidad->abreviatura }}
                                            </td>

                                            <td>
                                                <form autocomplete="off" action="{{ route('unidades.reactivar', $unidad->id_unidad) }}"
                                                      method="POST">

                                                    @csrf
                                                    @method('PUT')

                                                    <button type="button" class="btn-reactivar-unidad btn btn-sm btn-success text-white fw-bold rounded-pill px-3">
                                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reactivar
                                                    </button>

                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                    </div>
                </div>
            </div>
        </div>
    @endif

</div>

<x-alert-modal id="modalSuccess" title="Éxito" type="success"/>
<x-alert-modal id="modalError" title="Error" type="error"/>

@push('scripts')
    <script src="{{ asset('js/unidades.js') }}"></script>
@endpush

@endsection
