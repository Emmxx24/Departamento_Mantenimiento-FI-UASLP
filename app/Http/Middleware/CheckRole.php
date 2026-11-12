<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Verifica que el usuario autenticado tenga el rol requerido.
     * Uso en rutas: ->middleware('role:Administrador')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! Auth::check()) {
            return redirect()->guest(route('login'))
                ->with('error', 'Por favor, inicia sesión.');
        }

        if (! Auth::user()->activo) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Tu cuenta ha sido desactivada. Comunícate con el administrador.');
        }

        $userRol = Auth::user()->rol;

        if (! in_array($userRol, $roles)) {
            abort(404);
        }

        return $next($request);
    }
}
