@extends('layouts.app')

@section('title', 'Crear ticket')
@section('content')

    <div class="container my-5">

        <x-page-title titulo="Registro de ticket para mantenimiento"
            subtitulo="Llena los datos que se te piden a continuación." />

        <div class="card shadow-sm border-secondary-subtle">
            <div class="card-header d-flex justify-content-between align-items-center py-3 bg-light">
                <span class="fw-bold text-primary fs-5">Datos del ticket</span>
            </div>
            <div class="card-body p-4">
                <form autocomplete="off" action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data"
                    id="ticketForm" data-url-borrador="{{ route('tickets.borrador') }}"
                    data-url-home="{{ route('home') }}">
                    @csrf
                    <!-- Campos ocultos para el autoguardado -->
                    <input type="hidden" name="id_ticket" id="id_ticket" value="{{ $borrador->id_ticket ?? '' }}">
                    <input type="hidden" id="piso_borrador" value="{{ $borrador->piso ?? '' }}">
                    <input type="hidden" id="lugar_borrador" value="{{ $borrador->id_aula ?? '' }}">

                    <div class="row mb-3 align-items-center">
                        <div class="col-md-3">
                            <label class="fw-bold text-primary">Solicitante:</label>
                        </div>
                        <div class="col-md-9">
                            <span class="fw-bold">
                                {{ Auth::user()->nombre }} {{ Auth::user()->ap_pat }} {{ Auth::user()->ap_mat }} (Tú)
                            </span>
                        </div>
                    </div>

                    <!-- --FECHA-- -->
                    <div class="row mb-3 align-items-center">
                        <div class="col-md-3">
                            <label class="fw-bold text-primary">Fecha:</label>
                        </div>
                        <div class="col-md-9">
                            <span class="fw-bold">{{ date('d/m/Y') }}</span>
                        </div>
                    </div>

                    <!-- --EDIFICIO-- -->
                    <div class="row mb-3">
                        <div class="col-md-3 pt-2">
                            <label for="edificio" class="fw-bold text-primary">Edificio: <span
                                    class="text-danger">*</span></label>
                        </div>
                        <div class="col-md-9">
                            <select class="form-select bg-light border-secondary-subtle" id="edificio" name="id_edificio"
                                required>
                                <option value="" {{ !isset($borrador->id_edificio) ? 'selected' : '' }} disabled>
                                    Selecciona edificio</option>
                                @foreach ($edificios as $edificio)
                                    <option value="{{ $edificio->id_edificio }}"
                                        data-pisos="{{ json_encode($edificio->pisos) }}"
                                        {{ isset($borrador) && $borrador->id_edificio == $edificio->id_edificio ? 'selected' : '' }}>
                                        {{ $edificio->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- --PISO-- -->
                    <div class="row mb-3">
                        <div class="col-md-3 pt-2">
                            <label for="piso" class="fw-bold text-primary">Piso: <span
                                    class="text-danger">*</span></label>
                        </div>
                        <div class="col-md-9">
                            <select required class="form-select bg-light border-secondary-subtle" id="piso"
                                name="piso">
                                <option value="" selected>Selecciona piso</option>
                            </select>
                        </div>
                    </div>

                    <!-- --LUGAR-- -->
                    <div class="row mb-3">
                        <div class="col-md-3 pt-2">
                            <label for="aula" class="fw-bold text-primary">Lugar: <span
                                    class="text-danger">*</span></label>
                        </div>
                        <div class="col-md-9">
                            <select class="form-select bg-light border-secondary-subtle" id="aula" name="id_aula"
                                required>
                                <option value="" {{ !isset($borrador->id_aula) ? 'selected' : '' }} disabled>
                                    Selecciona Lugar</option>
                            </select>
                        </div>
                    </div>

                    <!-- --SERVICIO-- -->
                    <div class="row mb-4">
                        <div class="col-md-3 pt-2">
                            <label for="servicio" class="fw-bold text-primary">Tipo de servicio: <span
                                    class="text-danger">*</span></label>
                        </div>
                        <div class="col-md-9">
                            <select class="form-select bg-light border-secondary-subtle" id="servicio" name="id_servicio"
                                required>
                                <option value="" {{ !isset($borrador->id_servicio) ? 'selected' : '' }} disabled>
                                    Selecciona Servicio</option>
                                @foreach ($servicios as $servicio)
                                    <option value="{{ $servicio->id_servicio }}"
                                        {{ isset($borrador) && $borrador->id_servicio == $servicio->id_servicio ? 'selected' : '' }}>
                                        {{ $servicio->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- --ASUNTO-- -->
                    <div class="row mb-3">
                        <div class="col-md-3 pt-2">
                            <label for="asunto" class="fw-bold text-primary">Asunto: <span
                                    class="text-danger">*</span></label>
                        </div>
                        <div class="col-md-9">
                            <input type="text" name="asunto" class="form-control" id="asunto"
                                value="{{ old('asunto', $borrador->asunto ?? '') }}" maxlength="100" required>
                        </div>
                    </div>

                    <!-- --DESCRIPCION-- -->
                    <div class="row mb-4">
                        <div class="col-md-3 pt-2">
                            <label for="descripcion" class="fw-bold text-primary">Descripción:</label>
                        </div>
                        <div class="col-md-9 position-relative">
                            <div class="position-absolute top-0 start-0 mt-3 ms-4 z-1">
                                <label for="adjunto" class="cursor-pointer" style="cursor: pointer;">
                                    <img src="{{ asset('Images/paper-clip.png') }}" alt="Adjuntar archivo" width="24"
                                        height="24">
                                </label>
                                <input type="file" name="archivo_adjunto[]" id="adjunto" class="d-none" multiple>
                            </div>
                            <textarea class="form-control pt-5" name="descripcion" id="descripcion" rows="5"
                                placeholder="Escribe detalladamente la situación que deseas reportar...">{{ old('descripcion', $borrador->descripcion ?? '') }}</textarea>

                            <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>Formatos: JPG, PNG, WEBP, PDF, Word, Excel, TXT
                                    (Máx. 10 MB c/u)
                                </small>
                                <span id="contador-archivos"
                                    class="badge bg-light text-secondary border fw-bold px-3 py-2">
                                    0 / 5 archivos
                                </span>
                            </div>

                            <!-- Contenedor de previsualización de archivos en tarjetas -->
                            <div id="lista-archivos-seleccionados" class="mt-3 row g-2" style="display: none;">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-5 pt-3 flex-wrap gap-2">
                        @php
                            $rutaCancelar = (isset($borrador) || request()->filled('draft') || request('from') === 'borradores')
                                ? route('tickets.borradores')
                                : route('home');
                        @endphp
                        <a href="{{ $rutaCancelar }}" class="btn btn-outline-danger fw-bold rounded-pill px-4">
                            Cancelar
                        </a>

                        <div class="d-flex gap-3 align-items-center">
                            <span id="notificacion-borrador" class="text-success fw-bold d-none small">
                                <i class="bi bi-check-circle-fill"></i> Borrador guardado
                            </span>

                            <button type="submit" class="btn btn-primary text-white fw-bold rounded-pill px-4 shadow-sm"
                                id="btn-enviar-ticket">
                                Enviar
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>

        <!-- Modal de Previsualización de Imagen -->
        <div class="modal fade" id="modalImagenPreview" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header border-0 pb-0">
                        <h6 class="modal-title fw-bold text-truncate text-primary" id="modalImagenTitulo">Vista previa
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body text-center p-3">
                        <img id="modalImagenSrc" src="" class="img-fluid rounded-3 shadow-sm"
                            style="max-height: 70vh; object-fit: contain;">
                    </div>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
        <script src="{{ asset('js/crear-tickets.js') }}"></script>
    @endpush
@endsection
