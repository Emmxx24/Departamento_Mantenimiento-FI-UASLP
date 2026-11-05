<?php

namespace App\Http\Middleware;

use App\Models\Configuracion;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureModoLocal
{
    /**
     * Verifica que el sistema esté inicializado y que el modo de login sea estrictamente Local (0).
     * Si login_institucional == 1:
     * - En rutas de invitados (como recuperación de contraseña) responde con 404.
     * - Si el usuario autenticado es Superadministrador, lo redirige a /panel-maestro.
     * - Si es cualquier otro rol con sesión abierta, cierra su sesión y lo envía al login.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $config = Configuracion::obtener();

        if (!$config || !$config->permiteGestionPassword()) {
            if (!Auth::check()) {
                abort(404);
            }

            if (Auth::user()->rol === 'Superadministrador') {
                return redirect()->route('panel.maestro')
                    ->with('warning', 'El modo de autenticación institucional UASLP se encuentra en proceso de integración. Para operar los módulos del sistema, activa la Autenticación Local.');
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('warning', 'El inicio de sesión institucional UASLP se encuentra en proceso de integración técnica con la universidad. Por el momento el acceso para usuarios regulares está deshabilitado.');
        }

        return $next($request);
    }
}
