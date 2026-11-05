@props([
    'esMio' => false,        // true = mis mensajes (derecha), false = la otra persona (izquierda)
    'texto' => '',           // El contenido del mensaje
    'archivos' => [],        // Colección o array de archivos adjuntos
    'archivoNombre' => null, // Retrocompatibilidad: Nombre de un archivo
    'archivoUrl' => null,    // Retrocompatibilidad: URL de un archivo
    'remitenteNombre' => null, // Nombre del autor para mensajes ajenos
    'remitenteRol' => null,    // Rol del autor (Superadministrador, Administrador, Solicitante)
    'fecha' => null          // Timestamp formateado
])

@php
    $alineacion = $esMio ? 'justify-content-end' : 'justify-content-start';
    
    // Azul oscuro institucional para el usuario actual (#004A98), Azul más claro para la contraparte (#00AEEF)
    $colorFondo = $esMio ? '#004A98' : '#00AEEF'; 
    $radioBordes = $esMio ? '20px 20px 0 20px' : '20px 20px 20px 0';

    $listaArchivos = collect();
    if (!empty($archivos)) {
        foreach($archivos as $a) {
            if ($a instanceof \App\Models\Archivo) {
                $listaArchivos->push([
                    'nombre' => $a->nombre_archivo,
                    'url' => route('archivo.descargar', $a->id_archivo),
                ]);
            } elseif (is_array($a)) {
                $listaArchivos->push([
                    'nombre' => $a['nombre'] ?? $a['nombre_archivo'] ?? 'Archivo',
                    'url' => $a['url'] ?? '#',
                ]);
            } elseif (is_object($a)) {
                $listaArchivos->push([
                    'nombre' => $a->nombre ?? $a->nombre_archivo ?? 'Archivo',
                    'url' => $a->url ?? (isset($a->id_archivo) ? route('archivo.descargar', $a->id_archivo) : '#'),
                ]);
            }
        }
    } elseif ($archivoNombre && $archivoUrl) {
        $listaArchivos->push([
            'nombre' => $archivoNombre,
            'url' => $archivoUrl,
        ]);
    }
@endphp

<div class="d-flex {{ $alineacion }} mb-3">
    <div class="p-3 text-white shadow-sm d-flex flex-column" 
         style="background-color: {{ $colorFondo }}; border-radius: {{ $radioBordes }}; max-width: 75%; min-width: 140px;">
        
        {{-- Identificación del remitente en mensajes ajenos --}}
        @if(!$esMio && $remitenteNombre)
            <div class="d-flex align-items-center mb-1 pb-1 border-bottom border-light border-opacity-25" style="font-size: 0.82rem;">
                <i class="bi bi-person-fill me-1"></i>
                <span class="fw-bold me-1 text-truncate" style="max-width: 180px;">{{ $remitenteNombre }}</span>
                @if($remitenteRol)
                    <span class="badge bg-white text-dark ms-auto shadow-sm" style="font-size: 0.68rem; font-weight: 600;">{{ $remitenteRol }}</span>
                @endif
            </div>
        @endif

        {{-- Texto del mensaje (si existe) --}}
        @if(!empty($texto))
            <span style="word-wrap: break-word; white-space: pre-wrap;">{{ $texto }}</span>
        @endif

        {{-- Lista de archivos adjuntos --}}
        @if($listaArchivos->isNotEmpty())
            <div class="{{ !empty($texto) ? 'mt-2 pt-2 border-top border-light border-opacity-25' : '' }} d-flex flex-column gap-2">
                @foreach($listaArchivos as $arc)
                    <a href="{{ $arc['url'] }}" target="_blank" 
                       class="text-white text-decoration-none d-flex align-items-center px-3 py-2 rounded-3 shadow-sm archivo-adjunto-chip">
                        <i class="bi bi-file-earmark-arrow-down fs-5 me-2 flex-shrink-0 text-white"></i>
                        <span class="text-truncate flex-grow-1 text-white fw-semibold" title="{{ $arc['nombre'] }}">{{ $arc['nombre'] }}</span>
                        <i class="bi bi-box-arrow-up-right ms-2 flex-shrink-0 text-white" style="font-size: 0.75rem;"></i>
                    </a>
                @endforeach
            </div>
        @endif

        {{-- Fecha / Hora --}}
        @if($fecha)
            <div class="text-end mt-1" style="font-size: 0.70rem; opacity: 0.85;">
                {{ $fecha }}
            </div>
        @endif
    </div>
</div>