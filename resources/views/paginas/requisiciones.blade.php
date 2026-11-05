@extends('layouts.app')

@section('title', 'Requisiciones del Ticket #' . $ticket->id_ticket)

@section('content')
    <div class="container my-5">

        <x-page-title titulo='Requisiciones del Ticket #{{$ticket->id_ticket}}' 
            subtitulo="Administra las requisiciones de este ticket." />

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

        {{-- Barra de navegación y acciones --}}
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <a href="{{ route('tickets.show', $ticket->id_ticket) }}" class="btn btn-outline-secondary rounded-pill px-4 fw-bold shadow-sm">
                <i class="bi bi-arrow-left me-1"></i> Volver al Ticket
            </a>

            @if($ticket->estado != 'Cerrado' && $ticket->estado != 'Terminado')
                @if($requisiciones->count() < 5)
                    <a href="{{ route('requisicion.create', $ticket->id_ticket) }}"
                       id="btn-crear-requisicion"
                       data-total="{{ $requisiciones->count() }}"
                       class="btn btn-primary text-white fw-bold rounded-pill px-4 shadow-sm">
                        <i class="bi bi-plus-lg me-1"></i> Crear nueva requisición
                    </a>
                @else
                    <span class="badge bg-secondary-subtle text-secondary border px-3 py-2 rounded-pill fw-semibold">
                        <i class="bi bi-info-circle me-1"></i> Límite máximo de 5 requisiciones alcanzado
                    </span>
                @endif
            @endif
        </div>

        @if($requisiciones->count() > 0)
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="align-middle fw-semibold text-secondary">ID</th>
                    <th class="align-middle fw-medium text-dark">Autor</th>
                    <th class="align-middle text-nowrap text-muted">Fecha de creación</th>
                    <th class="align-middle fw-medium text-dark">Estado</th>
                    <th class="align-middle fw-medium text-dark">POA</th>
                    <th class="align-middle fw-medium text-dark text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($requisiciones as $requisicion)
                    <x-requisicion-card :requisicion="$requisicion" :ticket="$ticket"/>
                @endforeach
            </tbody>
        </table>
        @else
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center text-muted">
                    <i class="bi bi-card-checklist display-4 mb-3 text-secondary"></i>
                    <h5 class="fw-bold">No hay requisiciones creadas</h5>
                    <p class="mb-0">Crea una requisición para este Ticket.</p>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
        <script src="{{ asset('js/requisiciones.js') }}"></script>
    @endpush
@endsection