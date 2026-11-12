<?php

namespace App\Http\Controllers;

use App\Models\Mensaje;
use App\Models\Ticket;
use App\Services\TicketMailService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TicketMensajeController extends Controller
{
    /**
     * Guarda un nuevo mensaje en el chat del ticket (texto y/o hasta 5 archivos).
     */
    public function store(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);
        $user = Auth::user();

        // 1. Autorización única centralizada (devuelve 404)
        abort_unless($ticket->puedeVer($user), 404);

        // 2. Chat de solo lectura si concluyó el ticket
        if ($ticket->chatBloqueado()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El ticket concluyó, este chat es de sólo lectura.'
                ], 403);
            }
            abort(404);
        }

        // 3. Validar presencia de texto o archivos
        $hasText = $request->filled('texto_mensaje') && trim($request->texto_mensaje) !== '';
        $hasFiles = ($request->hasFile('archivos') && count($request->file('archivos')) > 0)
            || $request->hasFile('archivo_adjunto');

        if (!$hasText && !$hasFiles) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debes escribir un mensaje o adjuntar al menos un archivo.'
                ], 422);
            }
            return back()->withErrors(['texto_mensaje' => 'Debes escribir un mensaje o adjuntar al menos un archivo.']);
        }

        // 4. Validaciones centralizadas desde ArchivoController
        $reglas = array_merge([
            'texto_mensaje' => 'nullable|string|max:1000',
            'archivo_adjunto' => 'nullable|file|mimes:' . ArchivoController::EXTENSIONES_PERMITIDAS . '|max:' . ArchivoController::MAX_KB_POR_ARCHIVO,
        ], ArchivoController::reglas('archivos'));

        $mensajes = array_merge([
            'archivo_adjunto.mimes' => 'Tipo de archivo no permitido. Solo se aceptan: JPG, PNG, WEBP, PDF, Word, Excel o TXT.',
            'archivo_adjunto.max' => 'El archivo no puede superar los 10 MB.',
        ], ArchivoController::mensajes('archivos'));

        $request->validate($reglas, $mensajes);

        // Recolectar archivos válidos del request
        $archivosAProcesar = [];
        if ($request->hasFile('archivos')) {
            foreach ($request->file('archivos') as $file) {
                if ($file->isValid()) {
                    $archivosAProcesar[] = $file;
                }
            }
        }
        if ($request->hasFile('archivo_adjunto') && $request->file('archivo_adjunto')->isValid()) {
            $archivosAProcesar[] = $request->file('archivo_adjunto');
        }

        // 5. Transacción atómica en el store
        DB::beginTransaction();

        try {
            $mensaje = Mensaje::create([
                'texto' => $hasText ? trim($request->texto_mensaje) : '',
                'fecha_men' => now(),
                'remitente' => $user->id,
                'id_ticket' => $ticket->id_ticket,
            ]);

            $archivosGuardados = ArchivoController::guardarArchivos(
                $archivosAProcesar,
                idMensaje: $mensaje->id_mensaje,
                carpeta: 'mensajes'
            );

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al enviar mensaje en chat: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ocurrió un error al enviar el mensaje.',
                ], 500);
            }

            return redirect()->back()->with('error', 'Ocurrió un error al enviar el mensaje.');
        }

        // 6. Lógica de validación para envío de correo
        $ultimoMensajePrevio = Mensaje::where('id_ticket', $ticket->id_ticket)
            ->where('id_mensaje', '!=', $mensaje->id_mensaje) // Excluir el que acabamos de guardar
            ->orderBy('fecha_men', 'desc')
            ->first();
        $enviarCorreo = false;

        if (!$ultimoMensajePrevio) {
            // Es el primer mensaje en el chat, se envía el correo
            $enviarCorreo = true;
        } elseif ($ultimoMensajePrevio->remitente != $user->id) {
            // 1.- Si el mensaje anterior no es del mismo usuario, se manda el correo
            $enviarCorreo = true;
        } else {
            // 2.- Si el mensaje viene del mismo usuario, se verifica la diferencia de tiempo
            $fechaAnterior = \Carbon\Carbon::parse($ultimoMensajePrevio->fecha_men);
            
            // Si pasaron 10 minutos o más de diferencia uno del otro, se envía el correo
            if ($fechaAnterior->diffInMinutes(now()) >= 10) {
                $enviarCorreo = true;
            }
        }
        $str = $enviarCorreo ? 'true' : 'false';
        // Notificación por correo
        if ($enviarCorreo) {
            TicketMailService::enviarNuevoMensaje($ticket, $mensaje);
        }

        if ($request->ajax() || $request->wantsJson()) {
            $fechaStr = Carbon::parse($mensaje->fecha_men)->format('d/m/Y H:i');

            $htmlMensaje = view('components.mensaje', [
                'esMio' => true,
                'texto' => $mensaje->texto,
                'archivos' => $archivosGuardados,
                'remitenteNombre' => trim($user->nombre . ' ' . $user->ap_pat),
                'remitenteRol' => $user->rol,
                'fecha' => $fechaStr,
            ])->render();

            $bovedaHTML = '';
            foreach ($archivosGuardados as $arch) {
                $bovedaHTML .= view('components.item-evidencia', [
                    'url' => route('archivo.descargar', $arch->id_archivo),
                    'nombre' => $arch->nombre_archivo,
                    'origen' => 'Tú',
                    'fecha' => $fechaStr,
                    'id_archivo' => $arch->id_archivo,
                ])->render();
            }

            return response()->json([
                'success' => true,
                'html' => $htmlMensaje,
                'bovedaHTML' => $bovedaHTML,
                'mensaje' => [
                    'id_mensaje' => $mensaje->id_mensaje,
                    'texto' => $mensaje->texto,
                    'remitente' => $mensaje->remitente,
                    'fecha' => $fechaStr,
                ],
            ]);
        }

        return redirect()->back();
    }

    /**
     * Smart Polling: Consulta mensajes nuevos posteriores al último ID conocido.
     */
    public function obtenerMensajesNuevos(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);
        $user = Auth::user();

        // Autorización única (devuelve 404)
        abort_unless($ticket->puedeVer($user), 404);

        $ultimoId = (int) $request->query('ultimo_id', 0);
        $isLocked = $ticket->chatBloqueado();

        $mensajesNuevos = Mensaje::where('id_ticket', $ticket->id_ticket)
            ->where('id_mensaje', '>', $ultimoId)
            ->where('remitente', '!=', $user->id)
            ->with(['archivos', 'usuario'])
            ->orderBy('id_mensaje', 'asc')
            ->get();

        if ($mensajesNuevos->isEmpty()) {
            return response()->json([
                'nuevos' => false,
                'isLocked' => $isLocked,
                'ultimo_id' => $ultimoId,
            ]);
        }

        $html = '';
        $bovedaHTML = '';
        $maxId = $ultimoId;

        foreach ($mensajesNuevos as $msg) {
            if ($msg->id_mensaje > $maxId) {
                $maxId = $msg->id_mensaje;
            }

            $nombreRemitente = $msg->usuario ? trim($msg->usuario->nombre . ' ' . $msg->usuario->ap_pat) : 'Usuario';
            $fechaMsg = Carbon::parse($msg->fecha_men)->format('d/m/Y H:i');

            $html .= view('components.mensaje', [
                'esMio' => false,
                'texto' => $msg->texto,
                'archivos' => $msg->archivos,
                'remitenteNombre' => $nombreRemitente,
                'remitenteRol' => $msg->usuario?->rol,
                'fecha' => $fechaMsg,
            ])->render();

            foreach ($msg->archivos as $archivo) {
                $bovedaHTML .= view('components.item-evidencia', [
                    'url' => route('archivo.descargar', $archivo->id_archivo),
                    'nombre' => $archivo->nombre_archivo,
                    'origen' => 'De ' . $nombreRemitente,
                    'fecha' => $fechaMsg,
                    'id_archivo' => $archivo->id_archivo,
                ])->render();
            }
        }

        return response()->json([
            'nuevos' => true,
            'html' => $html,
            'bovedaHTML' => $bovedaHTML,
            'ultimo_id' => $maxId,
            'isLocked' => $isLocked,
        ]);
    }
}
