@extends('layouts.app')

@section('title', 'Tickets')
@section('content')

    <x-search-bar />

    <div class="container my-5">

        @if ($tickets->isEmpty())
            <div class="alert alert-info text-center fs-4">
                No hay tickets disponibles.
            </div>
        @else
            <div class="row" id="contenedor-tickets">
                @foreach ($tickets as $ticket)
                    <x-ticket-card :ticket="$ticket" />
                @endforeach
            </div>
            
            <div class="mt-4 d-flex justify-content-center">
                {{ $tickets->links('pagination::bootstrap-5') }}
            </div>
        @endif

    </div>

@endsection
