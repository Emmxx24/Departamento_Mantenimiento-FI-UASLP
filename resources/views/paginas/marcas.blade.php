@extends('layouts.app')

@section('title', 'Gestión de Marcas')

@section('content')
    <div class="container mt-4 mb-5">

        {{-- TÍTULO --}}
        <x-page-title titulo="Gestión de Marcas"
            subtitulo="Administración del catálogo de marcas para el inventario de materiales" />

        {{-- ALERTA --}}
        @if (session('success'))
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
            <a href="{{ route('marcas.create') }}" class="btn btn-primary text-white fw-bold rounded-pill px-4 shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Nueva marca
            </a>

            @if ($marcasInactivos->count() > 0)
                <button class="btn btn-outline-secondary rounded-pill px-3 fw-bold" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseInactivos" aria-expanded="false" aria-controls="collapseInactivos"
                    id="btnToggleInactivos">
                    <i class="bi bi-eye me-1"></i> Marcas desactivadas
                    <span class="badge bg-secondary rounded-pill ms-1">{{ $marcasInactivos->count() }}</span>
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
                                <th class="fw-bold">Nombre de la marca</th>
                                <th class="fw-bold">Acciones</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse($marcas as $marca)
                                <tr>
                                    <td class="fw-semibold text-dark">{{ $marca->nombre }}</td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-2 flex-wrap">

                                            <a href="{{ route('marcas.edit', $marca->id_marca) }}"
                                                class="btn btn-sm btn-outline-primary fw-bold rounded-pill px-3">
                                                <i class="bi bi-pencil-square me-1"></i>Modificar
                                            </a>

                                            <form autocomplete="off"
                                                action="{{ route('marcas.destroy', $marca->id_marca) }}" method="POST"
                                                data-nombre="{{ $marca->nombre }}"
                                                data-tiene-materiales="{{ $marca->materiales_exists ? '1' : '0' }}">

                                                @csrf
                                                @method('DELETE')

                                                <button type="button"
                                                    class="btn-eliminar-marca btn btn-sm btn-outline-danger fw-bold rounded-pill px-3">
                                                    <i class="bi bi-trash3 me-1"></i>Eliminar
                                                </button>

                                            </form>

                                        </div>
                                    </td>
                                </tr>

                            @empty

                                <tr>
                                    <td colspan="3" class="text-muted py-4">
                                        No hay marcas registradas.
                                    </td>
                                </tr>
                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>
        </div>

        {{-- TABLA INACTIVOS (colapsable) --}}
        @if ($marcasInactivos->count() > 0)
            <div class="mt-5 pt-4 border-top">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-muted mb-0">
                        <i class="bi bi-archive me-1"></i> Marcas desactivadas
                        <span class="badge bg-secondary rounded-pill ms-1">{{ $marcasInactivos->count() }}</span>
                    </h5>
                </div>

                <div class="collapse" id="collapseInactivos">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body p-4">

                            <div class="table-responsive">

                                <table class="table align-middle text-center">

                                    <thead class="table-light">
                                        <tr>
                                            <th class="fw-bold">Nombre de la marca</th>
                                            <th class="fw-bold">Estado</th>
                                            <th class="fw-bold">Acciones</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        @foreach ($marcasInactivos as $marca)
                                            <tr class="table-secondary">
                                                <td class="fw-semibold">
                                                    {{ $marca->nombre }}
                                                </td>

                                                <td>
                                                    <span
                                                        class="badge bg-warning text-dark rounded-pill px-3 py-1">Inactiva</span>
                                                </td>

                                                <td>
                                                    <form autocomplete="off"
                                                        action="{{ route('marcas.reactivar', $marca->id_marca) }}"
                                                        method="POST" data-nombre="{{ $marca->nombre }}">

                                                        @csrf
                                                        @method('PUT')

                                                        <button type="button"
                                                            class="btn-reactivar-marca btn btn-sm btn-success text-white fw-bold rounded-pill px-3">
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

    <x-alert-modal id="modalSuccess" title="Éxito" type="success" />
    <x-alert-modal id="modalError" title="Error" type="error" />

    @push('scripts')
        <script src="{{ asset('js/marcas.js') }}"></script>
        @if (session('failure'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Error',
                        text: '{{ session('failure') }}',
                        icon: 'error',
                        confirmButtonColor: '#dc3545',
                        confirmButtonText: 'Entendido'
                    });
                });
            </script>
        @endif
    @endpush

@endsection
