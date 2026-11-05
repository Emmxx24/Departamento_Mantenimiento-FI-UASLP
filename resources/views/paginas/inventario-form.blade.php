@extends('layouts.app')

@php
    $esEdicion = isset($material) && $material->exists;
    $titulo = $esEdicion ? 'Actualizar Material' : 'Agregar Material';
    $subtitulo = $esEdicion
        ? 'Modifica los detalles del material seleccionado'
        : 'Registra un nuevo material en el inventario';
    $accion = $esEdicion ? route('inventario.update', $material->id_material) : route('inventario.store');
    $mostrarMarcaNueva = old('marca_id') === 'nueva' || old('marca_nueva') !== null;
@endphp

@section('title', $titulo)

@section('content')
    <div class="container my-5">

        <x-page-title :titulo="$titulo" :subtitulo="$subtitulo" />

        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">

                <div class="card shadow-sm border rounded-4 p-4">

                    <form action="{{ $accion }}" method="POST" enctype="multipart/form-data" autocomplete="off">
                        @csrf
                        @if ($esEdicion)
                            @method('PUT')
                        @endif

                        {{-- Nombre --}}
                        <div class="mb-4">
                            <label for="nombre" class="form-label fw-bold text-primary">
                                Nombre del material: <span class="text-danger">*</span>
                            </label>

                            <input type="text" name="nombre" id="nombre"
                                class="form-control rounded-3 @error('nombre') is-invalid @enderror"
                                value="{{ old('nombre', $material->nombre ?? '') }}"
                                placeholder="Ej: Cable calibre 12, Foco LED 15W..." required>

                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Imagen con vista previa interactiva --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold text-primary">
                                Imagen:
                            </label>

                            <div class="row g-3 align-items-center">
                                <div class="col-auto">
                                    <div class="ratio ratio-1x1 rounded-3 overflow-hidden border bg-light d-flex align-items-center justify-content-center"
                                        style="width: 100px; height: 100px;">
                                        <img id="imgPreview" src="{{ $material->imagen_url ?? '' }}" alt="Vista previa"
                                            class="w-100 h-100 object-fit-cover {{ !empty($material->imagen) ? '' : 'd-none' }}">
                                        <div id="imgPlaceholder"
                                            class="d-flex align-items-center justify-content-center text-muted {{ !empty($material->imagen) ? 'd-none' : '' }}">
                                            <i class="bi bi-image fs-1"></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="col">
                                    <input type="file" name="imagen" id="inputImagen"
                                        class="form-control rounded-3 @error('imagen') is-invalid @enderror"
                                        accept="image/*">
                                    <small class="text-muted d-block mt-1">
                                        Formatos: JPG, PNG, WEBP (Máx. 2MB).
                                    </small>
                                    @error('imagen')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Descripción --}}
                        <div class="mb-4">
                            <label for="descripcion" class="form-label fw-bold text-primary">
                                Descripción:
                            </label>

                            <textarea name="descripcion" id="descripcion" class="form-control rounded-3 @error('descripcion') is-invalid @enderror"
                                rows="3" placeholder="Descripción técnica o especificaciones del material">{{ old('descripcion', $material->descripcion ?? '') }}</textarea>

                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Unidad --}}
                        <div class="mb-4">
                            <label for="id_unidad" class="form-label fw-bold text-primary">
                                Unidad de medida: <span class="text-danger">*</span>
                            </label>

                            <select name="id_unidad" id="id_unidad"
                                class="form-select rounded-3 @error('id_unidad') is-invalid @enderror" required>
                                <option value="" disabled
                                    {{ old('id_unidad', $material->id_unidad ?? '') ? '' : 'selected' }}>
                                    Selecciona una unidad
                                </option>
                                @foreach ($unidades as $u)
                                    <option value="{{ $u->id_unidad }}"
                                        {{ old('id_unidad', $material->id_unidad ?? '') == $u->id_unidad ? 'selected' : '' }}>
                                        {{ $u->nombre }} ({{ $u->abreviatura }})
                                    </option>
                                @endforeach
                            </select>

                            @error('id_unidad')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Marca --}}
                        <div class="mb-3">
                            <label for="marca_id" class="form-label fw-bold text-primary">Marca</label>
                            <select name="marca_id" id="marca_id"
                                class="form-select rounded-3 @error('marca_id') is-invalid @enderror">
                                <option value=""
                                    {{ old('marca_id', $marcaActual ?? null) === null ? 'selected' : '' }}>
                                    -- Sin marca / Selecciona una marca --
                                </option>

                                @foreach ($marcas as $marca)
                                    <option value="{{ $marca->id_marca }}"
                                        {{ (string) old('marca_id', $marcaActual ?? null) === (string) $marca->id_marca ? 'selected' : '' }}>
                                        {{ $marca->nombre }}
                                    </option>
                                @endforeach

                                <option value="nueva" {{ old('marca_id') === 'nueva' ? 'selected' : '' }}>
                                    + Registrar nueva marca...
                                </option>
                            </select>

                            @error('marca_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Contenedor del input para marca nueva --}}
                        <div id="contenedor_marca_nueva" class="mb-3 {{ $mostrarMarcaNueva ? '' : 'd-none' }}">
                            <label for="marca_nueva" class="form-label fw-bold text-primary">
                                Nombre de la nueva marca: <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="marca_nueva" id="marca_nueva"
                                class="form-control rounded-3 @error('marca_nueva') is-invalid @enderror"
                                value="{{ old('marca_nueva') }}" placeholder="Ej: Truper, Steren, Philips..."
                                {{ $mostrarMarcaNueva ? '' : 'disabled' }}>

                            @error('marca_nueva')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Cantidad --}}
                        <div class="mb-4">
                            <label for="cantidad" class="form-label fw-bold text-primary">
                                Cantidad disponible: <span class="text-danger">*</span>
                            </label>

                            <div class="d-flex align-items-center gap-2">
                                <button type="button"
                                    class="btn btn-outline-danger rounded-circle fw-bold d-flex align-items-center justify-content-center"
                                    style="width: 38px; height: 38px;"
                                    onclick="document.getElementById('cantidad').stepDown()">
                                    <i class="bi bi-dash"></i>
                                </button>

                                <input type="number" name="cantidad" id="cantidad"
                                    class="form-control rounded-3 text-center fw-bold @error('cantidad') is-invalid @enderror"
                                    style="width: 100px;" value="{{ old('cantidad', $material->cantidad ?? 0) }}"
                                    min="0" required>

                                <button type="button"
                                    class="btn btn-outline-primary rounded-circle fw-bold d-flex align-items-center justify-content-center"
                                    style="width: 38px; height: 38px;"
                                    onclick="document.getElementById('cantidad').stepUp()">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>

                            @error('cantidad')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Botones de acción --}}
                        <div class="d-flex justify-content-center gap-3 mt-4 pt-2 border-top">
                            <a href="{{ route('inventario') }}" class="btn btn-outline-danger fw-bold rounded-pill px-4">
                                Cancelar
                            </a>

                            <button type="submit" class="btn btn-primary text-white fw-bold rounded-pill px-4 shadow-sm">
                                <i class="bi bi-check-lg me-1"></i> {{ $esEdicion ? 'Actualizar' : 'Guardar' }}
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
            document.addEventListener('DOMContentLoaded', function() {
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Manejador del selector de marca
            const selectMarca = document.getElementById('marca_id');
            const contenedorMarcaNueva = document.getElementById('contenedor_marca_nueva');
            const inputMarcaNueva = document.getElementById('marca_nueva');

            if (selectMarca && contenedorMarcaNueva && inputMarcaNueva) {
                selectMarca.addEventListener('change', function() {
                    if (this.value === 'nueva') {
                        contenedorMarcaNueva.classList.remove('d-none');
                        inputMarcaNueva.disabled = false;
                        inputMarcaNueva.focus();
                    } else {
                        contenedorMarcaNueva.classList.add('d-none');
                        inputMarcaNueva.value = '';
                        inputMarcaNueva.disabled = true;
                    }
                });
            }

            // Vista previa en vivo de imagen seleccionada
            const inputImagen = document.getElementById('inputImagen');
            const imgPreview = document.getElementById('imgPreview');
            const imgPlaceholder = document.getElementById('imgPlaceholder');

            if (inputImagen && imgPreview && imgPlaceholder) {
                inputImagen.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            imgPreview.src = e.target.result;
                            imgPreview.classList.remove('d-none');
                            imgPlaceholder.classList.add('d-none');
                        };
                        reader.readAsDataURL(this.files[0]);
                    }
                });
            }
        });
    </script>
@endpush
