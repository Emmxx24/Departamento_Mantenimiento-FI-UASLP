@extends('layouts.guest')
@section('title', 'Iniciar Sesión')

@push('scripts')
<script src="{{ asset('js/login.js') }}"></script>
@endpush

@section('content')
<div class="sticky-top">
        <div class="header container-fluid">
            <div class="container">
                <div class="row header">

                    <!-- Logo UASLP y logo de entidad -->
                    <div
                        class="col-12 col-md-auto d-flex justify-content-center justify-content-md-start align-items-center">
                        <a href="https://www.uaslp.mx">
                            <img src="{{ asset('Images/logoUASLP.png') }}" class="img-fluid logoUASLP" />
                        </a>
                        <p class="textoUASLP d-none"><a href="https://www.uaslp.mx">UASLP</a></p>

                        <div class="divisorUASLP-ENTIDAD me-2 ms-2"></div>
                        <div class="divisorUASLP-ENTIDADScroll d-none me-2 ms-3"></div>

                        <!-- Colocar link y ruta de imagen de logo de entidad -->
                        <a href="#" class="me-2">
                            <!-- <img src="/" class="img-fluid logoUASLP" />-->
                        </a>
                        <p class="textoUASLP d-none me-2">
                            FI<!-- Siglas de entidad -->
                        </p>
                    </div>

                    <div
                        class="col-12 col-md-auto flex-grow-1 justify-content-center justify-content-md-end align-items-center pt-md-0 pt-2 d-none d-md-block d-lg-block d-xl-block">
                        <div
                            class="h-75 d-flex flex-column flex-sm-row bd-highlight justify-content-end align-items-sm-end pt-sm-0 pt-5 align-items-center">
                            <div class="p-1 bd-highlight"><a
                                    href="https://www.uaslp.mx/Paginas/Perfiles/3429">Aspirantes</a> <span
                                    class="text-white"> | </span></div>
                            <div class="p-1 px-1 bd-highlight"><a
                                    href="https://www.uaslp.mx/Paginas/Perfiles/3396">Estudiantes</a><span
                                    class="text-white"> | </span></div>
                            <div class="p-1 px-1 bd-highlight"><a
                                    href="https://www.uaslp.mx/Paginas/Perfiles/2359">Egresados</a><span
                                    class="text-white"> | </span></div>
                            <div class="p-1 px-1 bd-highlight"><a
                                    href="https://www.uaslp.mx/Paginas/Perfiles/2360">Docentes</a><span
                                    class="text-white"> | </span></div>
                            <div class="p-1 px-1 bd-highlight"><a
                                    href="https://www.uaslp.mx/Paginas/Perfiles/2361">Administrativos</a></div>
                        </div>
                    </div>

                    <div class="col-12 col-md-auto d-block d-sm-none">
                        <div class="row">
                            <div class="col px-1"><a href="https://www.uaslp.mx/Paginas/Perfiles/3429">Aspirantes</a>
                            </div>
                            <div class="col px-1"><a href="https://www.uaslp.mx/Paginas/Perfiles/3396">Estudiantes</a>
                            </div>
                            <div class="col px-1"><a href="https://www.uaslp.mx/Paginas/Perfiles/2359">Egresados</a>
                            </div>
                            <div class="col px-1"><a href="https://www.uaslp.mx/Paginas/Perfiles/2360">Docentes</a>
                            </div>
                            <div class="col px-1"><a
                                    href="https://www.uaslp.mx/Paginas/Perfiles/2361">Administrativos</a></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="fondoFooter" style="height:10px !important"></div>

    </div>

    <div class="container p-0">
        <div class="fondo-blanco pt-3 pt-md-5 pb-5">
            <div class="container">
                <div class="row justify-content-center">

                    <div class="col-md-5 mb-4">
                        <div class="card p-4 shadow-sm border-0 rounded-4">
                            <h5 class="text-center text-primary mb-3">
                                Departamento de Mantenimiento de la Facultad de Ingeniería
                            </h5>

                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-0 mb-3 small d-flex align-items-center"
                                    role="alert">
                                    <i class="bi bi-check-circle-fill fs-5 me-2 flex-shrink-0 text-success"></i>
                                    <div>
                                        {{ session('success') }}
                                    </div>
                                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            @endif

                            @if (session('message') || session('status') || session('warning'))
                                <div class="alert alert-warning alert-dismissible fade show rounded-3 shadow-sm border-0 mb-3 small d-flex align-items-center"
                                    role="alert">
                                    <i class="bi bi-exclamation-triangle-fill fs-5 me-2 flex-shrink-0 text-warning"></i>
                                    <div>
                                        {{ session('message') ?? session('status') ?? session('warning') }}
                                    </div>
                                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm border-0 mb-3 small d-flex align-items-center"
                                    role="alert">
                                    <i class="bi bi-x-circle-fill fs-5 me-2 flex-shrink-0 text-danger"></i>
                                    <div>
                                        {{ session('error') }}
                                    </div>
                                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            @endif

                            <form autocomplete="off" method="POST" action="{{ route('login.submit') }}">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-secondary">Clave:</label>
                                    <input type="text" id="usuario" name="usuario" class="form-control"
                                        placeholder="Ingresa tu clave" value="{{ old('usuario') }}" required autofocus>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label fw-semibold text-secondary mb-0">Contraseña:</label>
                                        @if ($config?->permiteGestionPassword())
                                            <a href="{{ route('password.request') }}" class="small text-decoration-none text-primary">
                                                ¿Olvidaste tu contraseña?
                                            </a>
                                        @endif
                                    </div>
                                    <input type="password" id="password" name="password" class="form-control"
                                        placeholder="Ingresa tu contraseña" required>
                                </div>

                                <div class="text-center mt-4">
                                    <button type="submit" class="btn btn-primary text-white fw-bold rounded-pill px-5 shadow-sm">
                                        Iniciar sesión
                                    </button>
                                </div>

                                <div id="mensaje" class="text-center mt-3 text-danger small"></div>
                            </form>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="card p-4 shadow-sm border-0 rounded-4">
                            @if ($config && $config->isInstitucional())
                                <h5 class="text-primary mb-3">Administrativos / Docentes</h5>
                                <p>
                                    Pueden ingresar con su cuenta de correo institucional ya que el
                                    usuario y contraseña son los mismos.
                                </p>
                                <p>
                                    Si no cuenta con su cuenta de correo, lo puede habilitar en el siguiente vínculo:
                                    <br>
                                    <a href="https://servicios.ing.uaslp.mx" target="_blank" class="text-decoration-none">Habilita correo</a>
                                </p>

                                <p>
                                    Para más información, acuda con el Responsable de Tecnología de
                                    su Unidad (RTIC). Puede comunicarse a la División de Informática
                                    a la cuenta:
                                    <br>
                                    <a href="mailto:servicios.internet@uaslp.mx" class="text-decoration-none">servicios.internet@uaslp.mx</a>
                                </p>

                                <p class="mb-0">
                                    Si no recuerda su contraseña, la puede cambiar en el siguiente vínculo:
                                    <br>
                                    <a href="https://servicios.ing.uaslp.mx" target="_blank" class="text-decoration-none">Cambio de contraseña</a>
                                </p>
                            @else
                                <h5 class="text-primary mb-3">Departamento de Mantenimiento</h5>
                                <p>
                                    Bienvenido al portal del Departamento de Mantenimiento de la Facultad de Ingeniería.
                                </p>
                                <p>
                                    A través de este sistema puedes registrar y dar seguimiento a solicitudes de servicio, reportar incidencias en aulas, cubículos y laboratorios, y consultar el estado de tus requerimientos.
                                </p>
                                <p class="mb-0">
                                    Para solicitar el alta de una cuenta o resolver dudas con tus credenciales de acceso, ponte en contacto con el personal administrativo del departamento.
                                </p>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Footer único unificado mediante componente --}}
    <x-footer></x-footer>
@endsection