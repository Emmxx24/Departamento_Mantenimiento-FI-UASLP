@props([
    'administradores' => null,
    'ticket',
    'servicios' => [],
    'edificios' => [],
    'reqFinal' => null,
    'reqBorrador' => null,
])

@php
    $esSuperAdmin = Auth::user()->rol === 'Superadministrador';
    $esAdmin = Auth::user()->rol === 'Administrador';
    $esSolicitante = Auth::user()->rol === 'Solicitante';
    $isLocked = in_array($ticket->estado, ['Cerrado', 'Terminado']);
    $tieneRequisicion = $ticket->requisiciones->count() > 0;

    $solicitante = $ticket->solicitante;
    $nombreSolicitante = $solicitante
        ? trim(($solicitante->nombre ?? '') . ' ' . ($solicitante->ap_pat ?? '') . ' ' . ($solicitante->ap_mat ?? ''))
        : 'N/A';

    $adminAsignado = $ticket->adminAtiende;
    $nombreAdmin = $adminAsignado
        ? (trim(($adminAsignado->nombre ?? '') . ' ' . ($adminAsignado->ap_pat ?? '') . ' ' . ($adminAsignado->ap_mat ?? '')) ?: 'Sin asignar')
        : 'Sin asignar';

    // Colores del botón de estado
    $colorEstado = 'bg-primary text-white';
    if ($ticket->estado == 'Atendiendo') {
        $colorEstado = 'bg-warning text-dark';
    }
    if ($ticket->estado == 'Terminado') {
        $colorEstado = 'bg-success text-white';
    }
    if ($ticket->estado == 'Cerrado') {
        $colorEstado = 'bg-secondary text-white';
    }
@endphp

