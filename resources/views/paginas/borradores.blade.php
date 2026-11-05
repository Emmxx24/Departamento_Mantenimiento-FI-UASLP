@extends('layouts.app')

@section('title', 'Borradores')


@section('content')

    <div class="container my-5">

        <x-page-title titulo="Mis Borradores" subtitulo="Tickets guardados sin enviar" />


        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (isset($borradoresTickets) && count($borradoresTickets) > 0)
            <div class="mb-5">
                <div class="row g-4">
                    @foreach ($borradoresTickets as $borrador)
                        <x-borrador-card :borrador="$borrador" />
                    @endforeach
                </div>
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-inbox fa-5x mb-4"></i>
                <h4 class="mb-3">No tienes borradores guardados</h4>
                <p class="mb-4">Los borradores que guardes al crear tickets aparecerán aquí</p>
                <a href="{{ route('tickets.crear') }}" class="btn-nuevo-ticket btn btn-primary text-white fw-bold rounded-pill px-4 shadow-sm">
                    <i class="fas fa-plus me-1"></i> Crear Nuevo Ticket
                </a>
            </div>
        @endif

    </div>
    @push('scripts')
        <script src="{{ asset('js/borradores.js') }}"></script>
    @endpush

@endsection
