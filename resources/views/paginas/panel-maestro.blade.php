@extends('layouts.app')

@section('title', 'Configuración del Sistema')

@section('content')
    <div class="container mt-4 mb-5">

        {{-- TÍTULO --}}
        <x-page-title titulo="Configuración del Sistema" subtitulo="Parámetros de autenticación y límites administrativos" />

        {{-- ALERTAS DE SESIÓN --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2 align-middle"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        @if (session('warning'))
            <div class="alert alert-warning alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2 align-middle"></i>{{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        @if (isset($errors) && $errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
                <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-exclamation-circle-fill me-2 fs-5"></i>
                    <strong class="fs-6">Verifica los siguientes campos:</strong>
                </div>
                <ul class="mb-0 ps-3 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        {{-- FORMULARIO PRINCIPAL --}}
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4 p-md-5">

                <form action="{{ route('panel.maestro.update') }}" method="POST" autocomplete="off">
                    @csrf
                    @method('PUT')

                    {{-- 1. MODO DE AUTENTICACIÓN --}}
                    <div class="mb-4 pb-3 border-bottom">
                        <h6 class="fw-bold text-primary mb-2">
                            <i class="bi bi-shield-lock me-1"></i> Modo de Autenticación
                        </h6>
                        <p class="text-muted small mb-3">Selecciona el método de inicio de sesión que utilizarán los
                            usuarios del sistema.</p>

                        <div class="d-flex flex-column gap-3">
                            {{-- Modo Local --}}
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="login_institucional" id="modo_local"
                                    value="0"
                                    {{ old('login_institucional', $config->login_institucional ? '1' : '0') == '0' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold text-dark" for="modo_local">
                                    Autenticación Local
                                </label>
                                <div class="text-muted small">
                                    Cuentas y contraseñas administradas en la base de datos interna. Permite autogestión de
                                    contraseña vía correo.
                                </div>
                            </div>

                            {{-- Modo Institucional --}}
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="login_institucional"
                                    id="modo_institucional" value="1"
                                    {{ old('login_institucional', $config->login_institucional ? '1' : '0') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold text-dark" for="modo_institucional">
                                    Autenticación Institucional UASLP
                                </label>
                                <div class="text-muted small">
                                    Modo indicativo preparado para la API institucional. Oculta la administración de
                                    contraseñas locales.
                                </div>
                            </div>
                        </div>

                        {{-- Vigencia de Código OTP --}}
                        <div class="mt-3 pt-3 border-top">
                            <label for="minutos_expiracion_otp" class="form-label fw-bold small text-dark">
                                Vigencia de Códigos OTP de Recuperación (minutos) <span class="text-danger">*</span>
                            </label>
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="number" min="1" max="120"
                                        class="form-control rounded-3 @error('minutos_expiracion_otp') is-invalid @enderror"
                                        id="minutos_expiracion_otp" name="minutos_expiracion_otp"
                                        value="{{ old('minutos_expiracion_otp', $config->minutos_expiracion_otp) }}" required>
                                    @error('minutos_expiracion_otp')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text small text-muted">
                                        Tiempo en minutos durante el cual el código de seguridad enviado al correo institucional es válido.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. LÍMITES DE ADMINISTRADORES --}}
                    <div class="mb-4 pb-3 border-bottom">
                        <h6 class="fw-bold text-primary mb-2">
                            <i class="bi bi-people me-1"></i> Límites de Cuentas Administrativas
                        </h6>
                        <p class="text-muted small mb-3">Define los topes máximos de usuarios con privilegios activos
                            permitidos en el sistema.</p>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="max_superadmins" class="form-label fw-bold small text-dark">
                                    Máximo Superadministradores <span class="text-danger">*</span>
                                </label>
                                <input type="number" min="1" max="50"
                                    class="form-control rounded-3 @error('max_superadmins') is-invalid @enderror"
                                    id="max_superadmins" name="max_superadmins"
                                    value="{{ old('max_superadmins', $config->max_superadmins) }}" required>
                                @error('max_superadmins')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text small text-muted">
                                    Actualmente activos: <strong>{{ $totalSuperadmins }}</strong>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="max_admins" class="form-label fw-bold small text-dark">
                                    Máximo Administradores <span class="text-danger">*</span>
                                </label>
                                <input type="number" min="1" max="100"
                                    class="form-control rounded-3 @error('max_admins') is-invalid @enderror" id="max_admins"
                                    name="max_admins" value="{{ old('max_admins', $config->max_admins) }}" required>
                                @error('max_admins')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text small text-muted">
                                    Actualmente activos: <strong>{{ $totalAdmins }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 3. DATOS DE CONTACTO Y SOPORTE --}}
                    <div class="mb-4 pb-3 border-bottom">
                        <h6 class="fw-bold text-primary mb-2">
                            <i class="bi bi-envelope-at me-1"></i> Información de Contacto y Soporte
                        </h6>
                        <p class="text-muted small mb-3">Datos de asistencia técnica que se muestran a los usuarios en
                            pantalla.</p>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="correo_contacto" class="form-label fw-bold small text-dark">
                                    Correo de Soporte Técnico <span class="text-danger">*</span>
                                </label>
                                <input type="email" maxlength="100"
                                    class="form-control rounded-3 @error('correo_contacto') is-invalid @enderror"
                                    id="correo_contacto" name="correo_contacto"
                                    value="{{ old('correo_contacto', $config->correo_contacto) }}"
                                    placeholder="ejemplo@uaslp.mx" required>
                                @error('correo_contacto')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="telefono_contacto" class="form-label fw-bold small text-dark">
                                    Teléfono / Extensión <span class="text-danger">*</span>
                                </label>
                                <input type="text" maxlength="50"
                                    class="form-control rounded-3 @error('telefono_contacto') is-invalid @enderror"
                                    id="telefono_contacto" name="telefono_contacto"
                                    value="{{ old('telefono_contacto', $config->telefono_contacto) }}"
                                    placeholder="(444) 826-2300 ext. 1234" required>
                                @error('telefono_contacto')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- 4. CONFIRMACIÓN Y BOTONES --}}
                    <div class="pt-2">
                        <div class="row align-items-center g-3">
                            <div class="col-md-6">
                                <label for="password_confirmacion" class="form-label fw-bold small text-dark mb-1">
                                    Contraseña de Confirmación <span class="text-danger">*</span>
                                </label>
                                <input type="password" name="password_confirmacion" id="password_confirmacion"
                                    class="form-control rounded-3 @error('password_confirmacion') is-invalid @enderror"
                                    placeholder="Ingresa tu contraseña actual" required>
                                @error('password_confirmacion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text small text-muted">Requerida para autorizar y consolidar los cambios
                                    en el sistema.</div>
                            </div>

                            <div class="col-md-6 text-md-end pt-3 pt-md-0">
                                <div class="d-flex justify-content-md-end gap-2 flex-wrap">
                                    @if ($config->sistema_inicializado && !$config->login_institucional)
                                        <a href="{{ route('home') }}"
                                            class="btn btn-outline-secondary fw-bold rounded-pill px-4">
                                            Volver
                                        </a>
                                    @endif
                                    <button type="submit"
                                        class="btn btn-primary text-white fw-bold rounded-pill px-4 shadow-sm">
                                        <i class="bi bi-check-lg me-1"></i> Guardar Configuración
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </form>

            </div>
        </div>

    </div>
@endsection
