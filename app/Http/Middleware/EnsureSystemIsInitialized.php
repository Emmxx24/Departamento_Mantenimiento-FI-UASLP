<?php

namespace App\Http\Middleware;

use App\Models\Configuracion;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSystemIsInitialized
{
    /**
     * Verifica que el sistema esté inicializado para acceder a las rutas protegidas.
     * Si no está inicializado:
     * - Al Superadministrador lo redirige a /panel-maestro para inicializar.
     * - A cualquier otro usuario o invitado lo redirige a /login.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $config = Configuracion::obtener();

        if (!$config || !$config->sistema_inicializado) {
            if (Auth::check() && Auth::user()->rol === 'Superadministrador') {
                return redirect()->route('panel.maestro')
                    ->with('warning', 'Debes configurar e inicializar el sistema antes de acceder a otros módulos.');
            }

            // Si hay un usuario autenticado que no sea Superadministrador,
            // cerramos su sesión e invalidamos el token para evitar bucles de redirección con el middleware 'guest'
            if (Auth::check()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return redirect()->route('login')
                ->with('error', 'El sistema no se encuentra inicializado, notifica al administrador.');
        }

        return $next($request);
    }
}
