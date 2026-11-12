@props(['ticket'])

<div class="col-md-4 mb-4" data-fecha="{{ $ticket->fecha_creacion }}">
    <div class="card bg-white border-1 p-3 rounded-4 shadow-sm h-100 d-flex flex-column ticket-card">

        <!-- Fila 1: número de ticket + badge -->
        <div class="d-flex justify-content-between align-items-center mb-2 gap-2">
            <span class="text-muted text-truncate small fw-bold d-block" title="{{ $ticket->id_ticket }}">
                Ticket {{ $ticket->id_ticket ? '#' . $ticket->id_ticket : 'Sin id' }}
            </span>
            @if (!$ticket->adminAtiende)
                <span
                    class="badge bg-warning text-dark border border-warning rounded-pill px-2 py-2 shadow-sm text-nowrap">
                    Sin asignar
                </span>
            @else
                <span
                    class="badge bg-success bg-opacity-10 text-success border border-success rounded-pill px-2 py-2 text-nowrap">
                    Asignado
                </span>
            @endif
        </div>

        <!-- Fila 2: asunto con todo el ancho -->
        <div class="mb-3">
            <span class="text-primary small fw-bold d-block">Asunto</span>
            <h5 class="mb-0 lh-sm text-dark text-truncate" title="{{ $ticket->asunto }}">
                {{ $ticket->asunto ?? 'Sin asunto' }}
            </h5>
        </div>

        <hr class="text-muted opacity-25 mb-3 mt-0">

        <!-- Cuerpo: Detalles del ticket -->
        <div class="mb-3">
            <div class="d-flex align-items-center text-secondary small mb-2">
                <i class="bi bi-calendar-event me-2 fs-6 text-primary"></i>
                <div>
                    <span class="d-block fw-semibold text-dark">Fecha de creación</span>
                    {{ $ticket->fecha_creacion ?? 'Sin fecha' }}
                </div>
            </div>

            <div class="d-flex align-items-center text-secondary small mb-2">
                <i class="bi bi-person-circle me-2 fs-6 text-primary"></i>
                <div>
                    <span class="d-block fw-semibold text-dark">Atiende</span>
                    {{ $ticket->adminAtiende ? $ticket->adminAtiende->nombre . ' ' . $ticket->adminAtiende->ap_pat : 'En espera...' }}
                </div>
            </div>

            <div class="d-flex align-items-center text-secondary small mb-2">
                <i class="bi bi-building me-2 fs-6 text-primary"></i>
                <div>
                    <span class="d-block fw-semibold text-dark">Edificio</span>
                    {{ $ticket->edificio?->nombre ?? 'Sin edificio' }}
                </div>
            </div>

            <div class="d-flex align-items-center text-secondary small">
                <i class="bi bi-geo-alt me-2 fs-6 text-primary"></i>
                <div>
                    <span class="d-block fw-semibold text-dark">Lugar</span>
                    {{ $ticket->lugar?->nombre ?? 'Sin lugar' }}
                </div>
            </div>
        </div>

        <!-- Sección inferior de metadatos con fondo gris claro -->
        <div
            class="d-flex justify-content-around align-items-center flex-wrap gap-3 small p-2 bg-light rounded-3 mb-4 mt-auto">
            <span class="fw-medium text-secondary">
                <i class="bi bi-tools text-primary me-1"></i>
                {{ $ticket->servicio->nombre ?? 'Sin servicio' }}
            </span>

            <span class="fw-medium text-secondary">
                <i class="bi bi-exclamation-triangle text-primary me-1"></i>
                {{ $ticket->prioridad ?? 'Sin estado' }}
            </span>

            <span class="fw-medium text-secondary">
                <i class="bi bi-info-circle text-primary me-1"></i>
                {{ $ticket->estado ?? 'Sin estado' }}
            </span>
        </div>

        <!-- Botón: lo empujamos hasta abajo -->
        <div>
            <a href="{{ route('tickets.show', $ticket->id_ticket) }}"
                class="btn btn-primary w-100 text-white fw-bold rounded-pill py-2 shadow-sm transition">
                Ver Detalles
            </a>
        </div>

    </div>
</div>
