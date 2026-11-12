<?php

use App\Http\Middleware\CheckRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Http\Middleware\EnsureSystemIsInitialized;
use App\Http\Middleware\EnsureModoLocal;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => CheckRole::class,
            'sistema.inicializado' => EnsureSystemIsInitialized::class,
            'modo.local' => EnsureModoLocal::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            return redirect()->route('login')
                ->with('error', 'Tu sesión expiró. Por favor, inicia sesión de nuevo.');
        });

        // Opcional: capturar también si se lanza como HttpException con código 419
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() === 419) {
                return redirect()->route('login')
                    ->with('error', 'Tu sesión expiró. Por favor, inicia sesión de nuevo.');
            }
        });
    })->create();
