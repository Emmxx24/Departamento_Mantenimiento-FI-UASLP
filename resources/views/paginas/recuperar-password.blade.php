@extends('layouts.guest')
@section('title', 'Recuperar Contraseña')

@section('content')
    <x-header></x-header>

    <div class="container p-0 flex-fill d-flex align-items-center py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-md-6 col-lg-5">

                    <div class="card p-4 p-md-5 shadow-sm border-0 rounded-4">
                        <div class="text-center mb-4">
                            <div class="d-inline-flex p-3 rounded-circle bg-primary-subtle text-primary mb-3">
                                <i class="bi bi-shield-lock fs-2"></i>
                            </div>
                            <h4 class="fw-bold text-primary mb-1">Recuperar Contraseña</h4>
                            <p class="text-muted small mb-0">
                                Ingresa tu clave o correo institucional. Te enviaremos un código de verificación de 6 dígitos a tu correo institucional.
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

                        <form autocomplete="off" method="POST" action="{{ route('password.email') }}">
                            @csrf

                            <div class="mb-4">
                                <label for="identificador" class="form-label fw-semibold text-secondary">
                                    Clave o Correo Institucional:
                                </label>
                                <input type="text"
                                       id="identificador"
                                       name="identificador"
                                       class="form-control rounded-2 @error('identificador') is-invalid @enderror"
                                       placeholder="Ej. 323352 o usuario@uaslp.mx"
                                       value="{{ old('identificador') }}"
                                       required
                                       autofocus>
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary text-white fw-bold rounded-pill py-2 shadow-sm">
                                    <i class="bi bi-send me-1"></i> Enviar código de verificación
                                </button>
                            </div>

                            <div class="text-center">
                                <a href="{{ route('login') }}" class="text-decoration-none small text-muted">
                                    <i class="bi bi-arrow-left me-1"></i> Volver al inicio de sesión
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
