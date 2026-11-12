<?php

namespace App\Http\Controllers;

use App\Exports\UsuariosPlantillaExport;
use App\Imports\UsuariosImport;
use App\Models\Configuracion;
use App\Models\User;
use App\Services\TicketMailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class UsuarioController extends Controller
{
    /**
     * Muestra la lista unificada de todos los usuarios del sistema.
     */
    public function index()
    {
        $config = Configuracion::obtener();
        $metricas = $this->obtenerMetricasCupos($config);

        // Consulta limpia y ligera de todos los usuarios
        $usuarios = User::orderByRaw("CASE 
                WHEN rol = 'Superadministrador' THEN 1 
                WHEN rol = 'Administrador' THEN 2 
                ELSE 3 
            END ASC")
            ->orderBy('nombre', 'asc')
            ->orderBy('ap_pat', 'asc')
            ->get();

        return view('paginas.usuarios', array_merge(compact('usuarios', 'config', 'metricas'), $metricas));
    }

    /**
     * Muestra el formulario para registrar un nuevo usuario.
     */
    public function create()
    {
        $usuario = new User();
        $roles = ['Superadministrador', 'Administrador', 'Solicitante'];
        $config = Configuracion::obtener();
        $metricas = $this->obtenerMetricasCupos($config);
        $esInstitucional = $config->isInstitucional();

        return view('paginas.usuarios-form', array_merge(compact('usuario', 'roles', 'config', 'esInstitucional'), $metricas));
    }

    /**
     * Almacena un nuevo usuario en la base de datos de forma atómica.
     */
    public function store(Request $request)
    {
        $config = Configuracion::obtener();
        $esInstitucional = $config->isInstitucional();

        $this->validarFormulario($request, null, $esInstitucional);

        DB::beginTransaction();

        try {
            $this->validarLimitesCupo($request->rol);

            $nuevoUsuario = new User();
            $nuevoUsuario->clave = trim($request->clave);
            $nuevoUsuario->nombre = trim($request->nombre);
            $nuevoUsuario->ap_pat = trim($request->ap_pat);
            $nuevoUsuario->ap_mat = $request->filled('ap_mat') ? trim($request->ap_mat) : null;
            $nuevoUsuario->email = strtolower(trim($request->email));
            $nuevoUsuario->rol = $request->rol;
            $nuevoUsuario->activo = 1;

            if ($request->filled('password')) {
                $nuevoUsuario->password = Hash::make($request->password);
            } elseif ($esInstitucional) {
                // En modo institucional no se asigna contraseña local
                $nuevoUsuario->password = null;
            }

            $nuevoUsuario->save();

            DB::commit();

            // Notificar bienvenida al usuario registrado
            $passwordInicial = (!$esInstitucional && $request->filled('password')) ? $request->password : null;
            TicketMailService::enviarBienvenidaUsuario($nuevoUsuario, $passwordInicial, $esInstitucional);

            return redirect()->route('usuarios')->with('success', 'Usuario registrado correctamente.');
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al registrar usuario: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return back()
                ->withInput()
                ->with('failure', 'Ocurrió un error inesperado al registrar el usuario.');
        }
    }

    /**
     * Muestra el formulario para editar los datos de un usuario existente.
     */
    public function edit($id)
    {
        $usuario = User::findOrFail($id);
        $roles = ['Superadministrador', 'Administrador', 'Solicitante'];
        $config = Configuracion::obtener();
        $metricas = $this->obtenerMetricasCupos($config);
        $esInstitucional = $config->isInstitucional();
        $esSuperadminRaiz = $config->isSuperadminRaiz($usuario->clave);

        return view('paginas.usuarios-form', array_merge(compact('usuario', 'roles', 'config', 'esInstitucional', 'esSuperadminRaiz'), $metricas));
    }

    /**
     * Actualiza los datos de un usuario de forma atómica.
     */
    public function update(Request $request, $id)
    {
        $usuario = User::findOrFail($id);
        $config = Configuracion::obtener();
        $esInstitucional = $config->isInstitucional();
        $esSuperadminRaiz = $config->isSuperadminRaiz($usuario->clave);
        $esPropio = (Auth::check() && Auth::id() === $usuario->id);

        $this->validarFormulario($request, $usuario, $esInstitucional, $esSuperadminRaiz);

        // Si intenta cambiar de rol, verificar que no sea el Superadmin raíz ni el propio usuario en sesión
        if ($request->filled('rol') && $usuario->rol !== $request->rol) {
            if ($esSuperadminRaiz) {
                return back()->withInput()->with('failure', 'No es posible modificar el rol del Superadministrador principal.');
            }
            if ($esPropio) {
                return back()->withInput()->with('failure', 'No puedes modificar tu propio rol.');
            }
        }

        $rolAnterior = $usuario->rol;
        $cambioPassword = $request->filled('password');

        DB::beginTransaction();

        try {
            if (!$esSuperadminRaiz && !$esPropio && $usuario->rol !== $request->rol) {
                if ($usuario->activo) {
                    $this->validarProteccionSuperadmin($usuario, $request->rol);
                    $this->validarLimitesCupo($request->rol, $usuario->rol);
                }
            }

            // La clave y el rol del Superadministrador raíz permanecen inmutables; el propio usuario no puede cambiar su rol
            if (!$esSuperadminRaiz) {
                $usuario->clave = trim($request->clave);
                if (!$esPropio) {
                    $usuario->rol = $request->rol;
                }
            }

            $usuario->nombre = trim($request->nombre);
            $usuario->ap_pat = trim($request->ap_pat);
            $usuario->ap_mat = $request->filled('ap_mat') ? trim($request->ap_mat) : null;
            $usuario->email  = strtolower(trim($request->email));

            if ($request->filled('password')) {
                if ($esSuperadminRaiz && !$esPropio) {
                    DB::rollBack();
                    return back()->withInput()->with('failure', 'No tienes permisos para modificar la contraseña del Superadministrador principal.');
                }
                $usuario->password = Hash::make($request->password);
            }

            $usuario->save();

            DB::commit();

            // Notificar cambio de contraseña si se actualizó
            if ($cambioPassword) {
                TicketMailService::enviarPasswordActualizada($usuario, $request->password);
            }

            // Notificar cambio de rol si se modificó
            if ($usuario->rol !== $rolAnterior) {
                TicketMailService::enviarRolActualizado($usuario, $usuario->rol);
            }

            return redirect()->route('usuarios')->with('success', 'Usuario actualizado correctamente.');
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al actualizar usuario ID {$id}: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return back()
                ->withInput()
                ->with('failure', 'Ocurrió un error inesperado al actualizar el usuario.');
        }
    }

    /**
     * Alterna el estado activo de un usuario (Activar / Desactivar).
     */
    public function toggleActivo($id)
    {
        $usuario = User::findOrFail($id);
        $config = Configuracion::obtener();

        // 1. Prohibido auto-desactivarse
        if (Auth::id() == $usuario->id) {
            return redirect()->route('usuarios')->with('failure', 'No puedes desactivar tu propia cuenta activa.');
        }

        // 2. Prohibido desactivar al Superadministrador raíz
        if ($config->isSuperadminRaiz($usuario->clave)) {
            return redirect()->route('usuarios')->with('failure', 'El Superadministrador principal no puede ser desactivado.');
        }

        DB::beginTransaction();

        try {
            // 3. Prohibido desactivar al único Superadministrador activo del sistema
            if ($usuario->activo && $usuario->rol === 'Superadministrador') {
                $otrosSuperadmins = User::where('rol', 'Superadministrador')
                    ->where('activo', 1)
                    ->where('id', '!=', $usuario->id)
                    ->count();

                if ($otrosSuperadmins === 0) {
                    DB::rollBack();
                    return redirect()->route('usuarios')->with('failure', 'No es posible desactivar al único Superadministrador activo del sistema.');
                }
            }

            // 4. Si se va a reactivar, validar que haya cupo disponible para su rol
            if (!$usuario->activo) {
                $this->validarLimitesCupo($usuario->rol);
            }

            $nuevoEstado = $usuario->activo ? 0 : 1;
            $usuario->activo = $nuevoEstado;
            $usuario->save();

            DB::commit();

            // Notificar cambio de estado de la cuenta
            TicketMailService::enviarCuentaEstado($usuario, (bool) $nuevoEstado);

            $accion = $nuevoEstado ? 'reactivado' : 'desactivado';
            return redirect()->route('usuarios')->with('success', "Usuario {$usuario->nombre_completo} {$accion} correctamente.");
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al alternar estado activo del usuario ID {$id}: " . $e->getMessage());

            return redirect()->route('usuarios')->with('failure', 'Error al modificar el estado del usuario.');
        }
    }

    /**
     * Permite cambiar rápidamente el rol de un usuario con validación de topes.
     */
    public function cambiarRol(Request $request, $id)
    {
        $request->validate([
            'rol' => 'required|in:Superadministrador,Administrador,Solicitante',
        ], [
            'rol.required' => 'El rol es obligatorio.',
            'rol.in'       => 'El rol seleccionado no es válido.',
        ]);

        $usuario = User::findOrFail($id);
        $config = Configuracion::obtener();

        // Prohibir que el usuario en sesión cambie su propio rol
        if (Auth::id() == $usuario->id && $request->rol !== $usuario->rol) {
            return back()->with('failure', 'No puedes modificar tu propio rol.');
        }

        if ($usuario->rol === $request->rol) {
            return back()->with('success', "El usuario ya tiene asignado el rol {$request->rol}.");
        }

        // Proteger Superadmin raíz
        if ($config->isSuperadminRaiz($usuario->clave) && $request->rol !== 'Superadministrador') {
            return back()->with('failure', 'No es posible cambiar el rol del Superadministrador principal.');
        }

        DB::beginTransaction();

        try {
            if ($usuario->activo) {
                $this->validarProteccionSuperadmin($usuario, $request->rol);
                $this->validarLimitesCupo($request->rol, $usuario->rol);
            }

            $usuario->rol = $request->rol;
            $usuario->save();

            DB::commit();

            // Notificar cambio de rol
            TicketMailService::enviarRolActualizado($usuario, $request->rol);

            return back()->with('success', "Rol de {$usuario->nombre_completo} actualizado a {$request->rol}.");
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al cambiar rol a usuario ID {$id}: " . $e->getMessage());

            return back()->with('failure', 'Error al cambiar el rol del usuario.');
        }
    }

    /**
     * Elimina físicamente a un usuario únicamente si no cuenta con historial asociado.
     * Si cuenta con registros asociados, redirige con aviso orientando a desactivar.
     */
    public function destroy($id)
    {
        $usuario = User::findOrFail($id);
        $config = Configuracion::obtener();

        // 1. Prohibido auto-eliminarse
        if (Auth::id() == $usuario->id) {
            return redirect()->route('usuarios')->with('failure', 'No puedes eliminar tu propia cuenta en sesión activa.');
        }

        // 2. Prohibido eliminar al Superadministrador raíz
        if ($config->isSuperadminRaiz($usuario->clave)) {
            return redirect()->route('usuarios')->with('failure', 'El Superadministrador principal no puede ser eliminado.');
        }

        // 3. Prohibido eliminar al único superadministrador activo
        if ($usuario->rol === 'Superadministrador' && $usuario->activo) {
            $otrosSuperadmins = User::where('rol', 'Superadministrador')
                ->where('activo', 1)
                ->where('id', '!=', $usuario->id)
                ->count();

            if ($otrosSuperadmins === 0) {
                return redirect()->route('usuarios')->with('failure', 'No es posible eliminar al único Superadministrador del sistema.');
            }
        }

        // 4. Integridad referencial: si tiene historial, no se debe eliminar físicamente
        if ($usuario->tieneRegistrosAsociados()) {
            return redirect()->route('usuarios')->with(
                'failure',
                'No es posible eliminar este usuario porque cuenta con registros asociados en el sistema (tickets, mensajes o reportes). Utiliza la opción Desactivar para revocar su acceso.'
            );
        }

        DB::beginTransaction();

        try {
            $usuario->delete();
            DB::commit();

            // Notificar al usuario la baja de su cuenta
            TicketMailService::enviarCuentaEstado($usuario, false);

            return redirect()->route('usuarios')->with('success', 'Usuario eliminado permanentemente del sistema.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al eliminar permanentemente usuario ID {$id}: " . $e->getMessage());

            return redirect()->route('usuarios')->with('failure', 'Error al eliminar el usuario.');
        }
    }

    /**
     * Descarga la plantilla oficial en formato Excel (.xlsx) para el aprovisionamiento masivo de usuarios.
     */
    public function descargarPlantilla()
    {
        return Excel::download(new UsuariosPlantillaExport(), 'plantilla_usuarios_uaslp.xlsx');
    }

    /**
     * Procesa la importación masiva de usuarios desde un archivo Excel oficial (.xlsx / .xls).
     */
    public function importarExcel(Request $request)
    {
        $request->validate([
            'archivo_excel' => 'required|file|mimes:xlsx,xls|max:10240',
        ], [
            'archivo_excel.required' => 'Debes seleccionar un archivo Excel para realizar la importación.',
            'archivo_excel.file'     => 'El archivo seleccionado no es válido.',
            'archivo_excel.mimes'    => 'El archivo debe tener formato Excel (.xlsx o .xls).',
            'archivo_excel.max'      => 'El archivo no puede superar los 10 megabytes.',
        ]);

        DB::beginTransaction();

        try {
            $importador = new UsuariosImport();
            Excel::import($importador, $request->file('archivo_excel'));

            $resumen = $importador->getResumen();

            DB::commit();

            return redirect()->route('usuarios')->with('import_resumen', $resumen);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error crítico durante la importación masiva de usuarios: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('usuarios')->with(
                'failure',
                'Ocurrió un error inesperado al procesar el archivo Excel. Asegúrate de utilizar la plantilla oficial y que el archivo no esté corrupto.'
            );
        }
    }

    /**
     * Valida que una dirección de correo pertenezca estrictamente al dominio institucional @uaslp.mx.
     */
    public static function esCorreoInstitucionalValido(?string $email): bool
    {
        if (empty($email)) {
            return false;
        }

        $email = strtolower(trim($email));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        // Estricta verificación del dominio @uaslp.mx
        return str_ends_with($email, '@uaslp.mx');
    }

    // =========================================================================
    // MÉTODOS PRIVADOS DE VALIDACIÓN Y REGLAS MODULARES (DRY)
    // =========================================================================

    /**
     * Retorna las métricas y topes de cupos leídos directamente de la configuración activa.
     */
    private function obtenerMetricasCupos(Configuracion $config): array
    {
        $totalSuperadmins = User::where('rol', 'Superadministrador')->where('activo', 1)->count();
        $totalAdmins = User::where('rol', 'Administrador')->where('activo', 1)->count();
        $totalSolicitantes = User::where('rol', 'Solicitante')->where('activo', 1)->count();
        $totalInactivos = User::where('activo', 0)->count();

        return [
            'totalSuperadmins'          => $totalSuperadmins,
            'totalAdmins'               => $totalAdmins,
            'totalSolicitantes'         => $totalSolicitantes,
            'totalInactivos'            => $totalInactivos,
            'maxSuperadmins'            => $config->max_superadmins,
            'maxAdmins'                 => $config->max_admins,
            'claveSuperadminOrigen'     => $config->clave_superadmin_origen,
            'limiteSuperadminAlcanzado' => ($totalSuperadmins >= $config->max_superadmins),
            'limiteAdminAlcanzado'      => ($totalAdmins >= $config->max_admins),
        ];
    }

    /**
     * Valida los campos comunes del formulario de usuario, contraseñas y unicidad de clave y email.
     */
    private function validarFormulario(Request $request, ?User $usuario = null, bool $esInstitucional = false, bool $esSuperadminRaiz = false): void
    {
        $esEdicion = ($usuario !== null);

        $reglas = [
            'nombre'   => 'required|string|max:50',
            'ap_pat'   => 'required|string|max:50',
            'ap_mat'   => 'nullable|string|max:50',
            'email'    => 'required|string|email|max:100',
        ];

        $esPropio = (Auth::check() && $usuario !== null && Auth::id() === $usuario->id);

        // Si es el Superadmin raíz, no se valida ni altera clave ni rol; si es la propia cuenta no se altera el rol
        if (!$esSuperadminRaiz) {
            $reglas['clave'] = 'required|string|max:20';
            if (!$esPropio) {
                $reglas['rol'] = 'required|in:Superadministrador,Administrador,Solicitante';
            }
        }

        // Validación condicional de contraseña según modo de login
        if (!$esInstitucional) {
            if (!$esEdicion) {
                // Al crear en modo local, la contraseña es obligatoria
                $reglas['password'] = 'required|string|min:8|confirmed';
            } elseif ($request->filled('password')) {
                // Si es el Superadmin raíz y otro usuario intenta cambiar su contraseña, bloquear
                if ($esSuperadminRaiz && !$esPropio) {
                    throw ValidationException::withMessages([
                        'password' => 'No tienes permisos para modificar la contraseña del Superadministrador principal.'
                    ]);
                }
                // Al editar en modo local, solo si se proporciona
                $reglas['password'] = 'nullable|string|min:8|confirmed';
            }
        }

        $mensajes = [
            'clave.required'       => 'La clave institucional es obligatoria.',
            'clave.max'            => 'La clave no puede superar los 20 caracteres.',
            'nombre.required'      => 'El nombre es obligatorio.',
            'nombre.max'           => 'El nombre no puede superar los 50 caracteres.',
            'ap_pat.required'      => 'El apellido paterno es obligatorio.',
            'ap_pat.max'           => 'El apellido paterno no puede superar los 50 caracteres.',
            'ap_mat.max'           => 'El apellido materno no puede superar los 50 caracteres.',
            'email.required'       => 'El correo electrónico es obligatorio.',
            'email.email'          => 'Ingresa un correo electrónico válido.',
            'email.max'            => 'El correo no puede superar los 100 caracteres.',
            'rol.required'         => 'Debes seleccionar un rol para el usuario.',
            'rol.in'               => 'El rol seleccionado no es válido.',
            'password.required'    => 'La contraseña es obligatoria en modo local.',
            'password.min'         => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed'   => 'La confirmación de la contraseña no coincide.',
        ];

        $request->validate($reglas, $mensajes);

        // Validación de clave numérica institucional
        if (!$esSuperadminRaiz) {
            $claveTrim = trim($request->clave);
            if (!ctype_digit($claveTrim)) {
                throw ValidationException::withMessages([
                    'clave' => 'La clave institucional debe ser numérica (solo dígitos).'
                ]);
            }
        }

        // Validación de correo: el Superadministrador principal puede usar cualquier dominio de correo válido
        $emailInput = strtolower(trim($request->email));
        $esMismoEmail = ($usuario !== null && strtolower(trim($usuario->email)) === $emailInput);

        if (!$esSuperadminRaiz && !$esMismoEmail && !self::esCorreoInstitucionalValido($emailInput)) {
            throw ValidationException::withMessages([
                'email' => 'El correo electrónico debe pertenecer estrictamente al dominio institucional (@uaslp.mx).'
            ]);
        }

        $this->validarUnicidad($request, $usuario ? $usuario->id : null, $esSuperadminRaiz);
    }

    /**
     * Valida que no existan duplicados para la clave o correo electrónico.
     */
    private function validarUnicidad(Request $request, ?int $ignorarId = null, bool $esSuperadminRaiz = false): void
    {
        $email = strtolower(trim($request->email));

        // Validar unicidad de clave solo si no es el Superadmin raíz
        if (!$esSuperadminRaiz) {
            $clave = trim($request->clave);

            $existenteClave = User::where('clave', $clave)
                ->when($ignorarId, fn ($q) => $q->where('id', '!=', $ignorarId))
                ->first();

            if ($existenteClave) {
                $msgClave = $existenteClave->activo == 0
                    ? "Ya existe un usuario desactivado con la clave '{$clave}'. Puedes reactivarlo desde la tabla."
                    : "Ya existe un usuario registrado con la clave '{$clave}'.";

                throw ValidationException::withMessages(['clave' => $msgClave]);
            }
        }

        // Validar unicidad de email
        $existenteEmail = User::where('email', $email)
            ->when($ignorarId, fn ($q) => $q->where('id', '!=', $ignorarId))
            ->first();

        if ($existenteEmail) {
            $msgEmail = $existenteEmail->activo == 0
                ? "Ya existe un usuario desactivado con el correo '{$email}'. Puedes reactivarlo desde la tabla."
                : "Ya existe un usuario registrado con el correo '{$email}'.";

            throw ValidationException::withMessages(['email' => $msgEmail]);
        }
    }

    /**
     * Valida dinámicamente que no se excedan los cupos de Superadministradores o Administradores activos.
     */
    private function validarLimitesCupo(string $nuevoRol, ?string $rolActual = null): void
    {
        if ($rolActual === $nuevoRol) {
            return;
        }

        $config = Configuracion::obtener();

        if ($nuevoRol === 'Superadministrador') {
            $count = User::where('rol', 'Superadministrador')->where('activo', 1)->count();
            if ($count >= $config->max_superadmins) {
                throw ValidationException::withMessages([
                    'rol' => "Límite alcanzado: Ya existen {$config->max_superadmins} Superadministradores activos. No es posible asignar más usuarios a este rol.",
                ]);
            }
        } elseif ($nuevoRol === 'Administrador') {
            $count = User::where('rol', 'Administrador')->where('activo', 1)->count();
            if ($count >= $config->max_admins) {
                throw ValidationException::withMessages([
                    'rol' => "Límite alcanzado: Ya existen {$config->max_admins} Administradores activos. No es posible asignar más usuarios a este rol.",
                ]);
            }
        }
    }

    /**
     * Verifica que no se degrade el rol del único Superadministrador activo del sistema.
     */
    private function validarProteccionSuperadmin(User $usuario, string $nuevoRol): void
    {
        if ($usuario->rol === 'Superadministrador' && $nuevoRol !== 'Superadministrador') {
            $otrosSuperadmins = User::where('rol', 'Superadministrador')
                ->where('activo', 1)
                ->where('id', '!=', $usuario->id)
                ->count();

            if ($otrosSuperadmins === 0) {
                throw ValidationException::withMessages([
                    'rol' => 'No es posible cambiar el rol del único Superadministrador del sistema. Debe existir al menos un Superadministrador activo.',
                ]);
            }
        }
    }
}
