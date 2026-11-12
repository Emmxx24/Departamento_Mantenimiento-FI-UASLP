<form autocomplete="off" action="{{ route('tickets.index') }}" method="GET" class="w-100">
    <div class="d-flex flex-column align-items-center mb-5 mt-5">

        <div class="position-relative" style="width: 600px; max-width: 100%;">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control rounded-pill ps-4 pe-5"
                placeholder="Buscar..." style="border:3px solid #1f5fa9; height:45px;">

            <button type="submit" class="btn position-absolute top-50 end-0 translate-middle-y me-3 p-0"
                style="border:none; background:none;">
                <i class="bi bi-search"></i>
            </button>
        </div>

        @php
            // Verificamos si realmente se escribió algo en alguno de los filtros avanzados
            $hasAdvancedFilters = request()
                ->collect()
                ->only([
                    'id_ticket',
                    'fecha',
                    'estado',
                    'prioridad',
                    'edificio',
                    'lugar',
                    'servicio',
                    'mostrar_inactivos',
                    'contexto',
                    'orden',
                ])
                ->filter()
                ->isNotEmpty();
        @endphp

        <button class="btn btn-link btn-sm text-decoration-none fw-bold mt-2" type="button" data-bs-toggle="collapse"
            data-bs-target="#filtrosAvanzados" aria-expanded="{{ $hasAdvancedFilters ? 'true' : 'false' }}"
            style="color: #1f5fa9;">
            <i class="fas fa-filter"></i> Filtrar opciones
        </button>

        <div class="collapse mt-3 {{ $hasAdvancedFilters ? 'show' : '' }}" id="filtrosAvanzados">
            <div class="card card-body border-0 shadow-sm mx-auto"
                style="border-radius: 15px; width: 700px; max-width: 100%; background-color: #ffffff;">
                <div class="row g-3">

                    @if (Auth::check() && Auth::user()->rol !== 'Solicitante')
                        <div class="col-12 mb-1">
                            <label class="form-label small fw-bold text-primary">Mostrar Tickets:</label>
                            <div class="d-flex gap-3 flex-wrap p-2 bg-light rounded border border-light">
                                @if (Auth::user()->rol === 'Superadministrador')
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="contexto" id="ctx_todos"
                                            value="todos"
                                            {{ request('contexto', 'todos') == 'todos' ? 'checked' : '' }}>
                                        <label class="form-check-label small fw-semibold" for="ctx_todos">Todos</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="contexto"
                                            id="ctx_pendientes" value="pendientes_asignar"
                                            {{ request('contexto') == 'pendientes_asignar' ? 'checked' : '' }}>
                                        <label class="form-check-label small fw-semibold"
                                            for="ctx_pendientes">Pendientes de asignar</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="contexto"
                                            id="ctx_asignados" value="mis_asignados"
                                            {{ request('contexto') == 'mis_asignados' ? 'checked' : '' }}>
                                        <label class="form-check-label small fw-semibold" for="ctx_asignados">Mis
                                            asignados</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="contexto"
                                            id="ctx_solicitudes" value="mis_solicitudes"
                                            {{ request('contexto') == 'mis_solicitudes' ? 'checked' : '' }}>
                                        <label class="form-check-label small fw-semibold" for="ctx_solicitudes">Mis
                                            solicitudes</label>
                                    </div>
                                @elseif(Auth::user()->rol === 'Administrador')
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="contexto"
                                            id="ctx_asignados" value="mis_asignados"
                                            {{ request('contexto', 'mis_asignados') == 'mis_asignados' ? 'checked' : '' }}>
                                        <label class="form-check-label small fw-semibold" for="ctx_asignados">Mis
                                            asignados</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="contexto"
                                            id="ctx_solicitudes" value="mis_solicitudes"
                                            {{ request('contexto') == 'mis_solicitudes' ? 'checked' : '' }}>
                                        <label class="form-check-label small fw-semibold" for="ctx_solicitudes">Mis
                                            solicitudes</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="contexto"
                                            id="ctx_todos_mis" value="todos"
                                            {{ request('contexto') == 'todos' ? 'checked' : '' }}>
                                        <label class="form-check-label small fw-semibold"
                                            for="ctx_todos_mis">Todos</label>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">ID Ticket</label>
                        <input type="number" min="1" name="id_ticket" value="{{ request('id_ticket') }}"
                            class="form-control form-control-sm" placeholder="Ej: 101">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Fecha</label>
                        <input type="date" name="fecha" value="{{ request('fecha') }}"
                            class="form-control form-control-sm">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Estado</label>
                        <select name="estado" class="form-select form-select-sm">
                            <option value="">Cualquiera</option>
                            @foreach (['Pendiente', 'Atendiendo', 'Terminado', 'Cerrado'] as $est)
                                <option value="{{ $est }}"
                                    {{ request('estado') == $est ? 'selected' : '' }}>{{ $est }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Ordenar por fecha</label>
                        <select name="orden" class="form-select form-select-sm">
                            <option value="mas_recientes"
                                {{ request('orden', 'mas_recientes') == 'mas_recientes' ? 'selected' : '' }}>Más
                                recientes primero</option>
                            <option value="mas_antiguos" {{ request('orden') == 'mas_antiguos' ? 'selected' : '' }}>
                                Más antiguos primero</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Prioridad</label>
                        <select name="prioridad" class="form-select form-select-sm">
                            <option value="">Cualquiera</option>
                            <option value="Baja" {{ request('prioridad') == 'Baja' ? 'selected' : '' }}>Baja
                            </option>
                            <option value="Media" {{ request('prioridad') == 'Media' ? 'selected' : '' }}>Media
                            </option>
                            <option value="Alta" {{ request('prioridad') == 'Alta' ? 'selected' : '' }}>Alta
                            </option>
                        </select>
                    </div>

                    {{-- EDIFICIO: muestra inactivos si se solicita --}}
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Edificio</label>
                        <select name="edificio" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            @if (request('mostrar_inactivos'))
                                <optgroup label="Activos">
                                    @foreach ($edificios as $edificio)
                                        <option value="{{ $edificio->id_edificio }}"
                                            {{ request('edificio') == $edificio->id_edificio ? 'selected' : '' }}>
                                            {{ $edificio->nombre }}
                                        </option>
                                    @endforeach
                                </optgroup>
                                @if ($edificiosInactivos->count() > 0)
                                    <optgroup label="Inactivos">
                                        @foreach ($edificiosInactivos as $edificio)
                                            <option value="{{ $edificio->id_edificio }}"
                                                {{ request('edificio') == $edificio->id_edificio ? 'selected' : '' }}>
                                                {{ $edificio->nombre }} (Inactivo)
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            @else
                                @foreach ($edificios as $edificio)
                                    <option value="{{ $edificio->id_edificio }}"
                                        {{ request('edificio') == $edificio->id_edificio ? 'selected' : '' }}>
                                        {{ $edificio->nombre }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    {{-- LUGAR: muestra inactivos si se solicita --}}
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Lugar</label>
                        <select name="lugar" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            @if (request('mostrar_inactivos'))
                                <optgroup label="Activos">
                                    @foreach ($lugares as $lugar)
                                        <option value="{{ $lugar->id_lugar }}"
                                            {{ request('lugar') == $lugar->id_lugar ? 'selected' : '' }}>
                                            {{ $lugar->nombre }}
                                        </option>
                                    @endforeach
                                </optgroup>
                                @if (isset($lugaresInactivos) && $lugaresInactivos->count() > 0)
                                    <optgroup label="Inactivos">
                                        @foreach ($lugaresInactivos as $lugar)
                                            <option value="{{ $lugar->id_lugar }}"
                                                {{ request('lugar') == $lugar->id_lugar ? 'selected' : '' }}>
                                                {{ $lugar->nombre }} (Inactivo)
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            @else
                                @foreach ($lugares as $lugar)
                                    <option value="{{ $lugar->id_lugar }}"
                                        {{ request('lugar') == $lugar->id_lugar ? 'selected' : '' }}>
                                        {{ $lugar->nombre }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    {{-- SERVICIO: muestra inactivos si se solicita --}}
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Servicio</label>
                        <select name="servicio" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            @if (request('mostrar_inactivos'))
                                <optgroup label="Activos">
                                    @foreach ($servicios as $servicio)
                                        <option value="{{ $servicio->id_servicio }}"
                                            {{ request('servicio') == $servicio->id_servicio ? 'selected' : '' }}>
                                            {{ $servicio->nombre }}
                                        </option>
                                    @endforeach
                                </optgroup>
                                @if ($serviciosInactivos->count() > 0)
                                    <optgroup label="Inactivos">
                                        @foreach ($serviciosInactivos as $servicio)
                                            <option value="{{ $servicio->id_servicio }}"
                                                {{ request('servicio') == $servicio->id_servicio ? 'selected' : '' }}>
                                                {{ $servicio->nombre }} (Inactivo)
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            @else
                                @foreach ($servicios as $servicio)
                                    <option value="{{ $servicio->id_servicio }}"
                                        {{ request('servicio') == $servicio->id_servicio ? 'selected' : '' }}>
                                        {{ $servicio->nombre }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    {{-- Checkbox: mostrar inactivos --}}
                    <div class="col-12 mt-1">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="mostrar_inactivos"
                                id="mostrar_inactivos" value="1"
                                {{ request('mostrar_inactivos') ? 'checked' : '' }}
                                onchange="this.form.submit()">
                            <label class="form-check-label small fw-bold text-muted" for="mostrar_inactivos">
                                <i class="bi bi-archive me-1"></i> Mostrar también registros con edificios, pisos, aulas o servicios inactivos
                            </label>
                        </div>
                    </div>

                </div>
                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold rounded-pill text-white shadow-sm">Aplicar Filtros</button>
                    <a href="{{ route('tickets.index') }}"
                        class="btn btn-outline-secondary btn-sm px-4 fw-bold rounded-pill">Limpiar</a>
                </div>
            </div>
        </div>

    </div>
</form>
