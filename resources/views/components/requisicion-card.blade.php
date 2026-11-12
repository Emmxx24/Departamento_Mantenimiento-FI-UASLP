@props(['requisicion', 'ticket' => null])

@php
    // Asegurar si viene como booleano, entero (1/0) o string
    $esBorrador = (bool) $requisicion->es_borrador;
@endphp

<tr>
    {{-- 1. ID de la requisición --}}
    <td class="align-middle fw-semibold text-secondary">
        #{{ $requisicion->id_requisicion }}
    </td>

    {{-- 2. Solicitante --}}
    <td class="align-middle">
        <span class="fw-medium text-dark">{{ $requisicion->admin ? ($requisicion->admin->nombre . ' ' . $requisicion->admin->ap_pat) : '—' }}</span>
    </td>

    {{-- 3. Fecha de la requisición (formateada con Carbon) --}}
    <td class="align-middle text-nowrap text-muted">
        <i class="bi bi-calendar3 me-1"></i>
        {{ \Carbon\Carbon::parse($requisicion->fecha_req)->format('d/m/Y H:i') }}
    </td>

    {{-- 4. Estatus: Borrador o Finalizada --}}
    <td class="align-middle">
        @if($esBorrador)
            <span class="badge rounded-pill bg-warning text-dark px-2 py-1">
                <i class="bi bi-pencil-fill me-1"></i> Borrador
            </span>
        @else
            <span class="badge rounded-pill bg-success px-2 py-1">
                <i class="bi bi-check-circle-fill me-1"></i> Finalizada
            </span>
        @endif
    </td>

    {{-- 5. Si tiene o no POA --}}
    <td class="align-middle">
        @if($requisicion->poa)
            <span class="badge rounded-pill bg-info text-dark px-2 py-1">
                <i class="me-1"></i> Con POA
            </span>
        @else
            <span class="badge rounded-pill bg-info text-dark px-2 py-1">
                <i class="me-1"></i> Sin POA
            </span>
        @endif
    </td>

    {{-- Acciones contextuales --}}
    <td class="align-middle text-end text-nowrap">
        @if(!$esBorrador)
            {{-- Ver detalle si está finalizada --}}
            <a href="{{ route('requisicion.show', $requisicion->id_requisicion) }}" 
               class="btn btn-sm btn-outline-secondary fw-bold rounded-pill px-3 me-1" 
               title="Ver requisición finalizada">
                <i class="bi bi-eye me-1"></i> Ver
            </a>
        @endif

        @if(!isset($ticket) || ($ticket->estado != 'Cerrado' && $ticket->estado != 'Terminado'))
            {{-- Editar disponible si el ticket no está cerrado/terminado --}}
            <a href="{{ route('requisicion.edit', $requisicion->id_requisicion) }}" 
               class="btn btn-sm btn-outline-primary fw-bold rounded-pill px-3 me-1" 
               title="Editar requisición">
                <i class="bi bi-pencil-square me-1"></i> Editar
            </a>

            {{-- Eliminar requisición --}}
            <form action="{{ route('requisicion.destroy', $requisicion->id_requisicion) }}" 
                  method="POST" 
                  class="d-inline">
                @csrf
                @method('DELETE')
                <button type="button" 
                        class="btn btn-sm btn-outline-danger fw-bold rounded-pill px-3 btn-eliminar-requisicion" 
                        data-tiene-poa="{{ $requisicion->poa ? '1' : '0' }}"
                        data-id-poa="{{ $requisicion->poa ? $requisicion->poa->id_poa : '' }}"
                        title="Eliminar requisición">
                    <i class="bi bi-trash3 me-1"></i> Eliminar
                </button>
            </form>
        @endif
    </td>
</tr>
