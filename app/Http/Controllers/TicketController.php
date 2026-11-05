<?php

namespace App\Http\Controllers;

use App\Models\Archivo;
use App\Models\Aula;
use App\Models\Edificio;
use App\Models\Lugar;
use App\Models\Mensaje;
use App\Models\Piso;
use App\Models\Servicio;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketMailService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class TicketController extends Controller
{
    public function create(Request $request)
    {
        // Solo edificios que tengan al menos un piso activo con aulas activas
        $edificios = Edificio::where('activo', 1)
            ->whereHas('pisos', function ($q) {
                $q->where('activo', 1)->whereHas('aulas', fn ($qa) => $qa->where('activo', 1));
            })
            ->with([
                'pisos' => fn ($q) => $q->where('activo', 1)
                    ->whereHas('aulas', fn ($qa) => $qa->where('activo', 1))
                    ->orderBy('piso', 'asc'),
                'pisos.aulas' => fn ($q) => $q->where('activo', 1),
            ])->get();
        $servicios = Servicio::where('activo', 1)->get();

        $borrador = null;
        if ($request->has('draft')) {
            $draftId = $request->query('draft');
            $borrador = Ticket::where('id_ticket', $draftId)
                ->where('id_solicitante', Auth::id())
                ->where('es_borrador', 1)
                ->first();
        }

        // Enviamos las variables a la vista 'createTicket'
        return view('paginas.crearTicket', compact('edificios', 'servicios', 'borrador'));
    }

    public function index(Request $request)
    {
        $usuario = Auth::user();

        // Evitar N+1 y filtrar borradores
        $tickets = Ticket::with(['servicio', 'adminAtiende', 'edificio', 'lugar'])->where('es_borrador', 0);

        // --- FILTRO DE CONTEXTO Y ROL ---
        $contexto = $request->input('contexto');

        if ($usuario->rol === 'Superadministrador') {
            if ($contexto === 'mis_solicitudes') {
                $tickets->where('id_solicitante', $usuario->id);
            } elseif ($contexto === 'mis_asignados') {
                $tickets->where('id_admin_atiende', $usuario->id);
            } elseif ($contexto === 'pendientes_asignar') {
                $tickets->whereNull('id_admin_atiende');
            }
            // 'todos' o vacío, no aplica filtro extra para superadmin.
        } elseif ($usuario->rol === 'Administrador') {
            if ($contexto === 'mis_solicitudes') {
                $tickets->where('id_solicitante', $usuario->id);
            } elseif ($contexto === 'todos') {
                $tickets->where(function ($query) use ($usuario) {
                    $query->where('id_admin_atiende', $usuario->id)
                        ->orWhere('id_solicitante', $usuario->id);
                });
            } else {
                // Por defecto o 'mis_asignados', solo sus asignados
                $tickets->where('id_admin_atiende', $usuario->id);
            }
        } elseif ($usuario->rol === 'Solicitante') {
            // El solicitante siempre ve solo los suyos, sin importar el contexto
            $tickets->where('id_solicitante', $usuario->id);
        }

        // Búsqueda global por texto (q)
        $tickets->when($request->filled('q'), function ($query) use ($request) {
            $q = trim($request->q);
            $query->where(function ($subquery) use ($q) {
                $subquery->where('asunto', 'LIKE', "%{$q}%")
                    ->orWhere('descripcion', 'LIKE', "%{$q}%")
                    ->orWhere('estado', 'LIKE', "%{$q}%")
                    ->orWhere('prioridad', 'LIKE', "%{$q}%")
                    ->orWhereHas('servicio', fn ($rel) => $rel->where('nombre', 'LIKE', "%{$q}%"))
                    ->orWhereHas('edificio', fn ($rel) => $rel->where('nombre', 'LIKE', "%{$q}%"))
                    ->orWhereHas('lugar', fn ($rel) => $rel->where('nombre', 'LIKE', "%{$q}%"))
                    ->orWhereHas('adminAtiende', function ($rel) use ($q) {
                        $rel->where('nombre', 'LIKE', "%{$q}%")
                            ->orWhere('ap_pat', 'LIKE', "%{$q}%")
                            ->orWhere('ap_mat', 'LIKE', "%{$q}%")
                            ->orWhere('clave', 'LIKE', "%{$q}%")
                            ->orWhere(DB::raw("CONCAT(nombre, ' ', ap_pat)"), 'LIKE', "%{$q}%")
                            ->orWhere(DB::raw("CONCAT(nombre, ' ', ap_pat, ' ', COALESCE(ap_mat, ''))"), 'LIKE', "%{$q}%");
                    });

                if (is_numeric($q)) {
                    $subquery->orWhere('id_ticket', $q);
                }
            });
        });

        // FILTROS ESPECÍFICOS
        $tickets->when($request->filled('id_ticket'), fn ($q) => $q->where('id_ticket', $request->id_ticket))
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->estado))
            ->when($request->filled('fecha'), fn ($q) => $q->whereDate('fecha_creacion', $request->fecha))
            ->when($request->filled('edificio'), fn ($q) => $q->where('id_edificio', $request->edificio))
            ->when($request->filled('servicio'), fn ($q) => $q->where('id_servicio', $request->servicio))
            ->when($request->filled('prioridad'), fn ($q) => $q->where('prioridad', $request->prioridad))
            ->when($request->filled('lugar'), fn ($q) => $q->whereHas('lugar', fn ($q2) => $q2->where('id_lugar', $request->lugar)));

        // Ocultar inactivos por defecto (aplicable solo a tickets cerrados/terminados)
        if (! $request->filled('mostrar_inactivos')) {
            $tickets->where(function ($query) {
                $query->whereNotIn('estado', ['Cerrado', 'Terminado'])
                    ->orWhere(function ($sub) {
                        $sub->whereHas('edificio', fn ($q) => $q->where('activo', 1))
                            ->whereHas('servicio', fn ($q) => $q->where('activo', 1))
                            ->whereHas('lugar', fn ($q) => $q->where('activo', 1)->whereHas('piso', fn ($qp) => $qp->where('activo', 1)));
                    });
            });
        }

        // FILTRO DE ORDENAMIENTO
        $orden = $request->input('orden', 'mas_recientes');
        if ($orden === 'mas_antiguos') {
            $tickets->orderBy('fecha_creacion', 'asc');
        } else {
            $tickets->orderBy('fecha_creacion', 'desc');
        }

        // Paginación (con query string para mantener parámetros de URL)
        $tickets = $tickets->paginate(6)->withQueryString();

        return view('paginas.tickets', compact('tickets'));
    }

    public function guardarBorrador(Request $request)
    {
        $id_ticket = $request->input('id_ticket');
        if ($id_ticket) {
            $ticket = Ticket::where('id_ticket', $id_ticket)
                ->where('id_solicitante', Auth::id())
                ->where('es_borrador', 1)
                ->first();

            if ($ticket) {
                $ticket->update([
                    'descripcion' => $request->descripcion,
                    'asunto' => $request->asunto,
                    'id_edificio' => $request->id_edificio,
                    'id_servicio' => $request->id_servicio,
                    'id_aula' => $request->id_aula,
                    'piso' => $request->piso,
                ]);

                return response()->json(['success' => true, 'id_ticket' => $ticket->id_ticket]);
            }
        }

        // Si no hay id_ticket o no se encontró, creamos uno nuevo
        $ticket = Ticket::create([
            'fecha_creacion' => now(),
            'estado' => 'Pendiente',
            'descripcion' => $request->descripcion,
            'asunto' => $request->asunto,
            'es_borrador' => 1, // Se marca como borrador
            'id_solicitante' => Auth::id(),
            'id_edificio' => $request->id_edificio,
            'id_servicio' => $request->id_servicio,
            'id_aula' => $request->id_aula,
            'piso' => $request->piso,
        ]);

        return response()->json(['success' => true, 'id_ticket' => $ticket->id_ticket]);
    }

    public function store(Request $request)
    {
        $id_admin_atiende = null;
        $ticket = $this->ticketValidaciones($request);

        // 6. Prevención de tickets duplicados (últimos 3 días, no cerrados, mismo edificio, aula y servicio)
        if ($respuestaDuplicado = $this->verificarTicketDuplicado($request, $ticket)) {
            return $respuestaDuplicado;
        }

        if (Auth::user()->rol == 'Superadministrador' || Auth::user()->rol == 'Administrador') {
            $id_admin_atiende = Auth::id();
        }

        DB::beginTransaction();

        try {
            if ($ticket) {
                // Actualizamos el borrador a finalizado
                $dataUpdate = [
                    'fecha_creacion' => now(),
                    'estado' => 'Pendiente',
                    'descripcion' => $request->descripcion,
                    'asunto' => $request->asunto,
                    'prioridad' => 'Media',
                    'es_borrador' => 0,
                    'id_edificio' => $request->id_edificio,
                    'id_servicio' => $request->id_servicio,
                    'id_aula' => $request->id_aula,
                    'piso' => $request->piso,
                ];

                if ($id_admin_atiende) {
                    $dataUpdate['id_admin_atiende'] = $id_admin_atiende;
                }

                $ticket->update($dataUpdate);
            } else {
                // Si por algún motivo no pasó por ser borrador, se crea normal
                $ticket = Ticket::create([
                    'fecha_creacion' => now(),
                    'estado' => 'Pendiente',
                    'descripcion' => $request->descripcion,
                    'asunto' => $request->asunto,
                    'prioridad' => 'Media',
                    'es_borrador' => 0,
                    'id_solicitante' => Auth::id(),
                    'id_admin_atiende' => $id_admin_atiende,
                    'id_edificio' => $request->id_edificio,
                    'id_servicio' => $request->id_servicio,
                    'id_aula' => $request->id_aula,
                    'piso' => $request->piso,
                ]);
            }

            if ($request->hasFile('archivo_adjunto')) {
                ArchivoController::guardarArchivos(
                    $request->file('archivo_adjunto'),
                    idTicket: $ticket->id_ticket,
                    carpeta: 'tickets'
                );
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar ticket: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ocurrió un error al registrar el ticket.',
                ], 500);
            }

            return redirect()->back()->withInput()->with('error', 'Ocurrió un error al registrar el ticket.');
        }

        $ticket->load(['servicio', 'edificio', 'lugar']);
        TicketMailService::enviarTicketCreado($ticket);

        $textoPiso = null;
        if (!is_null($ticket->piso)) {
            $textoPiso = ((int) $ticket->piso === 0) ? 'Planta baja' : 'Piso ' . $ticket->piso;
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'ticket' => [
                    'id' => $ticket->id_ticket,
                    'asunto' => $ticket->asunto,
                    'fecha' => Carbon::parse($ticket->fecha_creacion)->format('d/m/Y'),
                    'servicio' => $ticket->servicio?->nombre,
                    'edificio' => $ticket->edificio?->nombre,
                    'lugar'    => $ticket->lugar?->nombre,
                    'piso' => $textoPiso,
                    'estado' => $ticket->estado,
                ],
                'redirect' => route('tickets.show', $ticket->id_ticket),
            ]);
        }

        return redirect()->route('tickets.show', $ticket->id_ticket)->with('success', 'Ticket creado correctamente.');
    }



    private function ticketValidaciones($request)
    {
        $reglas = array_merge([
            'id_edificio' => 'required',
            'id_aula' => 'required',
            'piso' => 'required',
            'id_servicio' => 'required',
            'asunto' => 'required|max:100',
        ], ArchivoController::reglas('archivo_adjunto'));

        $mensajes = array_merge([
            'id_edificio.required' => 'Debes seleccionar un edificio.',
            'id_aula.required' => 'Debes seleccionar un aula.',
            'piso.required' => 'Debes indicar el piso.',
            'id_servicio.required' => 'Debes seleccionar el servicio requerido.',
            'asunto.required' => 'El asunto del ticket es obligatorio.',
            'asunto.max' => 'El asunto no puede superar los 100 caracteres.',
        ], ArchivoController::mensajes('archivo_adjunto'));

        $request->validate($reglas, $mensajes);

        $id_ticket = $request->input('id_ticket');
        $ticket = null;

        if ($id_ticket) {
            $ticket = Ticket::where('id_ticket', $id_ticket)
                ->where('id_solicitante', Auth::id())
                ->first();
        }

        // Si no se envió id_ticket, vincular con borrador pendiente reciente del usuario para evitar registros huérfanos/duplicados
        if (! $ticket) {
            $ticket = Ticket::where('id_solicitante', Auth::id())
                ->where('es_borrador', 1)
                ->where('id_edificio', $request->id_edificio)
                ->where('id_aula', $request->id_aula)
                ->where('id_servicio', $request->id_servicio)
                ->latest('id_ticket')
                ->first();
        }

        // Si es un ticket existente y no cambió el aula o servicio, se permite aunque ahora estén inactivos
        $aulaCambiada = ! $ticket || ($ticket->id_aula != $request->id_aula);
        $servicioCambiado = ! $ticket || ($ticket->id_servicio != $request->id_servicio);

        // 1. Validar edificio
        $edificio = Edificio::where('id_edificio', $request->id_edificio)
            ->when($aulaCambiada, fn ($q) => $q->where('activo', 1))
            ->first();
        if (! $edificio) {
            throw ValidationException::withMessages([
                'id_edificio' => 'El edificio seleccionado no es válido o se encuentra inactivo.',
            ]);
        }

        // 2. Validar piso correspondiente al edificio
        $piso = Piso::where('id_edificio', $edificio->id_edificio)
            ->where('piso', $request->piso)
            ->when($aulaCambiada, fn ($q) => $q->where('activo', 1))
            ->first();
        if (! $piso) {
            throw ValidationException::withMessages([
                'piso' => 'El piso seleccionado no es válido o se encuentra en mantenimiento.',
            ]);
        }

        // 3. Validar aula correspondiente al piso y edificio
        $aula = Aula::where('id_aula', $request->id_aula)
            ->where('id_piso', $piso->id_piso)
            ->when($aulaCambiada, fn ($q) => $q->where('activo', 1))
            ->first();
        if (! $aula) {
            throw ValidationException::withMessages([
                'id_aula' => 'El aula seleccionada no pertenece al piso indicado o se encuentra clausurada/desactivada.',
            ]);
        }

        // 4. Validar servicio
        $servicio = Servicio::where('id_servicio', $request->id_servicio)
            ->when($servicioCambiado, fn ($q) => $q->where('activo', 1))
            ->first();
        if (! $servicio) {
            throw ValidationException::withMessages([
                'id_servicio' => 'El servicio seleccionado no es válido o se encuentra inactivo.',
            ]);
        }

        return $ticket;
    }

    /**
     * Busca si existe un ticket duplicado en los últimos 3 días para el mismo edificio, aula y servicio.
     */
    private function buscarTicketDuplicado(Request $request)
    {
        $fechaLimite = Carbon::now()->subDays(3)->startOfDay();
        $idTicketActual = $request->input('id_ticket');

        return Ticket::where('es_borrador', 0)
            ->where('estado', '!=', 'Cerrado')
            ->where('id_edificio', $request->id_edificio)
            ->where('id_aula', $request->id_aula)
            ->where('id_servicio', $request->id_servicio)
            ->where('fecha_creacion', '>=', $fechaLimite)
            ->when($idTicketActual, function ($q) use ($idTicketActual) {
                $q->where('id_ticket', '!=', $idTicketActual);
            })
            ->first();
    }

    /**
     * Verifica si existe un ticket duplicado y genera la respuesta adecuada (JSON para AJAX o redirección).
     * Si existía un borrador generado para este intento, lo elimina.
     */
    private function verificarTicketDuplicado(Request $request, $ticket = null)
    {
        $ticketDuplicado = $this->buscarTicketDuplicado($request);

        if ($ticketDuplicado) {
            // Si existía un borrador generado para este intento, se elimina para no dejarlo pendiente
            if ($ticket && $ticket->es_borrador) {
                $ticket->delete();
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'duplicado' => true,
                    'ticket_existente' => $ticketDuplicado->id_ticket,
                    'message' => 'Ya existe un reporte registrado recientemente para este servicio en esta ubicación. Nuestro personal ya se encuentra atendiendo la situación.',
                    'redirect' => route('home'),
                ]);
            }

            return redirect()->route('home')->with('info', 'Ya existe un reporte registrado recientemente para este servicio en esta ubicación. Nuestro personal ya se encuentra atendiendo la situación.');
        }

        return null;
    }

    /**
     * Vista unificada de detalle de ticket.
     * Accesible por admin y solicitante. El controlador verifica permisos internamente.
     */
    public function show($id)
    {
        $user = Auth::user();

        $ticket = Ticket::with([
            'mensajes.archivos',
            'mensajes.usuario',
            'archivos',
            'solicitante',
            'adminAtiende',
            'servicio',
            'edificio',
            'lugar',
            'requisiciones',
        ])->where('es_borrador', 0)->findOrFail($id);

        abort_unless($ticket->puedeVer($user), 404);
        // Cargamos todos (incluyendo inactivos) para que no falle la vista si el ticket usa uno inactivo.
        // El filtrado visual se hace en frontend/blade.
        $edificios = Edificio::with([
            'pisos' => fn ($q) => $q->orderBy('piso', 'asc'),
            'pisos.aulas',
        ])->get();
        // De igual forma para servicios
        $servicios = Servicio::all();
        // Para ver si tiene una requisicion terminada(tal vez solo deberia permitir una requisicion por ticket, pero por si acaso)
        $reqFinal = $ticket->requisiciones->where('es_borrador', 0)->first();
        // Para ver si tiene una requisicion en borrador
        $reqBorrador = $ticket->requisiciones->where('es_borrador', 1)->first();

        $datos = [
            'ticket' => $ticket,
            'edificios' => $edificios,
            'servicios' => $servicios,
            'reqFinal' => $reqFinal,
            'reqBorrador' => $reqBorrador,
        ];

        if ($user->rol === 'Superadministrador') {
            $datos['administradores'] = User::whereIn('rol', ['Administrador', 'Superadministrador'])
                ->where('activo', 1)
                ->orderBy('nombre')
                ->get();
        }

        return view('paginas.detalle-ticket', $datos);
    }



    /**
     * Mostrar todos los borradores del usuario autenticado
     */
    public function borradores()
    {
        $usuario = Auth::user();

        // Obtener solo los borradores del usuario actual con sus relaciones
        $borradoresTickets = Ticket::with(['servicio', 'edificio', 'lugar'])
            ->where('id_solicitante', $usuario->id)
            ->where('es_borrador', 1)
            ->orderBy('fecha_creacion', 'desc')
            ->get();

        return view('paginas.borradores', compact('borradoresTickets'));
    }

    /**
     * Eliminar un borrador
     */
    public function eliminarBorrador($id)
    {
        DB::BeginTransaction();
        try {
            $borrador = Ticket::where('id_ticket', $id)
                ->where('id_solicitante', Auth::id())
                ->where('es_borrador', 1)
                ->first();

            // aquí había un if(borrador), pero al estar ahora en un
            // try catch, si el borrador es NULL y lo intenta eliminar
            // va a lanzar un error y el codigo va a seguir dentro del catch.
            $borrador->delete();

            DB::Commit();

            return redirect()->route('tickets.borradores')
                ->with('success', 'Borrador eliminado correctamente');

        } catch (\Exception $e) {
            DB::Rollback();
            Log::error('Error al eliminar registro: '.$e->getMessage());

            return redirect()->route('tickets.borradores')
                ->with('error', 'Borrador no encontrado');
        }

    }

    /**
     * Cancela (cierra) un ticket a solicitud de su creador (Solicitante).
     */
    public function cancelar($id)
    {
        $ticket = Ticket::where('es_borrador', 0)->findOrFail($id);

        if ($ticket->id_solicitante !== Auth::id()) {
            abort(403, 'No tienes autorización para cancelar este ticket.');
        }

        if (in_array($ticket->estado, ['Cerrado', 'Terminado'])) {
            return redirect()->route('tickets.show', $id)
                ->with('error', 'El ticket ya se encuentra ' . strtolower($ticket->estado) . ' y no puede ser cancelado.');
        }

        DB::beginTransaction();
        try {
            $ticket->estado = 'Cerrado';
            $ticket->fecha_cierre = now();
            $ticket->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al cancelar ticket: ' . $e->getMessage(), ['ticket_id' => $id]);

            return redirect()->route('tickets.show', $id)
                ->with('error', 'Ocurrió un error al intentar cancelar el ticket.');
        }

        // Notificar por correo al personal correspondiente y al solicitante
        TicketMailService::enviarTicketCancelado($ticket);

        return redirect()->route('tickets.show', $id)
            ->with('success', 'El ticket ha sido cancelado exitosamente.');
    }
}
