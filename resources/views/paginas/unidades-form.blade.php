@extends('layouts.app')

@php
    $esEdicion = isset($unidad) && $unidad->exists;
    $titulo = $esEdicion ? 'Actualizar Unidad de Medida' : 'Agregar Nueva Unidad de Medida';
    $subtitulo = $esEdicion ? 'Modifica los datos de la unidad seleccionada' : 'Registra una nueva unidad de medida en el catálogo';
    $accion = $esEdicion ? route('unidades.update', $unidad->id_unidad) : route('unidades.store');
@endphp

@section('title', $titulo)

@section('content')

<div class="container mt-4 mb-5">

    {{-- TÍTULO --}}
    <div class="mb-4">
        <x-page-title :titulo="$titulo" :subtitulo="$subtitulo" />
    </div>

    {{-- FORMULARIO --}}
    <div class="d-flex justify-content-center">

        <div class="card shadow-sm border rounded-4 w-100" style="max-width: 600px;">

            <div class="card-body p-4 p-md-5">

                <form autocomplete="off" action="{{ $accion }}" method="POST">

                    @csrf
                    @if ($esEdicion)
                        @method('PUT')
                    @endif

                    {{-- NOMBRE DE LA UNIDAD --}}
                    <div class="mb-4">

                        <label for="nombre" class="form-label fw-bold text-primary">
                            Nombre de la unidad <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               name="nombre"
                               id="nombre"
                               class="form-control rounded-3 @error('nombre') is-invalid @enderror"
                               value="{{ old('nombre', $unidad->nombre ?? '') }}"
                               placeholder="Ej: Kilogramo, Litro, Pieza, Metro..."
                               required>

                        @error('nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                    </div>

                    {{-- ABREVIATURA --}}
                    <div class="mb-4">

                        <label for="abreviatura" class="form-label fw-bold text-primary">
                            Abreviatura o símbolo <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               name="abreviatura"
                               id="abreviatura"
                               class="form-control rounded-3 @error('abreviatura') is-invalid @enderror"
                               value="{{ old('abreviatura', $unidad->abreviatura ?? '') }}"
                               placeholder="Ej: kg, lt, pza, m, cm..."
                               maxlength="20"
                               required>

                        @error('abreviatura')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <div class="form-text text-muted">
                            Esta abreviatura es la que se mostrará en los formatos oficiales de requisición, inventario y reportes.
                        </div>

                    </div>

                    {{-- BOTONES --}}
                    <div class="d-flex justify-content-center gap-3 flex-wrap pt-2 border-top">

                        <a href="{{ route('unidades') }}"
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

</div>

@endsection

@push('scripts')
    @if ($errors->any())
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
