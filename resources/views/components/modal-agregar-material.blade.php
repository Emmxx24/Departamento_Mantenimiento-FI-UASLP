@props(['materiales' => [], 'unidades' => []])
@php
    $unidadesLista = !empty($unidades) && count($unidades)
        ? $unidades
        : \App\Models\Unidad::where('activo', 1)->orderBy('nombre')->get();
@endphp

{{-- ============================================================
     MODAL: Agregar Material a Requisición
     Se incluye en la vista de crear-requisicion.blade.php
     ============================================================ --}}
<div class="modal fade" id="modalAgregarMaterial" tabindex="-1" aria-labelledby="modalAgregarMaterialLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

            {{-- Header --}}
            <div class="modal-header border-0 text-white" style="background-color: #004A98;">
                <h5 class="modal-title fw-bold" id="modalAgregarMaterialLabel">
                    <i class="bi bi-plus-circle me-2"></i>Agregar Material
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            {{-- Body --}}
            <div class="modal-body p-4 bg-light">

                {{-- Toggle: tipo de material --}}
                <div class="d-flex justify-content-center mb-4">
                    <div class="btn-group" role="group" id="toggleTipoMaterial">
                        <input type="radio" class="btn-check" name="tipoMaterial" id="tipoInventario" value="inventario" checked>
                        <label class="btn btn-outline-primary fw-bold rounded-start-pill px-4" for="tipoInventario">
                            <i class="bi bi-boxes me-1"></i>Del inventario
                        </label>
                        <input type="radio" class="btn-check" name="tipoMaterial" id="tipoLibre" value="libre">
                        <label class="btn btn-outline-secondary fw-bold rounded-end-pill px-4" for="tipoLibre">
                            <i class="bi bi-pencil-square me-1"></i>Material libre
                        </label>
                    </div>
                </div>

                {{-- ========== SECCIÓN: Del inventario ========== --}}
                <div id="seccionInventario">
                    <div class="mb-3">
                        <label for="selectMaterial" class="fw-bold mb-1">Material</label>
                        <select class="form-select" id="selectMaterial" name="id_material">
                            <option value="" selected disabled>Seleccionar material del inventario...</option>
                            @foreach($materiales as $mat)
                                <option value="{{ $mat->id_material }}"
                                        data-nombre="{{ $mat->nombre }}"
                                        data-descripcion="{{ $mat->descripcion }}"
                                        data-unidad="{{ $mat->unidad?->abreviatura }}">
                                    {{ $mat->nombre }}@if($mat->unidad) ({{ $mat->unidad->abreviatura }})@endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Datos autocargados del material --}}
                    <div id="datosAutoMaterial" class="d-none">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="fw-bold mb-1">Especificaciones</label>
                                <textarea class="form-control bg-secondary-subtle" id="autoEspecificaciones" rows="2" readonly></textarea>
                            </div>
                            <div class="col-md-3">
                                <label class="fw-bold mb-1">Unidad</label>
                                <input type="text" class="form-control bg-secondary-subtle" id="autoUnidad" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="fw-bold mb-1">Marca</label>
                                <select class="form-select" id="selectMarcaInventario" name="marca_inventario">
                                    <option value="" disabled selected>Sin marcas</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ========== SECCIÓN: Material libre ========== --}}
                <div id="seccionLibre" class="d-none">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="fw-bold mb-1">Nombre del material <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="libreNombre" placeholder="Ej: Tornillo galvanizado" maxlength="150">
                        </div>
                        <div class="col-md-3">
                            <label class="fw-bold mb-1">Unidad <span class="text-danger">*</span></label>
                            <select class="form-select" id="libreUnidad">
                                <option value="" disabled selected>Unidad</option>
                                @foreach ($unidadesLista as $u)
                                    <option value="{{ $u->abreviatura }}">{{ $u->nombre }} ({{ $u->abreviatura }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="fw-bold mb-1">Marca</label>
                            <input type="text" class="form-control" id="libreMarca" placeholder="Ej: Sika" maxlength="100">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold mb-1">Especificaciones</label>
                        <textarea class="form-control" id="libreEspecificaciones" rows="2" placeholder="Descripción técnica del material..."></textarea>
                    </div>
                </div>

                {{-- ========== Campos comunes ========== --}}
                <hr class="my-3">
                <div class="row">
                    <div class="col-md-4">
                        <label class="fw-bold mb-1">Cantidad <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="modalCantidad" min="1" value="1">
                    </div>
                    <div class="col-md-4">
                        <label class="fw-bold mb-1">
                            Precio Unitario ($) <span class="text-danger">*</span>
                            <small class="text-muted fw-normal" id="notaUnitarioLibre">(no aplica para inventario)</small>
                        </label>
                        <input type="number" class="form-control bg-secondary-subtle" id="modalUnitario" min="0" step="0.01" value="0.00" readonly disabled>
                    </div>
                    <div class="col-md-4">
                        <label class="fw-bold mb-1">Subtotal</label>
                        <input type="text" class="form-control fw-bold bg-secondary-subtle" id="modalSubtotal" readonly value="$0.00">
                    </div>
                </div>

                {{-- Mensaje de error --}}
                <div id="modalError" class="alert alert-danger mt-3 d-none fw-bold"></div>
            </div>

            {{-- Footer --}}
            <div class="modal-footer border-0" style="background-color: #f8f9fa;">
                <button type="button" class="btn btn-outline-danger fw-bold rounded-pill px-4" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" class="btn btn-primary text-white fw-bold rounded-pill px-4 shadow-sm" id="btnConfirmarMaterial">
                    <span id="btnConfirmarSpinner" class="spinner-border spinner-border-sm d-none me-1"></span>
                    Agregar a la requisición
                </button>
            </div>

        </div>
    </div>
</div>
