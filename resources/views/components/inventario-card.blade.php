@props(['material'])

<div class="col-12 col-md-6 col-lg-4">
    <div class="card h-100 shadow-sm border rounded-4 inventario-card {{ !$material->activo ? 'bg-light card-inactiva' : 'bg-white' }}">
        <div class="card-body p-3 d-flex flex-column">

            {{-- Encabezado: Título, ID y Estado --}}
            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                <div class="flex-grow-1">
                    <h5 class="fw-bold text-primary mb-0 text-break">{{ $material->nombre }}</h5>
                    <small class="text-muted fw-semibold">Material #{{ $material->id_material }}</small>
                </div>

                @if(!$material->activo)
                    <span class="badge bg-warning text-dark border">Inactivo</span>
                @else
                    <span class="badge bg-success-subtle text-success border border-success-subtle">Activo</span>
                @endif
            </div>

            {{-- Contenido: Datos e Imagen con ratio Bootstrap --}}
            <div class="row g-2 align-items-center my-auto py-2">
                <div class="col-7">
                    <p class="small text-muted mb-2 text-truncate" title="{{ $material->descripcion ?? 'Sin descripción' }}">
                        <strong>Descripción:</strong> {{ $material->descripcion ?? 'Sin descripción' }}
                    </p>
                    <p class="small mb-1">
                        <span class="text-muted">Cantidad:</span>
                        <span class="badge bg-light text-dark border fw-bold">{{ $material->cantidad }}</span>
                    </p>
                    <p class="small mb-1">
                        <span class="text-muted">Unidad:</span>
                        <span class="badge bg-light text-dark border" title="{{ $material->unidad?->nombre }}">{{ $material->unidad?->abreviatura }}</span>
                    </p>
                    <p class="small mb-0">
                        <span class="text-muted">Marca:</span>
                        <span class="fw-semibold text-dark">{{ $material->marca_nombre }}</span>
                    </p>
                </div>

                <div class="col-5">
                    <div class="ratio ratio-1x1 rounded-3 overflow-hidden border bg-light">
                        @if($material->imagen)
                            <img src="{{ route('inventario.mostrarImagen', $material) }}"
                                 alt="{{ $material->nombre }}"
                                 class="w-100 h-100 object-fit-cover"
                                 loading="lazy">
                        @else
                            <div class="d-flex align-items-center justify-content-center text-muted h-100">
                                <i class="bi bi-box-seam fs-1"></i>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Botones de acción --}}
            <div class="mt-3 pt-2 border-top d-flex gap-2 justify-content-end align-items-center">
                <a href="{{ route('inventario.edit', $material->id_material) }}"
                   class="btn btn-sm btn-outline-primary fw-bold rounded-pill px-3">
                    <i class="bi bi-pencil-square me-1"></i>Editar
                </a>

                @if($material->activo)
                    <form action="{{ route('inventario.destroy', $material->id_material) }}"
                          method="POST"
                          class="d-inline form-eliminar-material"
                          data-tiene-requisiciones="{{ (isset($material->material_requisiciones_exists) ? $material->material_requisiciones_exists : ($material->materialRequisiciones->count() > 0)) ? '1' : '0' }}"
                          data-nombre="{{ $material->nombre }}">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-sm btn-outline-danger fw-bold rounded-pill px-3 btn-eliminar-material">
                            <i class="bi bi-trash3 me-1"></i>Eliminar
                        </button>
                    </form>
                @else
                    <form action="{{ route('inventario.reactivar', $material->id_material) }}"
                          method="POST"
                          class="d-inline form-reactivar-material"
                          data-nombre="{{ $material->nombre }}">
                        @csrf
                        @method('PUT')
                        <button type="button" class="btn btn-sm btn-success text-white fw-bold rounded-pill px-3 btn-reactivar-material">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Reactivar
                        </button>
                    </form>
                @endif
            </div>

        </div>
    </div>
</div>
