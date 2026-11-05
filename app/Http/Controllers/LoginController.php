<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Muestra la vista de inicio de sesión.
     */
    public function mostrarLogin()
    {
        $config = Configuracion::obtener();
        return view('paginas.login', compact('config'));
    }

    /**
     * Procesa la autenticación del usuario según el modo configurado.
     */
    public function login(Request $request)
    {
        $credenciales = $this->validarPeticion($request);
        $config = Configuracion::obtener();

        // Si el sistema no está inicializado, control de puerta directo
        if (!$config || !$config->sistema_inicializado) {
            return $this->procesarLoginNoInicializado($request, $credenciales);
        }

        if ($config->isInstitucional()) {
            return $this->procesarLoginInstitucional($request, $credenciales, $config);
        }

        return $this->procesarLoginLocal($request, $credenciales);
    }

    /**
     * Procesa el inicio de sesión cuando el sistema aún no ha sido inicializado.
     * Rechaza de inmediato a cualquier cuenta que no sea Superadministrador sin crear sesión,
     * y redirige al Superadministrador directamente a /panel-maestro.
     */
    protected function procesarLoginNoInicializado(Request $request, array $credenciales)
    {
        $user = $this->buscarUsuarioPorClave($credenciales['usuario']);

        if (!$this->credencialesSonValidas($user, $credenciales['password'])) {
            return back()
                ->withInput($request->only('usuario'))
                ->with('error', 'Credenciales incorrectas.');
        }

        if ($user->rol !== 'Superadministrador') {
            return back()
                ->withInput($request->only('usuario'))
                ->with('error', 'El sistema no se encuentra inicializado, notifica al administrador.');
        }

        if ($errorActivo = $this->verificarUsuarioActivo($user)) {
            return back()
                ->withInput($request->only('usuario'))
                ->with('error', $errorActivo);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('panel.maestro');
    }

    /**
     * Procesa la autenticación en Modo Local.
     */
    protected function procesarLoginLocal(Request $request, array $credenciales)
    {
        $user = $this->buscarUsuarioPorClave($credenciales['usuario']);

        if (!$this->credencialesSonValidas($user, $credenciales['password'])) {
            return back()
                ->withInput($request->only('usuario'))
                ->with('error', 'Credenciales incorrectas.');
        }

        if ($errorActivo = $this->verificarUsuarioActivo($user)) {
            return back()
                ->withInput($request->only('usuario'))
                ->with('error', $errorActivo);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    /**
     * Procesa la autenticación en Modo Institucional (UASLP).
     * Por el momento, el servicio se encuentra en integración con la universidad.
     * Solo el Superadministrador maestro puede ingresar con sus credenciales al Panel Maestro.
     */
    protected function procesarLoginInstitucional(Request $request, array $credenciales, Configuracion $config)
    {
        $user = $this->buscarUsuarioPorClave($credenciales['usuario']);

        // Llave maestra: Solo el Superadministrador base puede acceder para configurar el Panel Maestro
        if ($user && $config->isSuperadminRaiz($user->clave) && $this->credencialesSonValidas($user, $credenciales['password'])) {
            if ($errorActivo = $this->verificarUsuarioActivo($user)) {
                return back()
                    ->withInput($request->only('usuario'))
                    ->with('error', $errorActivo);
            }

            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->route('panel.maestro');
        }

        // Aquí irá la conexión con la API institucional UASLP cuando esté disponible:

        // Para cualquier otro usuario, se indica que el acceso institucional está en proceso de integración
        return back()
            ->withInput($request->only('usuario'))
            ->with('warning', 'El inicio de sesión institucional UASLP se encuentra en proceso de integración técnica con la universidad. Por el momento el acceso para usuarios regulares está deshabilitado.');
    }

    /**
     * Valida los datos recibidos del formulario de login.
     */
    protected function validarPeticion(Request $request): array
    {
        return $request->validate([
            'usuario'  => 'required|string',
            'password' => 'required|string',
        ], [
            'usuario.required'  => 'La clave de usuario es obligatoria.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);
    }

    /**
     * Busca al usuario únicamente por su clave única o RPE.
     */
    protected function buscarUsuarioPorClave(string $clave): ?User
    {
        return User::where('clave', trim($clave))->first();
    }

    /**
     * Comprueba si el usuario existe y su contraseña coincide con el hash almacenado.
     */
    protected function credencialesSonValidas(?User $user, string $password): bool
    {
        return $user !== null && !empty($user->password) && password_verify($password, $user->password);
    }

    /**
     * Comprueba si el usuario se encuentra activo (activo == 1).
     * Retorna el mensaje de error si está dado de baja, o null si está activo.
     */
    protected function verificarUsuarioActivo(User $user): ?string
    {
        if (!$user->activo) {
            $config = Configuracion::obtener();
            $contacto = ($config && $config->correo_contacto)
                ? ' al correo ' . $config->correo_contacto
                : ' a un superadministrador.';

            return 'Tu cuenta se encuentra dada de baja en este sistema. Contacta' . $contacto;
        }

        return null;
    }

    /**
     * Cierra la sesión activa de forma segura.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}