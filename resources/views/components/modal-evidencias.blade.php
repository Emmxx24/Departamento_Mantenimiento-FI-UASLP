@props(['ticket'])

@php
    $todosLosArchivos = collect();
    
    // Archivos directos del ticket (la creación)
    foreach($ticket->archivos as $archivo) {
        $todosLosArchivos->push([
            'nombre' => $archivo->nombre_archivo,
            'url' => route('archivo.descargar', $archivo->id_archivo),
            'origen' => 'Ticket Inicial',
            'fecha' => \Carbon\Carbon::parse($ticket->fecha_creacion)->format('d/m/Y H:i'),
            'id_archivo' => $archivo->id_archivo
        ]);
    }

    // Archivos de todos los mensajes
    foreach($ticket->mensajes as $mensaje) {
        foreach($mensaje->archivos as $archivo) {
            $nombreUsuario = $mensaje->usuario ? $mensaje->usuario->nombre . ' ' . $mensaje->usuario->ap_pat : 'Desconocido';
            $todosLosArchivos->push([
                'nombre' => $archivo->nombre_archivo,
                'url' => route('archivo.descargar', $archivo->id_archivo),
                'origen' => ($mensaje->remitente == Auth::id() ? 'Tú' : 'De ' . $nombreUsuario),
                'fecha' => \Carbon\Carbon::parse($mensaje->fecha_men)->format('d/m/Y H:i'),
                'id_archivo' => $archivo->id_archivo
            ]);
        }
    }
@endphp

<!-- Modal de Evidencias -->
<div class="modal fade" id="evidenciasModal" tabindex="-1" aria-labelledby="evidenciasModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
    <div class="modal-content border-0" style="border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
      <div class="modal-header d-flex justify-content-between align-items-center border-0 pb-0" style="background-color: #004A98;">
        <h5 class="modal-title fw-bold text-white ps-3 mt-2 mb-3" id="evidenciasModalLabel">
            <i class="bi bi-folder2-open me-2 text-warning"></i> Bóveda de Evidencias
        </h5>
        <!-- Botón de cerrado personalizado -->
        <button type="button" class="btn text-white fs-4 fw-bold pb-2 btn-modal-close" data-bs-dismiss="modal" aria-label="Close">
            &times;
        </button>
      </div>
      <div class="modal-body p-4 pt-2" style="background-color: #f8f9fa;">
        
        <p class="text-muted font-monospace small mb-4 ms-2 mt-3">Archivos asegurados del Ticket #{{ $ticket->id_ticket }}.</p>
        
        <div id="lista-archivos-boveda" class="list-group shadow-sm" style="border-radius: 15px;">
            @forelse($todosLosArchivos as $archivo)
                <x-item-evidencia 
                    :url="$archivo['url']" 
                    :nombre="$archivo['nombre']" 
                    :origen="$archivo['origen']" 
                    :fecha="$archivo['fecha']"
                    :id_archivo="$archivo['id_archivo']"
                />
            @empty
                <div id="boveda-vacia" class="text-center py-5 bg-white rounded-3 border">
                    <i class="bi bi-box-seam fs-1 text-muted mb-3 opacity-50"></i>
                    <h6 class="text-muted fw-bold">No se han subido archivos a este ticket.</h6>
                </div>
            @endforelse
        </div>

      </div>
    </div>
  </div>
</div>
