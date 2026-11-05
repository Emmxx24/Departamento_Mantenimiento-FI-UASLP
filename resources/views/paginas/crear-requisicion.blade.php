@extends('layouts.app')

@section('title', isset($modoEdicion) ? 'Editar Requisición' : 'Crear Requisición')

@section('content')

    <div class="container my-4">

        <x-page-title titulo="Nueva Requisición" subtitulo="Crea una requisición para el ticket #{{ $ticket->id_ticket }}" />

        <form autocomplete="off" id="formRequisicion"
            action="{{ isset($modoEdicion) ? route('requisicion.update', $borrador->id_requisicion) : route('requisicion.store') }}"
            method="POST" data-url-borrador="{{ route('requisicion.borrador') }}"
            data-url-requisiciones="{{ url('/admin/requisiciones') }}" data-url-material-base="{{ url('/admin/materiales') }}"
            data-id-ticket="{{ $ticket->id_ticket }}" data-id-requisicion="{{ $borrador->id_requisicion ?? '' }}">
            @csrf
            @if (isset($modoEdicion))
                @method('PUT')
            @endif

            {{-- Campos ocultos para el autoguardado --}}
            <input type="hidden" name="id_requisicion" id="id_requisicion" value="{{ $borrador->id_requisicion ?? '' }}">
            <input type="hidden" name="id_ticket" value="{{ $ticket->id_ticket }}">

            {{-- ===== Barra de acciones ===== --}}
            <div
                class="d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-3 mb-4 flex-wrap">

                <a href="{{ route('requisicion.admin', $ticket->id_ticket) }}"
                    class="btn btn-outline-secondary rounded-pill px-4 fw-bold shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Volver a Requisiciones
                </a>
                <div
                    class="d-flex flex-column flex-sm-row flex-wrap justify-content-end align-items-stretch align-items-sm-center gap-2">
                    <span id="notificacionBorrador" class="text-success fw-bold d-none small me-2 text-center text-sm-end">
                        <i class="bi bi-check-circle-fill"></i> Borrador guardado
                    </span>

                    @if (isset($borrador) && $borrador->poa)
                        <span class="badge bg-info-subtle text-dark border px-3 py-2 rounded-pill fw-semibold"
                            title="Esta requisición ya cuenta con POA vinculado">
                            <i class="bi bi-file-earmark-check me-1"></i> POA vinculado (DMC {{ $borrador->poa->id_poa }})
                        </span>
                    @endif

                    <button type="submit" class="btn btn-primary text-white fw-bold rounded-pill px-4 shadow-sm"
                        id="btnGuardarFinal">
                        <i class="bi bi-check2-circle me-1"></i> Guardar requisición
                    </button>
                </div>
            </div>

            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4">

                    {{-- ===== Encabezado del documento ===== --}}
                    <div class="row mb-3 align-items-start">
                        <div class="col-md-6">
                            <div class="fw-bold fs-6">Departamento de Mantenimiento</div>
                            <div class="fw-bold fs-6">Facultad de Ingeniería</div>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <div class="fw-bold fs-5">Requisición</div>
                            <div class="text-muted" style="font-size:0.9rem;">
                                Fecha: <strong>{{ now()->format('d/m/Y') }}</strong>
                            </div>
                            <div class="text-muted" style="font-size:0.9rem;">
                                ID: <strong>#{{ $borrador->id_requisicion ?? '(nuevo)' }}</strong>
                            </div>
                            @if (isset($borrador->poa))
                                <div class="text-muted" style="font-size:0.9rem;">
                                    POA: <strong>DMC {{ $borrador->poa->id_poa }}/{{ now()->year % 100 }}</strong>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- ===== Tabla de partidas (16 renglones fijos con altura institucional) ===== --}}
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered align-middle mb-0" id="tablaPartidas">
                            <thead class="table-light text-center small align-middle">
                                <tr>
                                    <th style="width:40px;">No.</th>
                                    <th style="width:60px;">Cant.</th>
                                    <th style="width:60px;">Un.</th>
                                    <th style="min-width:120px; text-align:left;">Nombre</th>
                                    <th style="width:70px;">Clave</th>
                                    <th style="min-width:180px; text-align:left;">Especificaciones</th>
                                    <th style="width:90px;">Marca</th>
                                    <th style="width:70px;">Rubro</th>
                                    <th style="width:85px;">Unitario</th>
                                    <th style="width:85px;">Subtotal</th>
                                    <th style="width:40px;"></th>{{-- Acción eliminar --}}
                                </tr>
                            </thead>
                            <tbody id="cuerpoPartidas" style="font-size:0.84rem;">
                                {{-- Materiales del borrador si existen --}}
                                @php $itemsCargados = $borrador?->materiales ?? collect(); @endphp
                                @foreach ($itemsCargados as $item)
                                    <tr data-id="{{ $item->id_item }}">
                                        <td class="text-center text-muted">{{ $loop->iteration }}</td>
                                        <td class="text-center">{{ $item->cantidad }}</td>
                                        <td class="text-center">{{ $item->unidad }}</td>
                                        <td>{{ $item->material ? $item->material->nombre : $item->nombre_libre }}</td>
                                        <td class="text-center text-muted fst-italic" style="font-size:0.75rem;">—</td>
                                        <td>{{ $item->especificaciones }}</td>
                                        <td class="text-center">{{ $item->marca_texto }}</td>
                                        <td class="text-center text-muted fst-italic" style="font-size:0.75rem;">—</td>
                                        <td class="text-center">${{ number_format($item->unitario, 2) }}</td>
                                        <td class="text-center" data-subtotal="{{ $item->subtotal }}">
                                            ${{ number_format($item->subtotal, 2) }}</td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-danger py-0 px-1 btn-eliminar-item"
                                                data-id="{{ $item->id_item }}" title="Eliminar">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach

                                {{-- Renglones vacíos hasta completar 16 --}}
                                @for ($i = $itemsCargados->count(); $i < 16; $i++)
                                    <tr class="fila-vacia text-muted">
                                        <td class="text-center">{{ $i + 1 }}</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td class="text-center">0.00</td>
                                        <td class="text-center" data-subtotal="0">0.00</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>

                    {{-- ===== Botón agregar material ===== --}}
                    <div class="d-flex justify-content-end gap-2 mb-4">
                        <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm"
                            data-bs-toggle="modal" data-bs-target="#modalAgregarMaterial" id="btnAbrirModal">
                            <i class="bi bi-plus-circle me-1"></i> Agregar material
                        </button>
                    </div>

                    {{-- ===== Pie del documento ===== --}}
                    <div class="table-responsive">
                        <table class="table table-bordered mb-3" style="font-size:0.85rem; min-width:600px;">
                            <tbody>
                                <tr>
                                    <th style="width:120px; background:#f8f9fa;">Proveedor:</th>
                                    <td colspan="2">
                                        <input type="text" class="form-control form-control-sm border-0 p-0"
                                            name="proveedor" id="inputProveedor"
                                            value="{{ $borrador->proveedor ?? '' }}" placeholder="Nombre del proveedor"
                                            maxlength="100" required>
                                    </td>
                                    <th style="width:100px; background:#f8f9fa;">Solicita:</th>
                                    <td colspan="2" class="fw-bold">
                                        {{ $nombreAdmin }}
                                        <input type="hidden" name="id_admin" id="id_admin"
                                            value="{{ $admin->id }}">
                                    </td>
                                    <th style="width:90px; background:#f8f9fa;">Subtotal:</th>
                                    <td class="fw-bold text-end" id="displaySubtotal">
                                        ${{ number_format($borrador?->subtotal ?? 0, 2) }}</td>
                                </tr>
                                <tr>
                                    <th style="background:#f8f9fa;">Cotización: <span class="text-danger">*</span></th>
                                    <td colspan="2">
                                        <select
                                            class="form-select form-select-sm border-0 bg-transparent fw-bold text-dark"
                                            name="cotizacion" id="selectCotizacion" required>
                                            <option value="" disabled
                                                {{ empty($borrador?->cotizacion) ? 'selected' : '' }}>-- Selecciona
                                                cotización --</option>
                                            <option value="COMPRA URGENTE"
                                                {{ ($borrador?->cotizacion ?? '') === 'COMPRA URGENTE' ? 'selected' : '' }}>
                                                COMPRA URGENTE</option>
                                            <option value="COMPRA NORMAL"
                                                {{ ($borrador?->cotizacion ?? '') === 'COMPRA NORMAL' ? 'selected' : '' }}>
                                                COMPRA NORMAL</option>
                                        </select>
                                    </td>
                                    <th style="background:#f8f9fa;">Ticket:</th>
                                    <td colspan="2">#{{ $ticket->id_ticket }}</td>
                                    <th style="background:#f8f9fa;">I.V.A. 16%:</th>
                                    <td class="fw-bold text-end" id="displayIva">
                                        ${{ number_format(($borrador?->subtotal ?? 0) * 0.16, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="6"></td>
                                    <th style="background:#f8f9fa;">Total:</th>
                                    <td class="fw-bold text-end" id="displayTotal">
                                        ${{ number_format($borrador?->total ?? 0, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>{{-- /table-responsive --}}

                    {{-- Uso institucional --}}
                    <div class="text-muted" style="font-size:0.78rem;">
                        Uso: OFICINA DE MANTENIMIENTO Y CONSERVACIÓN
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Modal de agregar material --}}
    <x-modal-agregar-material :materiales="$materiales" :unidades="$unidades" />

@endsection

@push('scripts')
    <script src="{{ asset('js/requisicion-form.js') }}?v={{ time() }}"></script>
@endpush
