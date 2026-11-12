@extends('layouts.app')

@section('title', 'Detalle del Ticket #' . $ticket->id_ticket)

@section('content')
    <div class="container my-5">

        {{-- Componente de detalles (unificado, se adapta al rol) --}}
        <x-detalles-ticket :ticket="$ticket" :servicios="$servicios" :administradores="$administradores ?? null" :edificios="$edificios" :reqFinal="$reqFinal ?? null"
            :reqBorrador="$reqBorrador ?? null" />

        {{-- Chat (igual para ambos roles) --}}
        <x-chat-ticket :ticket="$ticket" />

        {{-- Acciones inferiores según rol --}}
        @if (Auth::user()->rol !== 'Solicitante')
            <div class="d-flex justify-content-between mt-4 flex-wrap gap-2">
                <a href="{{ route('home') }}" class="btn btn-outline-secondary rounded-pill px-4 fw-bold shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Volver
                </a>
                {{-- Botón de Generar Requisición / Ver Requisición --}}
                @if(!in_array($ticket->estado, ['Cerrado', 'Terminado']))
                    <a href="{{ route('requisicion.admin', $ticket->id_ticket) }}" id="btn-generar-requisicion"
                        class="btn btn-primary text-white rounded-pill px-4 fw-bold shadow-sm">
                        <i class="bi bi-file-earmark-text me-1"></i> Administrar Requisiciones
                    </a>
                @endif
            </div>
        @else
            <div class="mt-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <a href="{{ route('home') }}" class="btn btn-outline-secondary rounded-pill px-4 fw-bold shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Volver al inicio
                </a>

                @if (Auth::id() === $ticket->id_solicitante && !in_array($ticket->estado, ['Cerrado', 'Terminado']))
                    <form id="formCancelarTicket" action="{{ route('tickets.cancelar', $ticket->id_ticket) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PUT')
                        <button type="button" id="btnCancelarTicket" class="btn btn-outline-danger rounded-pill px-4 fw-bold shadow-sm">
                            <i class="bi bi-x-circle me-1"></i> Cancelar Ticket
                        </button>
                    </form>
                @endif
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/detalles-ticket.js') }}?v={{ time() }}"></script>
    <x-modal-evidencias :ticket="$ticket" />

    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: @json(session('success')),
                    confirmButtonColor: '#004A98'
                });
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: @json(session('error')),
                    confirmButtonColor: '#004A98'
                });
            });
        </script>
    @endif

    @if (Auth::id() === $ticket->id_solicitante && !in_array($ticket->estado, ['Cerrado', 'Terminado']))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const btnCancelar = document.getElementById('btnCancelarTicket');
                const formCancelar = document.getElementById('formCancelarTicket');

                if (btnCancelar && formCancelar) {
                    btnCancelar.addEventListener('click', function () {
                        Swal.fire({
                            title: '¿Deseas cancelar este ticket?',
                            text: 'El ticket pasará a estado "Cerrado" y se notificará al personal de mantenimiento. Esta acción no se puede deshacer.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#dc3545',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Sí, cancelar ticket',
                            cancelButtonText: 'No, mantener abierto',
                            reverseButtons: true
                        }).then((result) => {
                            if (result.isConfirmed) {
                                formCancelar.submit();
                            }
                        });
                    });
                }
            });
        </script>
    @endif
@endpush
