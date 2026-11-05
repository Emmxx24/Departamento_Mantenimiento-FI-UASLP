@extends('layouts.app')
@section('title', 'Inicio')

@section('content')

    <div class="container my-5">
        <!-- Contenido si es admin -->
        @if (Auth::user()->rol !== 'Solicitante')
            @if (Auth::user()->rol == 'Superadministrador' && !$ticketsSinAdmin->isEmpty())
                {{-- Sin admin --}}
                <div class="mb-4">
                    <h5 class="fw-bold text-danger mb-1">
                        <i class="bi bi-person-exclamation me-1"></i> Tickets pendientes de asignar
                    </h5>
                    <p class="text-muted small mb-0">
                        Solicitudes recién registradas que aún no tienen un encargado asignado para su atención.
                    </p>
                </div>
                <div class="row">
                    @foreach ($ticketsSinAdmin as $ticket)
                        <x-ticket-card :ticket="$ticket" />
                    @endforeach
                </div>
                @if ($ticketsSinAdmin->count() == 3)
                    <div class="text-end mb-5">
                        <a href="{{ route('tickets.index', ['contexto' => 'pendientes_asignar']) }}"
                            class="btn btn-primary text-white fw-bold rounded-pill px-4 shadow-sm">Ver más</a>
                    </div>
                @else
                    <div class="mb-5"></div>
                @endif
            @endif

            {{-- Pendientes --}}
            <div class="mb-4">
                <h5 class="fw-bold text-primary mb-1">
                    <i class="bi bi-clock-history me-1"></i> Últimos Tickets
                </h5>
                <p class="text-muted small mb-0">
                    Solicitudes registradas recientemente que se encuentran actualmente activas en el sistema.
                </p>
            </div>

            @if ($tickets->isEmpty())
                <p class="text-muted fw-semibold mb-5">
                    No hay tickets disponibles
                </p>
            @else
                <div class="row">
                    @foreach ($tickets as $ticket)
                        <x-ticket-card :ticket="$ticket" :hasAdmin=0 />
                    @endforeach
                </div>
                @if ($tickets->count() == 3)
                    <div class="text-end mb-5">
                        <a href="{{ route('tickets.index', ['orden' => 'mas_recientes']) }}"
                            class="btn btn-primary text-white fw-bold rounded-pill px-4 shadow-sm">Ver más</a>
                    </div>
                @else
                    <div class="mb-5"></div>
                @endif
            @endif

            {{-- Tickets más antiguos --}}
            <div class="mb-4">
                <h5 class="fw-bold text-primary mb-1">
                    <i class="bi bi-hourglass-split me-1"></i> Tickets más Antiguos
                </h5>
                <p class="text-muted small mb-0">
                    Solicitudes activas que llevan mayor tiempo registradas en el sistema y requieren seguimiento o atención.
                </p>
            </div>
            @if ($ticketsAntiguos->isEmpty())
                <p class="text-muted fw-semibold mb-5">No hay tickets disponibles</p>
            @else
                <div class="row">
                    @foreach ($ticketsAntiguos as $ticket)
                        <x-ticket-card :ticket="$ticket" />
                    @endforeach
                </div>
                @if ($ticketsAntiguos->count() == 3)
                    <div class="text-end mb-5">
                        <a href="{{ route('tickets.index', ['orden' => 'mas_antiguos']) }}"
                            class="btn btn-primary text-white fw-bold rounded-pill px-4 shadow-sm">Ver más</a>
                    </div>
                @else
                    <div class="mb-5"></div>
                @endif
            @endif

            {{-- Mayor importancia --}}
            <div class="mb-4">
                <h5 class="fw-bold text-primary mb-1">
                    <i class="bi bi-exclamation-diamond me-1"></i> Tickets con mayor Prioridad
                </h5>
                <p class="text-muted small mb-0">
                    Solicitudes marcadas con prioridad alta que requieren atención inmediata por parte del equipo.
                </p>
            </div>

            @if ($ticketsImportantes->isEmpty())
                <p class="text-muted fw-semibold mb-5">No hay tickets disponibles</p>
            @else
                <div class="row">
                    @foreach ($ticketsImportantes as $ticket)
                        <x-ticket-card :ticket="$ticket" />
                    @endforeach
                </div>
                @if ($ticketsImportantes->count() == 3)
                    <div class="text-end mb-5">
                        <a href="{{ route('tickets.index', ['prioridad' => 'Alta']) }}"
                            class="btn btn-primary text-white fw-bold rounded-pill px-4 shadow-sm">Ver más</a>
                    </div>
                @else
                    <div class="mb-5"></div>
                @endif
            @endif
        @elseif (Auth::user()->rol == 'Solicitante')
            <!-- Contenido si es solicitante -->
            {{-- Pendientes --}}
            <div class="mb-4">
                <h5 class="fw-bold text-primary mb-1">
                    <i class="bi bi-inbox me-1"></i> Tus tickets pendientes de servicio
                </h5>
                <p class="text-muted small mb-0">
                    Solicitudes que has enviado y que están en proceso de revisión por el equipo de
                    mantenimiento.
                </p>
            </div>
            @if ($ticketsPendientes->isEmpty())
                <p class="text-muted fw-semibold mb-5">No hay tickets disponibles</p>
            @else
                <div class="row">
                    @foreach ($ticketsPendientes as $ticket)
                        <x-ticket-card :ticket="$ticket" />
                    @endforeach
                </div>
                @if ($ticketsPendientes->count() == 3)
                    <div class="text-end mb-5">
                        <a href="{{ route('tickets.index', ['orden' => 'mas_recientes']) }}"
                            class="btn btn-primary text-white fw-bold rounded-pill px-4 shadow-sm">Ver más</a>
                    </div>
                @else
                    <div class="mb-5"></div>
                @endif
            @endif

            {{-- Resueltos --}}
            <div class="mb-4">
                <h5 class="fw-bold text-primary mb-1">
                    <i class="bi bi-check2-circle me-1"></i> Tus tickets de Servicios Resueltos
                </h5>
                <p class="text-muted small mb-0">
                    Historial de solicitudes concluidas satisfactoriamente por el personal de mantenimiento.
                </p>
            </div>
            @if ($ticketsResueltos->isEmpty())
                <p class="text-muted fw-semibold mb-5">No hay tickets disponibles</p>
            @else
                <div class="row">
                    @foreach ($ticketsResueltos as $ticket)
                        <x-ticket-card :ticket="$ticket" />
                    @endforeach
                </div>
                @if ($ticketsResueltos->count() == 3)
                    <div class="text-end mb-5">
                        <a href="{{ route('tickets.index', ['estado' => 'Terminado']) }}"
                            class="btn btn-primary text-white fw-bold rounded-pill px-4 shadow-sm">Ver más</a>
                    </div>
                @else
                    <div class="mb-5"></div>
                @endif
            @endif
        @endif
    </div>

    @if (session('info'))
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Ya estamos atendiendo tu solicitud',
                        text: @json(session('info')),
                        icon: 'info',
                        confirmButtonColor: '#004A98',
                        confirmButtonText: 'Entendido'
                    });
                });
            </script>
        @endpush
    @endif
@endsection
