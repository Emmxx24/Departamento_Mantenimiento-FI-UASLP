@props(['ticket'])

@php
    $isLocked = $ticket->chatBloqueado();
    $ultimoIdMensaje = $ticket->mensajes->max('id_mensaje') ?? 0;
@endphp

<div class="card shadow-sm mb-4 rounded-4 border-secondary-subtle">
    <div class="card-body p-4 p-md-5">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-bold mb-0 text-primary">
                <i class="bi bi-chat-dots-fill me-2"></i> Mensajes y Evidencias
            </h5>
            <button type="button" class="btn btn-primary btn-sm px-3 fw-bold rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#evidenciasModal">
                <i class="bi bi-folder2-open me-2"></i> Bóveda de Archivos
            </button>
        </div>

        <div class="chat-messages mb-4 pe-2 overflow-auto" 
             style="max-height: 400px;"
             data-ultimo-id="{{ $ultimoIdMensaje }}"
             data-poll-url="{{ route('tickets.mensajes.nuevos', $ticket->id_ticket) }}"
             data-ticket-id="{{ $ticket->id_ticket }}"
             data-is-locked="{{ $isLocked ? '1' : '0' }}">
            @forelse($ticket->mensajes as $mensaje)
                <x-mensaje 
                    :esMio="$mensaje->remitente == Auth::id()" 
                    :texto="$mensaje->texto" 
                    :archivos="$mensaje->archivos"
                    :remitenteNombre="$mensaje->usuario ? trim($mensaje->usuario->nombre . ' ' . $mensaje->usuario->ap_pat) : 'Usuario'"
                    :remitenteRol="$mensaje->usuario ? $mensaje->usuario->rol : null"
                    :fecha="\Carbon\Carbon::parse($mensaje->fecha_men)->format('d/m/Y H:i')"
                />
            @empty
                <div class="text-center text-muted py-4 empty-chat-placeholder">
                    <i class="bi bi-chat-dots fs-1 mb-2 opacity-50"></i>
                    <p class="mb-0">Aún no hay mensajes. Escribe el primero.</p>
                </div>
            @endforelse
        </div>

        @if($isLocked)
            <div class="alert alert-secondary text-center fw-bold text-dark border-0 shadow-sm rounded-4">
                <i class="bi bi-lock-fill me-2 text-secondary"></i> El ticket ha sido cerrado. El chat es de solo lectura.
            </div>
        @else
            <form autocomplete="off" id="form-chat-ticket" action="{{ route('tickets.mensaje', $ticket->id_ticket) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="position-relative mt-2">
                    <input type="text" 
                           name="texto_mensaje" 
                           class="form-control rounded-pill border-2 border-primary py-3 ps-4 text-dark fw-bold shadow-none" 
                           placeholder="Escribe un mensaje o adjunta archivos..." 
                           style="padding-right: 90px;"
                           autocomplete="off">
                    
                    <div class="position-absolute d-flex align-items-center gap-2 top-50 end-0 translate-middle-y me-3">
                        <label for="chat-attachment" class="rounded-circle d-flex align-items-center justify-content-center text-primary bg-light border border-secondary-subtle" 
                               style="cursor: pointer; width: 34px; height: 34px;" 
                               title="Adjuntar Archivos (máx. 5)">
                             <i class="bi bi-paperclip fs-5"></i>
                        </label>
                        <button type="submit" id="btn-enviar-chat" class="btn btn-primary btn-sm rounded-circle d-flex align-items-center justify-content-center p-0" 
                                style="width: 34px; height: 34px; opacity: 0.45; cursor: not-allowed;"
                                title="Escribe un mensaje o adjunta archivos para enviar"
                                disabled>
                            <i class="bi bi-send-fill" style="font-size: 0.85rem; margin-left: 2px;"></i>
                        </button>
                    </div>

                    <input type="file" id="chat-attachment" name="archivos[]" multiple class="d-none" accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx,.xls,.xlsx,.txt">
                </div>
                
                {{-- Contenedor dinámico de chips de archivos adjuntos --}}
                <div id="chips-archivos-chat" class="d-flex flex-wrap gap-2 mt-2 ms-2"></div>
            </form>
        @endif
    </div>
</div>
