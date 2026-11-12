@extends('layouts.app')

@section('title', 'Resultados de búsqueda')

@section('content')

    <x-search-bar />

    <div class="container my-5">

        <h4 class="mb-4">
            @if(request()->hasAny(['q', 'id_ticket', 'estado', 'fecha', 'edificio', 'servicio', 'prioridad', 'lugar']))
                @if(request()->filled('q'))
                    Resultados para: "{{ request('q') }}"
                @else
                    Resultados filtrados
                @endif
            @else
                Todos los tickets
            @endif
        </h4>

        @if ($tickets->isEmpty())

            <div class="alert alert-warning text-center">
                No se encontró ningún ticket
            </div>
        @else
            <div class="row">

                @foreach ($tickets as $ticket)
                    <x-ticket-card :ticket="$ticket" />
                @endforeach

            </div>

            <!-- Paginación con estilos de Bootstrap 5 -->
            <div class="d-flex justify-content-center mt-4">
                {{ $tickets->withQueryString()->links('pagination::bootstrap-5') }}
            </div>

        @endif

    </div>

@endsection
