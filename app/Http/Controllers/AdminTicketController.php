<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketMailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminTicketController extends Controller
{

    /**
     * Actualiza un único campo del ticket desde la vista de detalle (admin).
     * Recibe { campo, valor } por PUT y valida contra whitelist para
     * evitar mass-assignment sobre columnas sensibles (id_ticket, id_solicitante, etc).
     */
    public function updateDetalles(Request $request, $id)
    {
        try {
            $ticket = Ticket::where('es_borrador', 0)->findOrFail($id);

            if (in_array($ticket->estado, ['Cerrado', 'Terminado'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'El ticket ya está concluido y no se puede editar.',
                ], 403);
            }

            $camposPermitidos = [
                'asunto', 'descripcion', 'piso',
                'id_edificio', 'id_aula', 'id_servicio',
                'prioridad', 'estado', 'id_admin_atiende'
            ];

            $campo = $request->input('campo');
            $valor = $request->input('valor');

            if (!in_array($campo, $camposPermitidos)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Campo no permitido.',
                ], 422);
            }

            $camposBloqueadosPorRequisicion = [
                'asunto', 'descripcion', 'piso',
                'id_edificio', 'id_aula', 'id_servicio',
                'id_admin_atiende'
            ];

            if ($ticket->requisiciones()->exists() && in_array($campo, $camposBloqueadosPorRequisicion)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pueden modificar los datos del ticket porque ya cuenta con requisiciones asociadas.',
                ], 403);
            }

            $reglas = [
                'asunto'           => 'required|string|max:100',
                'descripcion'      => 'required|string',
                'piso'             => 'required|string|max:20',
                'id_edificio'      => 'required|exists:CatalogoEdificio,id_edificio',
                'id_aula'          => 'required|exists:CatalogoAula,id_aula',
                'id_servicio'      => 'required|exists:CatalogoServicio,id_servicio',
                'prioridad'        => 'required|in:Alta,Media,Baja',
                'estado'           => 'required|in:Pendiente,Atendiendo,Terminado,Cerrado',
                'id_admin_atiende' => [
                    'required',
                    'integer',
                    function ($attribute, $value, $fail) {
                        $admin = User::whereIn('rol', ['Administrador', 'Superadministrador'])
                            ->where('activo', 1)
                            ->where('id', $value)
                            ->first();
                        if (!$admin) {
                            $fail('El administrador seleccionado no es válido o se encuentra inactivo.');
                        }
                    },
                ],
            ];

            $validator = Validator::make(
                [$campo => $valor],
                [$campo => $reglas[$campo]]
            );

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            $cambioReal = (string) $ticket->$campo !== (string) $valor;

            $ticket->$campo = $valor;

            // Si el estado cambia a "Cerrado", registrar la fecha de cierre
            if ($campo === 'estado' && $valor === 'Cerrado') {
                $ticket->fecha_cierre = now();
            }

            $ticket->save();

            // Notificar al solicitante sobre la actualización del ticket solo si cambió el valor
            if ($cambioReal) {
                TicketMailService::enviarTicketActualizado($ticket, $campo, $valor);
            }

            return response()->json([
                'success' => true,
                'campo' => $campo,
                'valor' => $valor,
                'message' => ucfirst($campo) . ' actualizado correctamente.',
            ]);

        } catch (\Exception $e) {
            // Manejo de errores por si falla la base de datos
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al guardar en la base de datos.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}