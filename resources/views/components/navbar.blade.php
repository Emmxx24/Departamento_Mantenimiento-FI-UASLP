<nav class="navbar navbar-expand-lg bg-white py-3 shadow-sm">
    <div class="container-fluid px-2">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal"
            aria-controls="menuPrincipal" aria-expanded="false" aria-label="Navegación">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-center" id="menuPrincipal">
            <div
                class="navbar-nav align-items-center gap-1 gap-lg-4 mt-3 mt-lg-0 text-center w-100 justify-content-center">
                @if (Auth::check())
                    @if ($sistemaActivo)
                        <a class="nav-link fw-bold navbar-a-style" href="{{ route('home') }}">Inicio</a>
                        @if (Auth::user()->rol !== 'Solicitante')
                            <a class="nav-link fw-bold navbar-a-style" href="{{ route('tickets.index') }}">Administrar
                                tickets</a>
                            <a class="nav-link fw-bold navbar-a-style" href="{{ route('tickets.crear') }}">Crear
                                ticket</a>
                            <a class="nav-link fw-bold navbar-a-style"
                                href="{{ route('tickets.borradores') }}">Borradores</a>

                            @if (Auth::user()->rol == 'Superadministrador')
                                <a class="nav-link fw-bold navbar-a-style" href="{{ route('reportes') }}">Reportes</a>
                                {{-- Dropdown Catálogos --}}
                                <div class="nav-item dropdown mt-2 mt-lg-0">
                                    <a class="nav-link fw-bold navbar-a-style dropdown-toggle" href="#"
                                        role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Catálogos
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item fw-bold navbar-a-style"
                                                href="{{ route('inventario') }}">
                                                <i class="me-2"></i> Inventario
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item fw-bold navbar-a-style"
                                                href="{{ route('servicios') }}">
                                                <i class="me-2"></i> Servicios
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item fw-bold navbar-a-style"
                                                href="{{ route('edificios') }}">
                                                <i class="me-2"></i> Edificios
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item fw-bold navbar-a-style"
                                                href="{{ route('lugares') }}">
                                                <i class="me-2"></i> Lugares
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item fw-bold navbar-a-style"
                                                href="{{ route('marcas') }}">
                                                <i class="me-2"></i> Marcas
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item fw-bold navbar-a-style"
                                                href="{{ route('unidades') }}">
                                                <i class="me-2"></i> Unidades
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item fw-bold navbar-a-style"
                                                href="{{ route('usuarios') }}">
                                                <i class="me-2"></i> Usuarios
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            @elseif (Auth::user()->rol == 'Administrador')
                                <a class="nav-link fw-bold navbar-a-style" href="{{ route('inventario') }}">
                                    <i class="me-2"></i> Inventario
                                </a>
                            @endif
                        @else
                            <a class="nav-link fw-bold navbar-a-style" href="{{ route('tickets.crear') }}">Crear
                                ticket</a>
                            <a class="nav-link fw-bold navbar-a-style" href="{{ route('tickets.index') }}">Mis
                                tickets</a>
                            <a class="nav-link fw-bold navbar-a-style" href="{{ route('tickets.borradores') }}">
                                <i class="fas fa-file-alt"></i> Mis Borradores
                            </a>
                        @endif
                    @else
                        {{-- Sistema NO inicializado: Bloqueo de navegación --}}
                        {{-- <span class="navbar-text fw-bold text-muted small">
                            <i class="bi bi-gear-fill me-1"></i> Modo de Inicialización
                        </span> --}}
                    @endif

                    {{-- Dropdown usuario: estilo SaaS compacto con tarjeta de perfil interna --}}
                    @php
                        $primerNombre = explode(' ', trim(Auth::user()->nombre))[0];
                    @endphp
                    <div class="nav-item dropdown ms-lg-3 mt-2 mt-lg-0">
                        <a class="nav-link fw-bold dropdown-toggle d-inline-flex align-items-center gap-2 navbar-user-link navbar-a-style"
                            href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle fs-5" style="color: #004A98;"></i>
                            <span class="text-truncate" style="max-width: 140px;"
                                title="{{ Auth::user()->nombre }}">{{ $primerNombre }}</span>
                            <span class="badge rounded-pill fw-semibold"
                                style="font-size: 0.72rem; background-color: #E7ECF1; color: #004A98;">
                                {{ Auth::user()->rol === 'Superadministrador' ? 'Superadmin' : Auth::user()->rol }}
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2 py-2"
                            style="min-width: 240px; max-width: 290px;">
                            {{-- Mini tarjeta de información de usuario --}}
                            <li class="px-3 py-2 border-bottom mb-2 bg-light-subtle rounded-top-2">
                                <div class="fw-bold text-dark text-truncate small"
                                    title="{{ Auth::user()->nombre }} {{ Auth::user()->ap_pat }} {{ Auth::user()->ap_mat }}">
                                    {{ Auth::user()->nombre }} {{ Auth::user()->ap_pat }}
                                </div>
                                <div class="text-muted text-truncate" style="font-size: 0.78rem;"
                                    title="{{ Auth::user()->email }}">
                                    {{ Auth::user()->email }}
                                </div>
                            </li>
                            @if ($config?->permiteGestionPassword())
                                <li>
                                    <button type="button"
                                        class="dropdown-item fw-semibold text-secondary d-flex align-items-center gap-2 py-2 px-3"
                                        data-bs-toggle="modal" data-bs-target="#modalCambiarPasswordPropio">
                                        <i class="bi bi-key text-primary"></i>
                                        Cambiar Contraseña
                                    </button>
                                </li>
                                <li><hr class="dropdown-divider my-1"></li>
                            @endif
                            <li>
                                <form action="{{ route('logout') }}" method="POST" class="m-0 p-0">
                                    @csrf
                                    <button type="submit"
                                        class="dropdown-item text-danger fw-bold d-flex align-items-center gap-2 py-2 px-3">
                                        <i class="bi bi-box-arrow-right"></i>
                                        Cerrar Sesión
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>
</nav>

