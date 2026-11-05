@extends('layouts.app')

@php
    $esEdicion = isset($marca) && $marca->exists;
    $titulo = $esEdicion ? 'Actualizar Marca' : 'Agregar Nueva Marca';
    $subtitulo = $esEdicion ? 'Modifica el nombre de la marca seleccionada' : 'Registra una nueva marca para el inventario de materiales';
    $accion = $esEdicion ? route('marcas.update', $marca->id_marca) : route('marcas.store');
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

                    {{-- NOMBRE --}}
                    <div class="mb-4">

                        <label for="nombre" class="form-label fw-bold text-primary">
                            Nombre de la marca <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               name="nombre"
                               id="nombre"
                               class="form-control rounded-3 @error('nombre') is-invalid @enderror"
                               value="{{ old('nombre', $marca->nombre ?? '') }}"
                               placeholder="Ej: Truper, 3M, Phillips, Bosch..."
                               maxlength="100"
                               required>

                        @error('nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                    </div>

                    {{-- BOTONES --}}
                    <div class="d-flex justify-content-center gap-3 flex-wrap pt-2 border-top">

                        <a href="{{ route('marcas') }}"
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
    @if($errors->any())
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
