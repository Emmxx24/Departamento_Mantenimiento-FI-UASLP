<?php

namespace App\Http\Controllers;

use App\Models\Poa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PoaController extends Controller
{
    public function show($id)
    {
        $poa = Poa::with(['requisicion.ticket'])->findOrFail($id);
        return view('paginas.detalle-poa', compact('poa'));
    }

    public function edit($id)
    {
        $poa = Poa::with(['requisicion.ticket'])->findOrFail($id);
        return view('paginas.editar-poa', compact('poa'));
    }

    public function update(Request $request, $id)
    {
        $poa = Poa::findOrFail($id);

        try {
            DB::transaction(function () use ($poa, $request) {
                $poa->update([
                    'observaciones' => $request->filled('observaciones') ? trim($request->observaciones) : null,
                    'es_borrador'   => 0,
                ]);
            });

            return redirect()->route('poa.show', $poa->id_poa)
                ->with('success', 'POA guardado correctamente.');

        } catch (\Exception $e) {
            Log::error('Error al actualizar POA #' . $id . ': ' . $e->getMessage());
            return back()->with('error', 'Ocurrió un error al guardar el POA.');
        }
    }

    public function exportPdf($id)
    {
        $poa = Poa::with(['requisicion.ticket'])->findOrFail($id);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('paginas.poa-pdf', compact('poa'))
            ->setPaper('letter', 'portrait');
        return $pdf->stream('poa-' . $poa->id_poa . '.pdf');
    }
}