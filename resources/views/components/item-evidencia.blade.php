@props(['url', 'nombre', 'origen', 'fecha', 'id_archivo' => null])

<div class="p-3 d-flex justify-content-between align-items-center border-bottom border-secondary-subtle">
    {{-- Información del archivo (lado izquierdo) --}}
    <div class="d-flex align-items-center text-truncate pe-2">
        <div class="bg-primary bg-opacity-10 border border-primary border-opacity-25 rounded-circle d-flex justify-content-center align-items-center flex-shrink-0"
            style="width: 40px; height: 40px;">
            <i class="bi bi-file-earmark-text fs-5 text-primary"></i>
        </div>
        <div class="ms-3 text-truncate">
            {{-- Enlace opcional en el nombre para abrirlo --}}
            <a href="{{ $url }}" target="_blank" class="text-decoration-none d-block text-truncate"
                title="{{ $nombre }}">
                <h6 class="mb-0 fw-bold text-primary text-truncate">{{ $nombre }}</h6>
            </a>
            <small class="text-muted"><i class="bi bi-arrow-return-right me-1"></i> {{ $origen }}</small>
        </div>
    </div>

    {{-- Fecha y Botones de acción (lado derecho) --}}
    <div class="d-flex align-items-center text-muted small flex-shrink-0 ms-2">
        <span class="me-3">{{ $fecha }}</span>

        <div class="d-flex align-items-center gap-2">
            {{-- Botón para ver en navegador / abrir en pestaña nueva --}}
            <a href="{{ $url }}" target="_blank" class="text-primary fs-5 p-1 text-decoration-none"
                title="Ver en el navegador">
                <i class="bi bi-box-arrow-up-right"></i>
            </a>

            {{-- Botón para descargar el archivo a la computadora --}}
            @if(!empty($id_archivo))
                <a href="{{ route('archivo.fdescargar', $id_archivo) }}" class="text-primary fs-5 p-1 text-decoration-none"
                    title="Descargar archivo">
                    <i class="bi bi-download"></i>
                </a>
            @else
                <a href="{{ $url }}" download class="text-primary fs-5 p-1 text-decoration-none"
                    title="Descargar archivo">
                    <i class="bi bi-download"></i>
                </a>
            @endif
        </div>
    </div>
</div>
