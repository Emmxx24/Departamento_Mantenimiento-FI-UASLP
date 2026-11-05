@extends('layouts.app')

@php
    $esEdicion = isset($edificio) && $edificio->exists;
    $titulo = $esEdicion ? 'Editar Edificio' : 'Agregar Nuevo Edificio';
    $subtitulo = $esEdicion ? 'Modifica el edificio, sus pisos y aulas' : 'Registra un edificio con sus pisos y aulas';
    $accion = $esEdicion ? route('edificios.update', $edificio->id_edificio) : route('edificios.store');
    $cantidadPisos = old('cantidad_pisos', $esEdicion ? $edificio->pisos->count() : 1);
@endphp

@section('title', $titulo)

@section('content')

<div class="container mt-4 mb-5">

    {{-- TÍTULO --}}
    <div class="mb-4">
        <x-page-title :titulo="$titulo" :subtitulo="$subtitulo" />
    </div>

    {{-- FORMULARIO --}}
    <div class="card shadow-sm border rounded-4">

        <div class="card-body p-4 p-md-5">

            <form autocomplete="off" action="{{ $accion }}" method="POST" id="formEdificio">

                @csrf
                @if ($esEdicion)
                    @method('PUT')
                @endif

                {{-- NOMBRE DEL EDIFICIO --}}
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="nombre" class="form-label fw-bold text-primary">
                            Nombre del edificio <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               name="nombre"
                               id="nombre"
                               class="form-control rounded-3 @error('nombre') is-invalid @enderror"
                               value="{{ old('nombre', $edificio->nombre ?? '') }}"
                               placeholder="Ej: Edificio I, Torre de Ingeniería..."
                               required>

                        @error('nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-primary">
                            Número de pisos <span class="text-danger">*</span>
                        </label>

                        <div class="input-group" style="max-width: 200px;">
                            <button type="button" class="btn btn-outline-secondary" id="btnRestarPiso">
                                <i class="bi bi-dash-lg"></i>
                            </button>
                            <input type="number"
                                   name="cantidad_pisos"
                                   id="cantidadPisos"
                                   class="form-control text-center rounded-0 fw-bold"
                                   value="{{ $cantidadPisos }}"
                                   min="1"
                                   max="20"
                                   readonly
                                   required>
                            <button type="button" class="btn btn-outline-secondary" id="btnSumarPiso">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- CONTENEDOR DE PISOS --}}
                <div class="mb-4">
                    <h5 class="fw-bold mb-3">
                        <i class="bi bi-building"></i> Pisos y Aulas
                    </h5>

                    <div class="accordion" id="accordionPisos">
                        {{-- Los pisos se generan dinámicamente con JS --}}
                    </div>
                </div>

                {{-- BOTONES --}}
                <div class="d-flex justify-content-center gap-3 flex-wrap mt-4 pt-2 border-top">

                    <a href="{{ route('edificios') }}"
                       class="btn btn-outline-danger fw-bold rounded-pill px-4">
                        Cancelar
                    </a>

                    <button type="submit" class="btn btn-primary text-white fw-bold rounded-pill px-4 shadow-sm">
                        <i class="bi bi-check-lg me-1"></i> {{ $esEdicion ? 'Guardar cambios' : 'Guardar' }}
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

{{-- Datos para JS --}}
<script>
    window.lugaresDisponibles = @json($lugares);
    window.modoEdicion = {{ $esEdicion ? 'true' : 'false' }};
    window.pisosIniciales = @json($pisosData ?? []);
</script>

@push('scripts')
    <script src="{{ asset('js/edificios-form.js') }}"></script>
    @if($errors->has('pisos_duplicados'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const mensajeHtml = @json($errors->first('pisos_duplicados'));
                Swal.fire({
                    title: 'Aulas duplicadas en el mismo piso',
                    html: `
                        <p class="text-muted small mb-3">No puedes registrar dos aulas con el mismo nombre dentro del mismo piso:</p>
                        <div class="text-start p-3 bg-light border rounded-3 mb-2" style="max-height: 200px; overflow-y: auto; font-size: 0.9rem;">
                            ${mensajeHtml}
                        </div>
                    `,
                    icon: 'warning',
                    confirmButtonColor: '#ffc107',
                    confirmButtonText: 'Entendido'
                });
            });
        </script>
    @elseif($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    title: 'Verifica los campos',
                    text: @json($errors->first()),
                    icon: 'error',
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Entendido'
                });
            });
        </script>
    @endif
@endpush

@endsection