{{-- MODAL CAMBIAR CONTRASEÑA EN PERFIL (SOLO EN MODO LOCAL) --}}
@if (Auth::check() && $config?->permiteGestionPassword())
    <div class="modal fade" id="modalCambiarPasswordPropio" tabindex="-1" aria-labelledby="modalCambiarPasswordPropioLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <form action="{{ route('perfil.password.update') }}" method="POST" autocomplete="off">
                    @csrf
                    @method('PUT')
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold text-dark" id="modalCambiarPasswordPropioLabel">
                            <i class="bi bi-key text-primary me-2"></i>Cambiar Contraseña
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body py-4">
                        <p class="text-muted small mb-3">
                            Actualiza tu contraseña de acceso. Utiliza al menos 8 caracteres para mantener segura tu cuenta.
                        </p>

                        <div class="mb-3">
                            <label for="password_actual" class="form-label fw-bold small text-muted">Contraseña actual <span class="text-danger">*</span></label>
                            <input type="password" name="password_actual" id="password_actual" class="form-control rounded-2 @error('password_actual') is-invalid @enderror" placeholder="Ingresa tu contraseña actual" required>
                            @error('password_actual')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="nuevo_password" class="form-label fw-bold small text-muted">Nueva contraseña <span class="text-danger">*</span></label>
                            <input type="password" name="nuevo_password" id="nuevo_password" class="form-control rounded-2 @error('nuevo_password') is-invalid @enderror" placeholder="Mínimo 8 caracteres" required>
                            @error('nuevo_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-2">
                            <label for="nuevo_password_confirmation" class="form-label fw-bold small text-muted">Confirmar nueva contraseña <span class="text-danger">*</span></label>
                            <input type="password" name="nuevo_password_confirmation" id="nuevo_password_confirmation" class="form-control rounded-2" placeholder="Repite la nueva contraseña" required>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary text-white fw-bold rounded-pill px-4 shadow-sm">
                            <i class="bi bi-check-lg me-1"></i> Actualizar Contraseña
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if ($errors->has('password_actual') || $errors->has('nuevo_password') || $errors->has('nuevo_password_confirmation'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const modalEl = document.getElementById('modalCambiarPasswordPropio');
                if (modalEl) {
                    const modal = new bootstrap.Modal(modalEl);
                    modal.show();
                }
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al cambiar contraseña',
                        text: @json($errors->first()),
                        confirmButtonColor: '#dc3545',
                        confirmButtonText: 'Entendido'
                    });
                }
            });
        </script>
    @endif

    @if (session('password_success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Contraseña actualizada!',
                        text: @json(session('password_success')),
                        confirmButtonColor: '#004A98',
                        confirmButtonText: 'Aceptar'
                    });
                }
            });
        </script>
    @endif
@endif