<div id="ticket-container" data-ticket-id="{{ $ticket->id_ticket }}"
    data-url="{{ route('admin.tickets.detalles', $ticket->id_ticket) }}"
    class="card shadow-sm border-secondary-subtle mb-4 rounded-4">
    <div class="card-body p-4 p-md-5">
        <div class="row">
            <div class="col-md-8 col-lg-9 pe-lg-4">
                <h3 class="fw-bold mb-3 text-primary">Detalle del servicio solicitado</h3>

                @if ($tieneRequisicion && !$isLocked && !$esSolicitante)
                    <div class="alert alert-info border-0 rounded-4 shadow-sm mb-3 py-2 px-3 small d-flex align-items-center gap-2" style="max-width: 650px;">
                        <i class="bi bi-info-circle-fill text-primary fs-5"></i>
                        <span>Este ticket cuenta con requisiciones asociadas, por lo que sus datos principales se encuentran bloqueados para edición.</span>
                    </div>
                @endif

                <p class="mb-1 fw-bold text-primary">
                    Id-Ticket: <span class="text-dark fw-normal">#{{ $ticket->id_ticket }}</span>
                </p>
                <p class="mb-2 fw-bold text-primary">
                    Solicitante: <span class="text-dark fw-normal">{{ $nombreSolicitante }}</span>
                </p>
                @if ($esSuperAdmin && !$tieneRequisicion && !$isLocked)
                    @php
                        $tieneAdmin = !empty($adminAsignado);
                        // Clases dinámicas: si no hay admin, usa rojo con fondo de alerta; si hay, usa azul estándar
                        $claseLabelAdmin = $tieneAdmin
                            ? 'text-primary'
                            : 'text-danger bg-danger-subtle px-2 py-1 rounded border border-danger';
                    @endphp
                    <div class="d-flex align-items-center gap-2 mb-3" style="max-width: 650px;">
                        <label id="label-admin"
                            class="form-label fw-bold mb-0 text-nowrap {{ $claseLabelAdmin }}"
                            style="min-width: 140px;">Administrador:</label>
                        <select class="form-select form-select-sm shadow-sm fw-normal"
                            onchange="updateTicket('id_admin_atiende', this.value)" {{ $isLocked ? 'disabled' : '' }}>
                            <option value="" {{ !$tieneAdmin ? 'selected' : '' }} disabled>Seleccionar
                                administrador</option>
                            @if (!empty($administradores))
                                @foreach ($administradores as $admin)
                                    <option value="{{ $admin->id }}"
                                        {{ $ticket->id_admin_atiende == $admin->id || optional($adminAsignado)->id == $admin->id ? 'selected' : '' }}>
                                        {{ $admin->nombre }} {{ $admin->ap_pat }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                @else
                    <p class="mb-2 fw-bold text-primary">
                        Administrador:
                        <span class="fw-normal {{ $adminAsignado ? 'text-dark' : 'text-muted fst-italic' }}">
                            {{ $nombreAdmin }}
                        </span>
                    </p>
                @endif

                @if (!$esSolicitante && !$isLocked && !$tieneRequisicion)
                    <label for="campo-asunto" class="form-label fw-bold text-primary mb-1">Asunto:</label>
                    <div class="input-group input-group-sm mb-3" style="max-width: 650px;">
                        <input type="text" id="campo-asunto" class="form-control" value="{{ $ticket->asunto }}"
                            data-previo="{{ $ticket->asunto }}" disabled>
                        @if (!$reqFinal)
                        <button type="button" class="btn btn-outline-secondary" onclick="toggleEditField('asunto')"
                            id="btn-edit-asunto" title="Editar asunto">
                            <i class="fas fa-edit">Editar</i>
                        </button>
                        @endif
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-1" style="max-width: 650px;">
                        <label for="campo-descripcion" class="form-label fw-bold text-primary mb-0">Descripción:</label>
                        @if (!$reqFinal)
                        <button type="button" class="btn btn-outline-secondary btn-sm"
                            onclick="toggleEditField('descripcion', true)" id="btn-edit-descripcion"
                            title="Editar descripción">
                            <i class="fas fa-edit me-1"></i> Editar
                        </button>
                        @endif
                    </div>
                    <textarea id="campo-descripcion" class="form-control mb-3" rows="3" data-previo="{{ $ticket->descripcion }}"
                        style="max-width: 650px;" disabled>{{ $ticket->descripcion }}</textarea>
                @else
                    <p class="mb-2 fw-bold text-primary">
                        Asunto: <span class="text-dark fw-normal">{{ $ticket->asunto }}</span>
                    </p>
                    <p class="mb-2 fw-bold text-primary">
                        Descripción: <br>
                        <span class="fw-normal text-dark">{{ $ticket->descripcion }}</span>
                    </p>
                @endif

                <p class="mb-2 fw-bold text-primary">
                    Fecha de creación:
                    <span
                        class="fw-normal text-dark">{{ \Carbon\Carbon::parse($ticket->fecha_creacion)->format('d/m/Y') }}</span>
                </p>

                @if (!$esSolicitante && !$isLocked && !$tieneRequisicion)
                    <div class="d-flex align-items-center gap-2 mb-2" style="max-width: 650px;">
                        <label class="form-label fw-bold text-primary mb-0 text-nowrap" style="min-width: 140px;">Tipo de servicio:</label>
                        <select class="form-select form-select-sm shadow-sm fw-normal"
                            onchange="updateTicket('id_servicio', this.value)" {{$reqFinal != null ? 'disabled' : ''}}>
                            @foreach ($servicios as $servicio)
                                <option value="{{ $servicio->id_servicio }}"
                                    {{ optional($ticket->servicio)->id_servicio == $servicio->id_servicio ? 'selected' : '' }}>
                                    {{ $servicio->nombre }} {{ !$servicio->activo ? '(Desactivado)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-flex align-items-center gap-2 mb-2" style="max-width: 650px;">
                        <label class="form-label fw-bold text-primary mb-0 text-nowrap" style="min-width: 140px;">Lugar:</label>
                        <select class="form-select form-select-sm shadow-sm fw-normal"
                            data-lugar-actual="{{ $ticket->id_aula }}" id="campo-aula"
                            onchange="updateTicket('id_aula', this.value); this.dataset.lugarActual = this.value;"
                            {{$reqFinal != null ? 'disabled' : ''}}>
                            <option value="" selected disabled>Selecciona lugar</option>
                        </select>
                    </div>

                    <div class="d-flex align-items-center gap-2 mb-2" style="max-width: 650px;">
                        <label class="form-label fw-bold text-primary mb-0 text-nowrap" style="min-width: 140px;">Edificio:</label>
                        <select class="form-select form-select-sm shadow-sm fw-normal" id="edificio"
                            onchange="updateTicket('id_edificio', this.value); updateFloor(this)"
                            {{$reqFinal != null ? 'disabled' : ''}}>
                            @foreach ($edificios as $edificio)
                                <option value="{{ $edificio->id_edificio }}"
                                    data-pisos="{{ json_encode($edificio->pisos) }}"
                                    {{ optional($ticket->edificio)->id_edificio == $edificio->id_edificio ? 'selected' : '' }}>
                                    {{ $edificio->nombre }} {{ !$edificio->activo ? '(Desactivado)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-flex align-items-center gap-2 mb-2" style="max-width: 650px;">
                        <label class="form-label fw-bold text-primary mb-0 text-nowrap" style="min-width: 140px;">Piso:</label>
                        <select class="form-select form-select-sm shadow-sm fw-normal" id="campo-piso"
                            data-piso-actual="{{ $ticket->piso }}"
                            onchange="updateTicket('piso', this.value); this.dataset.pisoActual = this.value; updateLugares();"
                            {{$reqFinal != null ? 'disabled' : ''}}>
                            <option value="" selected disabled>Selecciona piso</option>
                        </select>
                    </div>
                @else
                    <p class="mb-1 fw-bold text-primary">
                        Tipo de servicio: <span
                            class="fw-normal text-dark">{{ $ticket->servicio?->nombre }}</span>
                    </p>
                    <p class="mb-1 fw-bold text-primary">
                        Lugar: <span class="fw-normal text-dark">{{ $ticket->lugar?->nombre }}</span>
                    </p>
                    <p class="mb-1 fw-bold text-primary">
                        Edificio: <span class="fw-normal text-dark">{{ $ticket->edificio?->nombre }}</span>
                    </p>
                    <p class="mb-0 fw-bold text-primary">
                        Piso: <span
                            class="fw-normal text-dark">{{ $ticket->piso == '0' ? 'Planta baja' : $ticket->piso }}</span>
                    </p>
                @endif
            </div>

            <div class="col-md-4 col-lg-3 d-flex flex-column gap-3 mt-4 mt-md-0">
                @if (!$esSolicitante)
                    {{-- DROPDOWN PARA IMPORTANCIA --}}
                    <div class="d-flex align-items-center justify-content-md-end gap-2">
                        <span class="fw-bold text-primary text-nowrap">Importancia:</span>
                        <div class="dropdown">
                            <button id="dropdownImportanciaBtn"
                                class="btn btn-sm dropdown-toggle fw-bold text-white px-3 py-1 bg-primary rounded-pill shadow-sm"
                                type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                {{ $isLocked ? 'disabled' : '' }}>
                                {{ $ticket->prioridad }}
                            </button>
                            <ul class="dropdown-menu shadow-sm">
                                <li><a class="dropdown-item fw-bold" href="#"
                                        onclick="updateTicket('prioridad', 'Alta'); return false;">Alta</a></li>
                                <li><a class="dropdown-item fw-bold" href="#"
                                        onclick="updateTicket('prioridad', 'Media'); return false;">Media</a></li>
                                <li><a class="dropdown-item fw-bold" href="#"
                                        onclick="updateTicket('prioridad', 'Baja'); return false;">Baja</a></li>
                            </ul>
                        </div>
                    </div>

                    {{-- DROPDOWN PARA ESTADO --}}
                    <div class="d-flex align-items-center justify-content-md-end gap-2">
                        <span class="fw-bold text-primary text-nowrap">Estado:</span>
                        <div class="dropdown">
                            <button id="dropdownEstadoBtn"
                                class="btn btn-sm dropdown-toggle fw-bold px-3 py-1 shadow-sm rounded-pill {{ $colorEstado }}"
                                type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                {{ $isLocked ? 'disabled' : '' }}>
                                {{ $ticket->estado }}
                            </button>
                            <ul class="dropdown-menu shadow-sm">
                                <li><a class="dropdown-item fw-bold text-primary" href="#"
                                        onclick="updateTicket('estado', 'Pendiente'); return false;">Pendiente</a></li>
                                <li><a class="dropdown-item fw-bold text-warning" href="#"
                                        onclick="updateTicket('estado', 'Atendiendo'); return false;">Atendiendo</a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item fw-bold text-success" href="#"
                                        onclick="updateTicket('estado', 'Terminado'); return false;">Terminado (Dar
                                        solución)</a></li>
                                <li><a class="dropdown-item fw-bold text-danger" href="#"
                                        onclick="updateTicket('estado', 'Cerrado'); return false;">Cerrado (Sin
                                        solución)</a></li>
                            </ul>
                        </div>
                    </div>
                @else
                    <div class="d-flex flex-column gap-2 text-md-end">
                        <div class="fw-bold text-primary">
                            Importancia: <span
                                class="text-dark fw-normal">{{ $ticket->prioridad }}</span>
                        </div>
                        <div class="fw-bold text-primary">
                            Estado: <span class="text-dark fw-normal">{{ $ticket->estado }}</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if (!$esSolicitante)
    @push('scripts')
        <script src="{{ asset('js/componente-detalles-ticket.js') }}"></script>
    @endpush
@endif
