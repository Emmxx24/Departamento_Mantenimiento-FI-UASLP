<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialRequisicion;
use App\Models\Poa;
use App\Models\Requisicion;
use App\Models\Ticket;
use App\Models\Unidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RequisicionController extends Controller
{
    // ===================================================================
    // CRUD PRINCIPAL
    // ===================================================================

    /**
     * Formulario de nueva requisición.
     */
    public function create($ticket_id, Request $request)
    {
        $ticket = Ticket::with(['solicitante', 'servicio'])
            ->where('es_borrador', 0)
            ->findOrFail($ticket_id);

        if ($ticket->requisiciones()->count() >= 5) {
            return redirect()->route('requisicion.admin', $ticket->id_ticket)
                ->with('failure', 'Este ticket ya cuenta con el límite máximo de 5 requisiciones.');
        }

        $materiales = Material::with('unidad')->where('activo', 1)->orderBy('nombre')->get();
        $unidades   = Unidad::where('activo', 1)->orderBy('nombre')->get();

        return view('paginas.crear-requisicion', [
            'ticket'      => $ticket,
            'materiales'  => $materiales,
            'unidades'    => $unidades,
            'nombreAdmin' => $this->getNombreAdmin(),
            'admin'       => Auth::user(),
        ]);
    }

    /**
     * Autoguardado AJAX del borrador.
     */
    public function guardarBorrador(Request $request)
    {
        
        $ticket_reqs = Requisicion::where('id_ticket', $request->id_ticket)->get();
        if($ticket_reqs->count() >= 5){
            return response()->json(['message' => 'Ya no se pueden crear más requisiciones para este ticket.'], 409);
        }
        DB::beginTransaction();

        try {
            $id_requisicion = $request->input('id_requisicion');

            if ($id_requisicion) {
                $req = Requisicion::where('id_requisicion', $id_requisicion)->first();

                if (!$req) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Borrador no encontrado.'], 404);
                }

                $req->update([
                    'id_admin'   => $request->id_admin,
                    'proveedor'  => $request->proveedor,
                    'cotizacion' => $request->filled('cotizacion') ? $request->cotizacion : null,
                    'subtotal'   => $request->subtotal ?? 0,
                    'iva'        => 0.16,
                    'total'      => $request->total ?? 0,
                    'fecha_req'  => now(),
                ]);

                DB::commit();
                return response()->json(['success' => true, 'id_requisicion' => $req->id_requisicion]);
            }

            // Crear nuevo borrador
            $req = Requisicion::create([
                'id_admin'    => $request->id_admin,
                'proveedor'   => $request->proveedor,
                'cotizacion'  => $request->filled('cotizacion') ? $request->cotizacion : null,
                'fecha_req'   => now(),
                'subtotal'    => $request->subtotal ?? 0,
                'iva'         => 0.16,
                'total'       => $request->total ?? 0,
                'es_borrador' => 1,
                'id_ticket'   => $request->id_ticket,
            ]);

            DB::commit();
            return response()->json(['success' => true, 'id_requisicion' => $req->id_requisicion]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar borrador de requisición: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al intentar guardar el borrador.',
            ], 500);
        }
    }

    /**
     * Guardar requisición final.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_ticket'  => 'required|exists:Ticket,id_ticket',
            'proveedor'  => 'required|string|max:150',
            'cotizacion' => 'required|in:COMPRA URGENTE,COMPRA NORMAL',
        ], [
            'cotizacion.required' => 'Debes seleccionar el tipo de cotización.',
            'cotizacion.in'       => 'El tipo de cotización seleccionado no es válido.',
        ]);

        $id_requisicion = $request->input('id_requisicion');

        $req = $id_requisicion
            ? Requisicion::where('id_requisicion', $id_requisicion)->where('es_borrador', 1)->first()
            : null;

        if (!$req || $req->materiales()->count() === 0) {
            return response()->json(['message' => 'La requisición debe tener al menos un material.'], 422);
        }
        $ticket_reqs = Requisicion::where('id_ticket', $request->id_ticket)->get();
        if($ticket_reqs->count() >= 5){
            return response()->json(['message' => 'Ya no se pueden crear más requisiciones para este ticket.'], 409);
        }

        try {
            return DB::transaction(function () use ($request, $req) {
                $totales = $this->calcularTotales($req->id_requisicion);

                $req->update([
                    'id_admin'    => $request->id_admin ?? Auth::id(),
                    'proveedor'   => $request->proveedor,
                    'cotizacion'  => $request->cotizacion,
                    'fecha_req'   => now(),
                    'subtotal'    => $totales['subtotal'],
                    'iva'         => 0.16,
                    'total'       => $totales['total'],
                    'es_borrador' => 0,
                    'id_ticket'   => $request->id_ticket,
                ]);

                // Generar POA automáticamente si hay materiales libres (fuera de inventario)
                $hayMaterialesLibres = MaterialRequisicion::where('id_requisicion', $req->id_requisicion)
                    ->whereNull('id_material')
                    ->exists();

                if ($hayMaterialesLibres) {
                    $ticket = Ticket::with(['edificio', 'lugar'])->find($req->id_ticket);
                    Poa::firstOrCreate(
                        ['id_requisicion' => $req->id_requisicion],
                        [
                            'fecha_poa'     => now(),
                            'area'          => $ticket?->edificio?->nombre ?? '',
                            'subarea'       => $ticket?->lugar?->nombre    ?? '',
                            'descripcion'   => $ticket?->descripcion      ?? '',
                            'observaciones' => '',
                            'monto'         => $totales['total'],
                            'es_borrador'   => 0,
                        ]
                    );
                }

                return redirect()->route('requisicion.show', $req->id_requisicion)
                    ->with('success', 'Requisición guardada correctamente.');
            });

        } catch (\Exception $e) {
            Log::error('Error al guardar requisición: ' . $e->getMessage());
            return response()->json(['message' => 'Ocurrió un error al guardar la requisición.'], 500);
        }
    }

    /**
     * Detalle de una requisición.
     */
    public function show($id)
    {
        $req = Requisicion::with(['materiales.material', 'ticket', 'poa', 'admin'])->findOrFail($id);
        $ticket = Ticket::where('id_ticket', $req->id_ticket)->first();

        return view('paginas.detalle-requisicion', compact('req', 'ticket'));
    }

    /**
     * Formulario de edición.
     */
    public function edit($id)
    {
        $req        = Requisicion::with(['materiales.material', 'ticket', 'poa'])->findOrFail($id);
        $materiales = Material::with('unidad')->where('activo', 1)->orderBy('nombre')->get();
        $unidades   = Unidad::where('activo', 1)->orderBy('nombre')->get();

        return view('paginas.crear-requisicion', [
            'ticket'      => $req->ticket,
            'borrador'    => $req,
            'materiales'  => $materiales,
            'unidades'    => $unidades,
            'nombreAdmin' => $this->getNombreAdmin(),
            'modoEdicion' => true,
            'admin'       => Auth::user(),
        ]);
    }

    /**
     * Actualizar requisición existente.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'proveedor'  => 'required|string|max:150',
            'cotizacion' => 'required|in:COMPRA URGENTE,COMPRA NORMAL',
        ], [
            'cotizacion.required' => 'Debes seleccionar el tipo de cotización.',
            'cotizacion.in'       => 'El tipo de cotización seleccionado no es válido.',
        ]);

        $req = Requisicion::findOrFail($id);

        if ($req->materiales()->count() === 0) {
            return response()->json(['message' => 'La requisición debe tener al menos un material.'], 422);
        }

        try {
            return DB::transaction(function () use ($request, $req, $id) {
                $totales = $this->calcularTotales($id);

                $req->update([
                    'proveedor'   => $request->proveedor,
                    'cotizacion'  => $request->cotizacion,
                    'subtotal'    => $totales['subtotal'],
                    'total'       => $totales['total'],
                    'es_borrador' => 0,
                ]);

                if ($req->poa) {
                    $req->poa->update(['monto' => $totales['total']]);
                } else {
                    $hayMaterialesLibres = MaterialRequisicion::where('id_requisicion', $req->id_requisicion)
                        ->whereNull('id_material')
                        ->exists();

                    if ($hayMaterialesLibres) {
                        $ticket = Ticket::with(['edificio', 'lugar'])->find($req->id_ticket);
                        Poa::firstOrCreate(
                            ['id_requisicion' => $req->id_requisicion],
                            [
                                'fecha_poa'     => now(),
                                'area'          => $ticket?->edificio?->nombre ?? '',
                                'subarea'       => $ticket?->lugar?->nombre    ?? '',
                                'descripcion'   => $ticket?->descripcion      ?? '',
                                'observaciones' => '',
                                'monto'         => $totales['total'],
                                'es_borrador'   => 0,
                            ]
                        );
                    }
                }

                return redirect()->route('requisicion.show', $req->id_requisicion)
                    ->with('success', 'Requisición actualizada correctamente.');
            });

        } catch (\Exception $e) {
            Log::error('Error al actualizar requisición #' . $id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Ocurrió un error al actualizar la requisición.'], 500);
        }
    }

    /**
     * Generar POA manualmente desde el detalle de la requisición.
     */
    public function generarPoaManual($id)
    {
        $req = Requisicion::findOrFail($id);

        if ($req->poa) {
            return back()->with('info', 'Esta requisición ya tiene un POA generado.');
        }

        $hayMaterialesLibres = MaterialRequisicion::where('id_requisicion', $req->id_requisicion)
            ->whereNull('id_material')
            ->exists();

        if (!$hayMaterialesLibres) {
            return back()->with('error', 'Solo se puede generar POA si la requisición contiene materiales que no están en el inventario.');
        }

        try {
            DB::transaction(function () use ($req) {
                $ticket = Ticket::with(['edificio', 'lugar'])->find($req->id_ticket);

                Poa::create([
                    'id_requisicion' => $req->id_requisicion,
                    'fecha_poa'      => now(),
                    'area'           => $ticket?->edificio?->nombre ?? '',
                    'subarea'        => $ticket?->lugar?->nombre    ?? '',
                    'descripcion'    => $ticket?->descripcion      ?? '',
                    'observaciones'  => '',
                    'monto'          => $req->total,
                    'es_borrador'    => 0,
                ]);
            });

            return back()->with('success', 'POA generado correctamente.');

        } catch (\Exception $e) {
            Log::error('Error al generar POA manual para requisición #' . $req->id_requisicion . ': ' . $e->getMessage());
            return back()->with('error', 'Ocurrió un error al generar el POA.');
        }
    }

    /**
     * Eliminar requisición, devolviendo stock y eliminando el POA asociado.
     */
    public function destroy($id)
    {
        $req      = Requisicion::with(['materiales', 'poa'])->findOrFail($id);
        $ticketId = $req->id_ticket;

        try {
            DB::transaction(function () use ($req) {
                // Eliminar POA asociado si existe
                if ($req->poa) {
                    $req->poa()->delete();
                }

                // Devolver stock al inventario
                foreach ($req->materiales as $item) {
                    if ($item->id_material) {
                        $material = Material::find($item->id_material);
                        if ($material) {
                            $material->increment('cantidad', $item->cantidad);
                        }
                    }
                }

                // Eliminar materiales y la requisición
                $req->materiales()->delete();
                $req->delete();
            });

            return redirect()->route('requisicion.admin', $ticketId)
                ->with('success', 'Requisición eliminada.');

        } catch (\Exception $e) {
            Log::error('Error al eliminar requisición #' . $id . ': ' . $e->getMessage());
            return back()->with('error', 'Ocurrió un error al eliminar la requisición.');
        }
    }

    // ===================================================================
    // AJAX: MATERIALES
    // ===================================================================

    /**
     * AJAX: Agregar un material a la requisición.
     */
    public function addMaterial(Request $request, $id)
    {
        $request->validate([
            'tipo'         => 'required|in:inventario,libre',
            'cantidad'     => 'required|integer|min:1',
            'unitario'     => 'required|numeric|min:0',
            'id_material'  => 'required_if:tipo,inventario|nullable|exists:Material,id_material',
            'nombre_libre' => 'required_if:tipo,libre|nullable|string|max:150',
            'unidad'       => 'required_if:tipo,libre|nullable|string|max:20',
        ]);

        // Para material libre, el precio unitario debe ser mayor a 0
        if ($request->tipo === 'libre' && $request->unitario <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'El precio unitario es obligatorio para materiales que no están en el inventario.',
            ], 422);
        }

        $req = Requisicion::findOrFail($id);

        if ($req->materiales()->count() >= 16) {
            return response()->json([
                'success' => false,
                'message' => 'La requisición ya tiene el máximo de 16 partidas.',
            ], 422);
        }

        try {
            $item = DB::transaction(function () use ($request, $req) {
                $subtotal = $request->cantidad * $request->unitario;

                if ($request->tipo === 'inventario') {
                    $material = Material::findOrFail($request->id_material);

                    if ($request->cantidad > $material->cantidad) {
                        throw new \Exception("Stock insuficiente. Solo hay {$material->cantidad} disponibles.");
                    }

                    $item = MaterialRequisicion::create([
                        'id_requisicion'   => $req->id_requisicion,
                        'id_material'      => $material->id_material,
                        'especificaciones' => $material->descripcion,
                        'unidad'           => $material->unidad?->abreviatura ?? '',
                        'marca_texto'      => $request->marca_texto ?? null,
                        'cantidad'         => $request->cantidad,
                        'unitario'         => $request->unitario,
                        'subtotal'         => $subtotal,
                    ]);

                    $material->decrement('cantidad', $request->cantidad);

                } else {
                    $item = MaterialRequisicion::create([
                        'id_requisicion'   => $req->id_requisicion,
                        'id_material'      => null,
                        'nombre_libre'     => $request->nombre_libre,
                        'especificaciones' => $request->especificaciones,
                        'unidad'           => $request->unidad,
                        'marca_texto'      => $request->marca_texto,
                        'cantidad'         => $request->cantidad,
                        'unitario'         => $request->unitario,
                        'subtotal'         => $subtotal,
                    ]);
                }

                $this->actualizarTotalesReq($req);

                return $item;
            });

            return response()->json([
                'success' => true,
                'item'    => $this->formatearItem($item),
            ]);

        } catch (\Exception $e) {
            Log::error('Error al agregar material a requisición #' . $id . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * AJAX: Quitar un material de la requisición y devolver stock.
     */
    public function removeMaterial(Request $request, $id, $item_id)
    {
        $req  = Requisicion::findOrFail($id);
        $item = MaterialRequisicion::where('id_item', $item_id)
            ->where('id_requisicion', $req->id_requisicion)
            ->firstOrFail();

        try {
            DB::transaction(function () use ($req, $item) {
                if ($item->id_material) {
                    $material = Material::find($item->id_material);
                    if ($material) {
                        $material->increment('cantidad', $item->cantidad);
                    }
                }

                $item->delete();
                $this->actualizarTotalesReq($req);
            });

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Error al eliminar material de requisición #' . $id . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'No se pudo eliminar la partida.'], 500);
        }
    }

    /**
     * AJAX: Datos de un material del inventario para el modal.
     */
    public function getMaterialData($id)
    {
        $material = Material::with(['marcas', 'unidad'])->findOrFail($id);

        return response()->json([
            'nombre'      => $material->nombre,
            'descripcion' => $material->descripcion,
            'unidad'      => $material->unidad?->abreviatura ?? '',
            'marcas'      => $material->marcas->map(fn($m) => [
                'id_marca' => $m->id_marca,
                'nombre'   => $m->nombre,
            ])->values(),
        ]);
    }

    // ===================================================================
    // EXPORTAR
    // ===================================================================

    public function exportPdf($id)
    {
        $req = Requisicion::with(['materiales.material', 'ticket', 'poa', 'admin'])->findOrFail($id);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('paginas.requisicion-pdf', compact('req'));
        return $pdf->stream('requisicion-' . $req->id_requisicion . '.pdf');
    }

    public function exportExcel($id)
    {
        $req = Requisicion::with(['materiales.material', 'ticket', 'poa', 'admin'])->findOrFail($id);
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\RequisicionExport($req),
            'requisicion-' . $req->id_requisicion . '.xlsx'
        );
    }

    // ===================================================================
    // HELPERS PRIVADOS
    // ===================================================================

    /**
     * Construye el nombre completo del admin autenticado.
     */
    private function getNombreAdmin(): string
    {
        $admin = Auth::user();
        return trim(($admin->nombre ?? '') . ' ' . ($admin->ap_pat ?? '') . ' ' . ($admin->ap_mat ?? ''));
    }

    /**
     * Calcula subtotal, IVA y total de una requisición.
     */
    private function calcularTotales($id_requisicion): array
    {
        $subtotal = MaterialRequisicion::where('id_requisicion', $id_requisicion)->sum('subtotal');
        $iva      = round($subtotal * 0.16, 2);
        $total    = round($subtotal + $iva, 2);

        return compact('subtotal', 'iva', 'total');
    }

    /**
     * Recalcula y persiste subtotal y total en la BD.
     */
    private function actualizarTotalesReq(Requisicion $req): void
    {
        $totales = $this->calcularTotales($req->id_requisicion);
        $req->update([
            'subtotal' => $totales['subtotal'],
            'total'    => $totales['total'],
        ]);
    }

    /**
     * Formatea un MaterialRequisicion para la respuesta JSON.
     */
    private function formatearItem(MaterialRequisicion $item): array
    {
        $item->load('material');
        return [
            'id_item'          => $item->id_item,
            'nombre'           => $item->material ? $item->material->nombre : $item->nombre_libre,
            'especificaciones' => $item->especificaciones,
            'unidad'           => $item->unidad,
            'marca_texto'      => $item->marca_texto,
            'cantidad'         => $item->cantidad,
            'unitario'         => $item->unitario,
            'subtotal'         => $item->subtotal,
            'es_libre'         => is_null($item->id_material),
        ];
    }

    /*
    * Regresa la nueva página de requisiciones de un ticket.
    */
    public function requisiciones($ticket_id, Request $request)
    {
        $ticket = Ticket::with(['solicitante', 'servicio'])
            ->where('es_borrador', 0)
            ->findOrFail($ticket_id);

        $requisiciones = Requisicion::with(['poa', 'admin'])->where('id_ticket', $ticket_id)->get();

        return view('paginas.requisiciones', [
            'ticket'      => $ticket,
            'requisiciones'=>$requisiciones]); 
    }
}