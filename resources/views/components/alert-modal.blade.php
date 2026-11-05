@props([
    'id' => 'alertModal',
    'title' => 'Mensaje',
    'type' => 'success' // success | error | warning
])

@php
    $colors = [
        'success' => '#198754',
        'error' => '#dc3545',
        'warning' => '#ffc107'
    ];
@endphp

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px; overflow:hidden;">

            {{-- Header --}}
            <div class="modal-header text-white" style="background: {{ $colors[$type] }}">
                <h5 class="modal-title fw-bold">{{ $title }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            {{-- Body --}}
            <div class="modal-body text-center p-4">
                <div id="{{ $id }}Message" class="fs-5"></div>
            </div>

            {{-- Footer --}}
            <div class="modal-footer border-0 justify-content-center">
                <button class="btn btn-dark rounded-pill px-4" data-bs-dismiss="modal">
                    Aceptar
                </button>
            </div>

        </div>
    </div>
</div>