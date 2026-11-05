@extends('layouts.guest')
@section('title', 'Restablecer Contraseña')

@section('content')
    <x-header></x-header>

    <div class="container p-0 flex-fill d-flex align-items-center py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-md-6 col-lg-5">

                    <div class="card p-4 p-md-5 shadow-sm border-0 rounded-4">
                        <div class="text-center mb-4">
                            <div class="d-inline-flex p-3 rounded-circle bg-success-subtle text-success mb-3">
                                <i class="bi bi-key fs-2"></i>
                            </div>
                            <h4 class="fw-bold text-primary mb-1">Nueva Contraseña</h4>
                            <p class="text-muted small mb-0">
                                Ingresa el código de 6 dígitos enviado a tu correo institucional y define tu nueva contraseña.
                            </p>
                        </div>

                        {{-- ALERTAS DE SESIÓN O ERRORES --}}
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show rounded-3 small border-0 mb-3" role="alert">
                                <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show rounded-3 small border-0 mb-3" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $errors->first() }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                            </div>
                        @endif

                        <form autocomplete="off" method="POST" action="{{ route('password.update') }}">
                            @csrf

                            {{-- CORREO INSTITUCIONAL --}}
                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold text-secondary">
                                    Correo Institucional:
                                </label>
                                <input type="email"
                                       id="email"
                                       name="email"
                                       class="form-control rounded-2 @error('email') is-invalid @enderror"
                                       value="{{ old('email', session('email_enviado', '')) }}"
                                       placeholder="usuario@uaslp.mx"
                                       required>
                            </div>

                            {{-- CÓDIGO OTP --}}
                            <div class="mb-3">
                                <label for="codigo" class="form-label fw-semibold text-secondary">
                                    Código de Verificación (6 dígitos):
                                </label>
                                <input type="text"
                                       id="codigo"
                                       name="codigo"
                                       class="form-control text-center font-monospace rounded-2 fw-bold @error('codigo') is-invalid @enderror"
                                       style="letter-spacing: 6px; font-size: 1.3rem;"
                                       maxlength="6"
                                       inputmode="numeric"
                                       placeholder="000000"
                                       value="{{ old('codigo') }}"
                                       required
                                       autofocus>
                                <div class="form-text small text-muted text-center">Revisa tu bandeja de entrada o spam para obtener tu código.</div>
                            </div>

                            {{-- NUEVA CONTRASEÑA --}}
                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold text-secondary">
                                    Nueva Contraseña:
                                </label>
                                <input type="password"
                                       id="password"
                                       name="password"
                                       class="form-control rounded-2 @error('password') is-invalid @enderror"
                                       placeholder="Mínimo 8 caracteres"
                                       required>
                            </div>

                            {{-- CONFIRMAR NUEVA CONTRASEÑA --}}
                            <div class="mb-4">
                                <label for="password_confirmation" class="form-label fw-semibold text-secondary">
                                    Confirmar Nueva Contraseña:
                                </label>
                                <input type="password"
                                       id="password_confirmation"
                                       name="password_confirmation"
                                       class="form-control rounded-2"
                                       placeholder="Repite tu contraseña"
                                       required>
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary text-white fw-bold rounded-pill py-2 shadow-sm">
                                    <i class="bi bi-check-lg me-1"></i> Restablecer Contraseña
                                </button>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                <a href="{{ route('password.request') }}" class="text-decoration-none small text-muted">
                                    <i class="bi bi-arrow-repeat me-1"></i> Reenviar código
                                </a>
                                <a href="{{ route('login') }}" class="text-decoration-none small text-muted">
                                    Volver al login
                                </a>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <x-footer></x-footer>
@endsection
