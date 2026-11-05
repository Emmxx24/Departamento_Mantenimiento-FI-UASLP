@extends('layouts.app')

@php
    $esEdicion = isset($usuario) && $usuario->exists;
    $titulo = $esEdicion ? 'Actualizar Usuario' : 'Registrar Nuevo Usuario';
    $subtitulo = $esEdicion
        ? 'Modifica los datos personales y el nivel de permisos del usuario seleccionado'
        : 'Registra un nuevo usuario en la plataforma con el rol correspondiente';
    $accion = $esEdicion ? route('usuarios.update', $usuario->id) : route('usuarios.store');
    $esSuperadminRaiz = $esSuperadminRaiz ?? false;
    $esPropio = $esEdicion && Auth::id() === $usuario->id;
    $aplicaRestriccionCupo = !$esEdicion || $usuario->activo;
@endphp

@section('title', $titulo)

@section('content')

    <div class="container mt-4 mb-5">

        {{-- TÍTULO --}}
        <div class="mb-4">
            <x-page-title :titulo="$titulo" :subtitulo="$subtitulo" />
        </div>

        {{-- AVISO DE CUPOS DISPONIBLES --}}
        <div class="d-flex justify-content-center mb-4">
            <div class="card border-0 shadow-sm rounded-3 w-100 bg-light p-3" style="max-width: 720px;">
                <div class="d-flex justify-content-around flex-wrap gap-2 text-center">
                    <div>
                        <span class="text-muted small d-block">Superadministradores activos</span>
                        <strong class="fs-6 {{ $totalSuperadmins >= $maxSuperadmins ? 'text-danger' : 'text-primary' }}">
                            {{ $totalSuperadmins }} / {{ $maxSuperadmins }}
                        </strong>
                        @if ($totalSuperadmins >= $maxSuperadmins)
                            <span class="badge bg-warning text-dark ms-1">Cupo Lleno</span>
                        @endif
                    </div>
                    <div class="vr d-none d-md-block"></div>
                    <div>
                        <span class="text-muted small d-block">Administradores activos</span>
                        <strong class="fs-6 {{ $totalAdmins >= $maxAdmins ? 'text-danger' : 'text-primary' }}">
                            {{ $totalAdmins }} / {{ $maxAdmins }}
                        </strong>
                        @if ($totalAdmins >= $maxAdmins)
                            <span class="badge bg-warning text-dark ms-1">Cupo Lleno</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- FORMULARIO --}}
        <div class="d-flex justify-content-center">

            <div class="card shadow-sm border-0 rounded-3 w-100" style="max-width: 720px;">

                <div class="card-body p-4 p-md-5">

                    <form autocomplete="off" action="{{ $accion }}" method="POST" id="formUsuario">

                        @csrf
                        @if ($esEdicion)
                            @method('PUT')
                        @endif

                        <div class="row g-3">

                            {{-- CLAVE --}}
                            <div class="col-12 col-md-6">
                                <label for="clave" class="form-label fw-bold text-primary">
                                    Clave Institucional <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="clave" id="clave"
                                    class="form-control rounded-2 font-monospace @error('clave') is-invalid @enderror"
                                    value="{{ old('clave', $usuario->clave ?? '') }}" maxlength="20" inputmode="numeric"
                                    {{ $esSuperadminRaiz ? 'readonly' : 'oninput="this.value = this.value.replace(/[^0-9]/g, \'\')"' }}
                                    {{ $esSuperadminRaiz ? 'readonly' : 'required' }}>
                                @error('clave')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if ($esSuperadminRaiz)
                                    <div class="form-text text-muted small"><i class="bi bi-shield-lock me-1"></i>Clave del
                                        Superadministrador principal (no modificable).</div>
                                @else
                                    <div class="form-text text-muted small"><i class="bi bi-hash me-1"></i>RPE o Clave
                                        numérica institucional.</div>
                                @endif
                            </div>

                            {{-- ROL --}}
                            <div class="col-12 col-md-6">
                                <label for="rol" class="form-label fw-bold text-primary">
                                    Rol en el Sistema <span class="text-danger">*</span>
                                </label>
                                @if ($esSuperadminRaiz)
                                    <input type="text" class="form-control rounded-2 bg-light text-muted"
                                        value="Superadministrador" readonly>
                                    <input type="hidden" name="rol" value="Superadministrador">
                                    <div class="form-text text-muted small">El rol del Superadministrador principal no se
                                        puede modificar.</div>
                                @elseif ($esPropio)
                                    <input type="text" class="form-control rounded-2 bg-light text-muted"
                                        value="{{ $usuario->rol }}" readonly>
                                    <input type="hidden" name="rol" value="{{ $usuario->rol }}">
                                    <div class="form-text text-muted small">No puedes modificar tu propio rol en el
                                        sistema.</div>
                                @else
                                    <select name="rol" id="rol"
                                        class="form-select rounded-2 @error('rol') is-invalid @enderror" required>
                                        <option value="" disabled
                                            {{ old('rol', $usuario->rol ?? '') == '' ? 'selected' : '' }}>Selecciona un
                                            rol...</option>

                                        <option value="Superadministrador"
                                            {{ old('rol', $usuario->rol ?? '') == 'Superadministrador' ? 'selected' : '' }}
                                            {{ $aplicaRestriccionCupo && (!$esEdicion || $usuario->rol !== 'Superadministrador') && $limiteSuperadminAlcanzado ? 'disabled' : '' }}>
                                            Superadministrador
                                            {{ $aplicaRestriccionCupo && (!$esEdicion || $usuario->rol !== 'Superadministrador') && $limiteSuperadminAlcanzado ? '- [Lleno]' : '' }}
                                        </option>

                                        <option value="Administrador"
                                            {{ old('rol', $usuario->rol ?? '') == 'Administrador' ? 'selected' : '' }}
                                            {{ $aplicaRestriccionCupo && (!$esEdicion || $usuario->rol !== 'Administrador') && $limiteAdminAlcanzado ? 'disabled' : '' }}>
                                            Administrador
                                            {{ $aplicaRestriccionCupo && (!$esEdicion || $usuario->rol !== 'Administrador') && $limiteAdminAlcanzado ? '- [Lleno]' : '' }}
                                        </option>

                                        <option value="Solicitante"
                                            {{ old('rol', $usuario->rol ?? '') == 'Solicitante' ? 'selected' : '' }}>
                                            Solicitante
                                        </option>
                                    </select>
                                    @error('rol')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text text-muted small">Nivel de privilegios y permisos en el sistema.
                                    </div>
                                @endif
                            </div>

                            {{-- NOMBRE --}}
                            <div class="col-12">
                                <label for="nombre" class="form-label fw-bold text-primary">
                                    Nombre(s) <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="nombre" id="nombre"
                                    class="form-control rounded-2 @error('nombre') is-invalid @enderror"
                                    value="{{ old('nombre', $usuario->nombre ?? '') }}" maxlength="50" required>
                                @error('nombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- APELLIDO PATERNO --}}
                            <div class="col-12 col-md-6">
                                <label for="ap_pat" class="form-label fw-bold text-primary">
                                    Apellido Paterno <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="ap_pat" id="ap_pat"
                                    class="form-control rounded-2 @error('ap_pat') is-invalid @enderror"
                                    value="{{ old('ap_pat', $usuario->ap_pat ?? '') }}" maxlength="50" required>
                                @error('ap_pat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- APELLIDO MATERNO --}}
                            <div class="col-12 col-md-6">
                                <label for="ap_mat" class="form-label fw-bold text-primary">
                                    Apellido Materno
                                </label>
                                <input type="text" name="ap_mat" id="ap_mat"
                                    class="form-control rounded-2 @error('ap_mat') is-invalid @enderror"
                                    value="{{ old('ap_mat', $usuario->ap_mat ?? '') }}" maxlength="50">
                                @error('ap_mat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- CORREO ELECTRÓNICO --}}
                            <div class="col-12">
                                <label for="email" class="form-label fw-bold text-primary">
                                    {{ $esSuperadminRaiz ? 'Correo Electrónico' : 'Correo Institucional' }} <span
                                        class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i
                                            class="bi bi-envelope-at"></i></span>
                                    <input type="email" name="email" id="email"
                                        class="form-control rounded-end-2 @error('email') is-invalid @enderror"
                                        value="{{ old('email', $usuario->email ?? '') }}" maxlength="100"
                                        placeholder="{{ $esSuperadminRaiz ? 'ejemplo@correo.com' : 'ejemplo@uaslp.mx' }}"
                                        required>
                                    @error('email')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-text text-muted small mt-1">
                                    @if ($esSuperadminRaiz)
                                        <i class="bi bi-shield-check me-1 text-primary"></i>Superadministrador principal:
                                        se permite cualquier dominio de correo válido.
                                    @else
                                        <i class="bi bi-info-circle me-1"></i>El correo debe pertenecer estrictamente al
                                        dominio oficial <strong>@uaslp.mx</strong>.
                                    @endif
                                </div>
                            </div>

                            {{-- AVISO DISCRETO EN MODO INSTITUCIONAL O PROTECCIÓN SUPERADMIN RAÍZ --}}
                            @if ($esInstitucional)
                                <div class="col-12">
                                    <div
                                        class="alert alert-light border small text-muted py-2 px-3 d-flex align-items-center gap-2 mb-0 rounded-2">
                                        <i class="bi bi-info-circle text-primary"></i>
                                        <span>Modo institucional activo: La contraseña del usuario es validada centralmente
                                            por la UASLP.</span>
                                    </div>
                                </div>
                            @elseif ($esSuperadminRaiz && Auth::id() !== ($usuario->id ?? null))
                                {{-- OTROS SUPERADMINS NO PUEDEN MODIFICAR LA CONTRASEÑA DEL SUPERADMIN BASE --}}
                                <div class="col-12">
                                    <div
                                        class="alert alert-light border small text-muted py-2 px-3 d-flex align-items-center gap-2 mb-0 rounded-2">
                                        <i class="bi bi-shield-lock text-primary"></i>
                                        <span>Por seguridad, la contraseña del Superadministrador principal solo puede ser
                                            modificada directamente por el titular de la cuenta.</span>
                                    </div>
                                </div>
                            @else
                                {{-- CAMPOS DE CONTRASEÑA EN MODO LOCAL --}}
                                <div class="col-12 col-md-6">
                                    <label for="password" class="form-label fw-bold text-primary">
                                        Contraseña {{ $esEdicion ? '(Opcional)' : '*' }}
                                    </label>
                                    <input type="password" name="password" id="password"
                                        class="form-control rounded-2 @error('password') is-invalid @enderror"
                                        {{ $esEdicion ? '' : 'required' }}>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text text-muted small">
                                        {{ $esEdicion ? 'Déjala vacía para conservar la actual. Mínimo 8 caracteres.' : 'Mínimo 8 caracteres.' }}
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="password_confirmation" class="form-label fw-bold text-primary">
                                        Confirmar Contraseña {{ $esEdicion ? '(Opcional)' : '*' }}
                                    </label>
                                    <input type="password" name="password_confirmation" id="password_confirmation"
                                        class="form-control rounded-2" {{ $esEdicion ? '' : 'required' }}>
                                </div>
                            @endif

                        </div>

                        {{-- BOTONES --}}
                        <div class="d-flex justify-content-center gap-3 flex-wrap pt-2 border-top">

                            <a href="{{ route('usuarios') }}" class="btn btn-outline-danger fw-bold rounded-pill px-4">
                                Cancelar
                            </a>

                            <button type="submit" class="btn btn-primary text-white fw-bold rounded-pill px-4 shadow-sm"
                                id="btnSubmitUsuario">
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.getElementById('email');
            const formUsuario = document.getElementById('formUsuario');
            const emailOriginal = @json(strtolower($usuario->email ?? ''));
            const esSuperadminRaiz = {{ $esSuperadminRaiz ? 'true' : 'false' }};

            function validarEmailInstitucional(mostrarAlerta = false) {
                if (!emailInput) return true;
                const valor = emailInput.value.trim().toLowerCase();
                if (valor === '') return true;

                // El Superadministrador principal puede utilizar cualquier dominio de correo válido
                if (esSuperadminRaiz) {
                    emailInput.classList.remove('is-invalid');
                    return true;
                }

                // Permitir conservar el correo previo si estamos en edición y no ha cambiado
                if (emailOriginal !== '' && valor === emailOriginal) {
                    emailInput.classList.remove('is-invalid');
                    return true;
                }

                // Estricta verificación del dominio @uaslp.mx
                const esValido = valor.endsWith('@uaslp.mx') && valor.length > 9;

                if (!esValido) {
                    emailInput.classList.add('is-invalid');
                    if (mostrarAlerta) {
                        Swal.fire({
                            title: 'Correo Institucional Requerido',
                            text: 'El correo electrónico debe pertenecer estrictamente al dominio institucional (@uaslp.mx).',
                            icon: 'warning',
                            confirmButtonColor: '#004A98',
                            confirmButtonText: 'Entendido'
                        });
                    }
                    return false;
                } else {
                    emailInput.classList.remove('is-invalid');
                    return true;
                }
            }

            if (emailInput) {
                emailInput.addEventListener('blur', function() {
                    validarEmailInstitucional(false);
                });
            }

            if (formUsuario) {
                formUsuario.addEventListener('submit', function(e) {
                    if (!validarEmailInstitucional(true)) {
                        e.preventDefault();
                        emailInput.focus();
                        return false;
                    }
                });
            }
        });
    </script>

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
    @elseif(session('failure'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Atención',
                    text: @json(session('failure')),
                    icon: 'error',
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Entendido'
                });
            });
        </script>
    @endif
@endpush
