<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reporte;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ReportesController extends Controller
{
    public function index()
    {
        return view('paginas.reportes', [
            'edificios' => \App\Models\Edificio::all(),
            'servicios' => \App\Models\Servicio::all(),
            'lugares'   => \App\Models\Lugar::all(),
            'admins'    => \App\Models\User::whereIn('rol', ['Administrador', 'Superadministrador'])->get(),
            'reportes'  => \App\Models\Reporte::all()
        ]);
    }

    public function configuracion(Request $request)
    {
        return response()->json(['success' => true]);
    }

    // =========================
    // FUNCIÓN CENTRAL
    // =========================
    private function obtenerDatosReporte($reporte)
    {
        $query = Ticket::query()->where('es_borrador', 0);

        if ($reporte->fecha_ini) {
            $query->where('fecha_creacion', '>=', $reporte->fecha_ini);
        }

        if ($reporte->fecha_fin) {
            $query->where('fecha_creacion', '<=', $reporte->fecha_fin);
        }

        if ($reporte->id_edificio) {
            $query->where('id_edificio', $reporte->id_edificio);
        }

        if ($reporte->id_servicio) {
            $query->where('id_servicio', $reporte->id_servicio);
        }

        if ($reporte->id_lugar) {
            $query->whereHas('lugar', function ($q) use ($reporte) {
                $q->where('id_lugar', $reporte->id_lugar);
            });
        }

        if($reporte->id_admin) {
            $query->where('id_admin_atiende', $reporte->id_admin);
        }

        if ($reporte->prioridad) {
            $query->where('prioridad', $reporte->prioridad);
        }

        if ($reporte->estado) {
            $query->where('estado', $reporte->estado);
        }

        $tickets = $query->with([
            'solicitante',
            'edificio',
            'lugar',
            'servicio'
        ])->get();

        // =========================
        // MÉTRICAS
        // =========================
        $total      = $tickets->count();
        $atendidos  = $tickets->where('estado', 'Atendiendo')->count();
        $cerrados   = $tickets->where('estado', 'Cerrado')->count();
        $enProceso  = $tickets->where('estado', 'Pendiente')->count();

        // Tiempo promedio
        $promedio = $tickets->avg(function ($t) {
            if ($t->fecha_cierre && $t->fecha_creacion) {
                return Carbon::parse($t->fecha_creacion)
                    ->diffInHours($t->fecha_cierre);
            }

            return null;
        });

        // Tickets por edificio
        $porEdificio = $tickets
            ->groupBy('edificio.nombre')
            ->map
            ->count();

        // Tickets por área
        $porArea = $tickets
            ->groupBy('servicio.nombre')
            ->map
            ->count();

        // Periodo: si no hay filtro de fecha, se usa la fecha del ticket más viejo / más reciente
        $inicio = $reporte->fecha_ini ?? $tickets->min('fecha_creacion');
        $fin    = $reporte->fecha_fin ?? $tickets->max('fecha_creacion');

        $periodoInicio = $inicio ? Carbon::parse($inicio)->format('d/m/Y') : 'N/A';
        $periodoFin    = $fin ? Carbon::parse($fin)->format('d/m/Y') : 'N/A';

        return compact(
            'periodoInicio',
            'periodoFin',
            'tickets',
            'total',
            'atendidos',
            'cerrados',
            'enProceso',
            'promedio',
            'porEdificio',
            'porArea'
        );
    }

    // =========================
    // GENERAR REPORTE
    // =========================
    public function generar(Request $request)
    {
        try {

            if ($request->filled('periodo')) {

                switch ($request->periodo) {

                    case 'semana':
                        $fechaInicio = \Carbon\Carbon::now()->subWeek();
                        break;

                    case 'mes':
                        $fechaInicio = \Carbon\Carbon::now()->subMonth();
                        break;

                    case 'semestre':
                        $fechaInicio = \Carbon\Carbon::now()->subMonths(6);
                        break;

                    default:
                        $fechaInicio = null;
                        break;
                }

                $fechaFin = now();

            } else {

                // Fechas del calendario: inicio desde las 00:00:00 y fin hasta las 23:59:59 (día completo)
                $fechaInicio = $request->filled('fecha_inicio')
                    ? Carbon::parse($request->fecha_inicio)->startOfDay()
                    : null;
                $fechaFin = $request->filled('fecha_fin')
                    ? Carbon::parse($request->fecha_fin)->endOfDay()
                    : null;
            }

            $reporte = \App\Models\Reporte::create([
                'id_edificio' => $request->id_edificio,
                'id_servicio' => $request->id_servicio,
                'id_lugar'    => $request->id_lugar,
                'id_admin'    => $request->id_admin,
                'prioridad'   => $request->prioridad,
                'estado'      => $request->estado,

                'fecha_rep' => now(),
                'fecha_ini' => $fechaInicio,
                'fecha_fin' => $fechaFin,
            ]);

            $datos = $this->obtenerDatosReporte($reporte);

            return response()->json([
                'success'       => true,
                'reporte_id'    => $reporte->id_reporte,
                'total_tickets' => $datos['total']
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile()
            ], 500);
        }
    }

    // =========================
    // PDF
    // =========================
    public function pdf(Request $request)
    {
        $reporte = Reporte::findOrFail($request->reporte_id);

        $datos = $this->obtenerDatosReporte($reporte);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'paginas.reporte-pdf',
            array_merge($datos, [
                'reporte' => $reporte
            ])
        )->setPaper('A4');

        $nombreArchivo = 'reporte_' . $reporte->id_reporte . '.pdf';
        $rutaArchivo = 'reportes/' . $nombreArchivo;

        Storage::disk('local')->put($rutaArchivo, $pdf->output());
        $reporte->update(['ruta_pdf' => $rutaArchivo]);

        return Storage::disk('local')->download($rutaArchivo, $nombreArchivo, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $nombreArchivo . '"'
        ]);
    }

    // =========================
    // EXCEL
    // =========================
    public function excel(Request $request)
    {
        $reporte = Reporte::findOrFail($request->reporte_id);
        $datos = $this->obtenerDatosReporte($reporte);

        $nombreArchivo = 'reporte_' . $reporte->id_reporte . '.xlsx';
        $rutaArchivo = 'reportes/excel/' . $nombreArchivo;

        $export = new \App\Exports\ReportesExport($reporte, $datos);
        Excel::store($export, $rutaArchivo, 'local');
        $reporte->update(['ruta_excel' => $rutaArchivo]);

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ReportesExport($reporte, $datos),
            'reporte_' . $reporte->id_reporte . '.xlsx'
        );
    }
}