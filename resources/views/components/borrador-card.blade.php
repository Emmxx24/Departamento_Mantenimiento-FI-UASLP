@props(['borrador'])

<div class="col-md-6 col-lg-4 mb-4">
    <div class="card bg-white border-1 p-3 rounded-4 shadow-sm h-100 d-flex flex-column borrador-card">

        <!-- Fila 1: ID de Borrador + Badge de estado -->
        <div class="d-flex justify-content-between align-items-center mb-2 gap-2">
            <span class="text-muted text-truncate small fw-bold d-block" title="ID: #{{ $borrador->id_ticket }}">
                Ticket {{ $borrador->id_ticket ? '#' . $borrador->id_ticket : 'Sin ID' }}
            </span>
            <span class="badge bg-warning text-dark border border-warning rounded-pill px-2 py-1 shadow-sm text-nowrap">
                <i class="bi bi-pencil-fill me-1"></i> Borrador
            </span>
        </div>

        <!-- Fila 2: Asunto -->
        <div class="mb-3">
            <span class="text-primary small fw-bold d-block">Asunto</span>
            <h5 class="mb-0 lh-sm text-dark text-truncate" title="{{ $borrador->asunto ?? 'Sin asunto' }}">
                {{ $borrador->asunto ?? 'Sin asunto' }}
            </h5>
        </div>

        <hr class="text-muted opacity-25 mb-3 mt-0">

        <!-- Cuerpo: Detalles del borrador -->
        <div class="mb-3">
            <div class="d-flex align-items-center text-secondary small mb-2">
                <i class="bi bi-calendar-event me-2 fs-6 text-primary"></i>
                <div>
                    <span class="d-block fw-semibold text-dark">Fecha guardado</span>
                    {{ $borrador->fecha_creacion ? \Carbon\Carbon::parse($borrador->fecha_creacion)->format('d/m/Y H:i') : date('d/m/Y') }}
                </div>
            </div>

            <div class="d-flex align-items-center text-secondary small mb-2">
                <i class="bi bi-building me-2 fs-6 text-primary"></i>
                <div>
                    <span class="d-block fw-semibold text-dark">Edificio</span>
                    {{ $borrador->edificio?->nombre ?? 'Sin edificio' }}
                </div>
            </div>

            <div class="d-flex align-items-center text-secondary small mb-2">
                <i class="bi bi-geo-alt me-2 fs-6 text-primary"></i>
                <div>
                    <span class="d-block fw-semibold text-dark">Lugar</span>
                    {{ $borrador->lugar?->nombre ?? 'Sin lugar' }}
                </div>
            </div>

            @if($borrador->descripcion)
                <div class="d-flex align-items-start text-secondary small">
                    <i class="bi bi-card-text me-2 fs-6 text-primary mt-1"></i>
                    <div class="overflow-hidden">
                        <span class="d-block fw-semibold text-dark">Descripción</span>
                        <span class="text-muted text-truncate d-block" title="{{ $borrador->descripcion }}">{{ $borrador->descripcion }}</span>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sección inferior de metadatos con fondo gris claro -->
        <div class="d-flex justify-content-around align-items-center flex-wrap gap-2 small p-2 bg-light rounded-3 mb-3 mt-auto">
            <span class="fw-medium text-secondary text-truncate" title="{{ $borrador->servicio->nombre ?? 'Sin servicio' }}">
                <i class="bi bi-tools text-primary me-1"></i>
                {{ $borrador->servicio->nombre ?? 'Sin servicio' }}
            </span>

            <span class="fw-medium text-secondary">
                <i class="bi bi-exclamation-triangle text-primary me-1"></i>
                {{ $borrador->prioridad ?? 'Media' }}
            </span>

            <span class="fw-medium text-secondary">
                <i class="bi bi-clock-history text-primary me-1"></i>
                Borrador
            </span>
        </div>

        <!-- Botones de acción -->
        <div class="d-flex gap-2 align-items-center">
            <a href="{{ route('tickets.crear', ['draft' => $borrador->id_ticket, 'from' => 'borradores']) }}"
                class="btn btn-primary text-white fw-bold rounded-pill py-2 px-3 flex-grow-1 shadow-sm d-flex align-items-center justify-content-center gap-2">
                <i class="bi bi-pencil-square"></i>
                <span>Continuar</span>
            </a>

            <form autocomplete="off"
                action="{{ route('tickets.borrador.eliminar', $borrador->id_ticket) }}"
                method="POST"
                class="d-inline m-0">
                @csrf
                @method('DELETE')
                <button type="button"
                    class="btn btn-outline-danger fw-bold rounded-pill py-2 px-3 btn-eliminar-borrador"
                    title="Eliminar borrador">
                    <i class="bi bi-trash3"></i>
                </button>
            </form>
        </div>

    </div>
</div>
