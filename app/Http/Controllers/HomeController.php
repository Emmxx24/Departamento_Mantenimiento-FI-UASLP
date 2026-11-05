<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->rol !== 'Solicitante') {
            // Últimos tickets creados
            $tickets = Ticket::with(['servicio', 'adminAtiende', 'edificio', 'lugar'])
                ->where('es_borrador', 0)
                ->visiblesPara($user)
                ->whereNotIn('estado', ['Cerrado', 'Terminado'])
                ->whereNotNull('id_admin_atiende')
                ->latest('fecha_creacion')
                ->take(3)
                ->get();

            $ticketsAntiguos = Ticket::with(['servicio', 'adminAtiende', 'edificio', 'lugar'])
                ->where('es_borrador', 0)
                ->visiblesPara($user)
                ->whereNotIn('estado', ['Cerrado', 'Terminado'])
                ->whereNotNull('id_admin_atiende')
                ->oldest('fecha_creacion') // Los más antiguos activos primero
                ->take(3)
                ->get();

            $ticketsImportantes = Ticket::with(['servicio', 'adminAtiende', 'edificio', 'lugar'])
                ->where('es_borrador', 0)
                ->visiblesPara($user)
                ->whereNotIn('estado', ['Cerrado', 'Terminado'])
                ->where('prioridad', 'Alta')
                ->whereNotNull('id_admin_atiende')
                ->latest('fecha_creacion')
                ->take(3)
                ->get();
            if ($user->rol == 'Superadministrador') {
                $ticketsSinAdmin = Ticket::with(['servicio', 'adminAtiende', 'edificio', 'lugar'])
                    ->where('es_borrador', 0)
                    ->visiblesPara($user)
                    ->whereNotIn('estado', ['Cerrado', 'Terminado'])
                    ->whereNull('id_admin_atiende')
                    ->latest('fecha_creacion')
                    ->take(3)
                    ->get();

                return view('paginas.home', compact('tickets', 'ticketsAntiguos', 'ticketsImportantes', 'ticketsSinAdmin'));
            }

            return view('paginas.home', compact('tickets', 'ticketsAntiguos', 'ticketsImportantes'));

        } elseif ($user->rol == 'Solicitante') {

            // Tickets pendientes del solicitante
            $ticketsPendientes = Ticket::with(['servicio', 'adminAtiende', 'edificio', 'lugar'])
                ->where('es_borrador', 0)
                ->where('id_solicitante', $user->id)
                ->whereNotIn('estado', ['Cerrado', 'Terminado'])
                ->latest('fecha_creacion')
                ->take(3)
                ->get();

            // Tickets terminados del solicitante
            $ticketsResueltos = Ticket::with(['servicio', 'adminAtiende', 'edificio', 'lugar'])
                ->where('es_borrador', 0)
                ->where('id_solicitante', $user->id)
                ->where('estado', 'Terminado')
                ->latest('fecha_creacion')
                ->take(3)
                ->get();

            return view('paginas.home', compact('ticketsPendientes', 'ticketsResueltos'));

        } else {
            // Prevenir acceso si el rol está corrupto o es inválido
            abort(404);
        }
    }
}
