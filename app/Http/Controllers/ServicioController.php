<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ServicioController extends Controller
{
    public function index()
    {
        $servicios          = Servicio::where('activo', 1)->withExists('tickets')->get();
        $serviciosInactivos = Servicio::where('activo', 0)->get();
        return view('paginas.servicios', compact('servicios', 'serviciosInactivos'));
    }

    public function create()
    {
        $servicio = new Servicio();
        return view('paginas.servicios-form', compact('servicio'));
    }

    public function store(Request $request)
    {
        $this->validar($request);

        DB::BeginTransaction();

        try {
            Servicio::create([
                'nombre'      => $request->nombre,
                'descripcion' => $request->descripcion,
            ]);

            DB::Commit();
            return redirect()->route('servicios')->with('success', 'Servicio agregado correctamente.');
        } catch (\Exception $e) {
            DB::Rollback();
            Log::error('Error al crear el registro: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->withErrors(['nombre' => 'Ocurrió un error inesperado al guardar el servicio. Inténtalo de nuevo.']);
        }
    }

    public function edit($id)
    {
        $servicio = Servicio::findOrFail($id);
        return view('paginas.servicios-form', compact('servicio'));
    }

    public function update(Request $request, $id)
    {
        $servicio = Servicio::findOrFail($id);

        $this->validar($request, $id);

        DB::BeginTransaction();

        try {
            $servicio->nombre      = $request->nombre;
            $servicio->descripcion = $request->descripcion;
            $servicio->save();

            DB::Commit();

            return redirect()->route('servicios')->with('success', 'Servicio actualizado correctamente.');
        } catch (\Exception $e) {
            DB::Rollback();
            Log::error('Error al actualizar servicio: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->withErrors(['nombre' => 'Ocurrió un error inesperado al actualizar el servicio. Inténtalo de nuevo.']);
        }
    }

    /**
     * Valida los datos del formulario de Servicios (store y update).
     */
    private function validar(Request $request, $id = null)
    {
        $request->validate(
            [
                'nombre'      => 'required|string|max:100',
                'descripcion' => 'nullable|string|max:255',
            ],
            [
                'nombre.required' => 'El nombre del servicio es obligatorio.',
                'nombre.max'      => 'El nombre no puede superar los 100 caracteres.',
                'descripcion.max' => 'La descripción no puede superar los 255 caracteres.',
            ]
        );

        $existente = Servicio::where('nombre', $request->nombre)
            ->when($id, fn($q) => $q->where('id_servicio', '!=', $id))
            ->first();

        if ($existente) {
            $mensaje = $existente->activo == 0
                ? 'Ya existe un servicio desactivado con el nombre "' . $request->nombre . '". Puedes reactivarlo desde la lista de desactivados.'
                : 'Ya existe un servicio con ese nombre. Elige uno diferente.';

            throw \Illuminate\Validation\ValidationException::withMessages([
                'nombre' => $mensaje,
            ]);
        }
    }

    public function destroy($id)
    {
        $servicio = Servicio::findOrFail($id);
        $tieneTickets = Ticket::where('id_servicio', $id)->exists();

        DB::BeginTransaction();
        if ($tieneTickets) {
            try {
                $servicio->activo = 0;
                $servicio->save();

                DB::Commit();

                return redirect()->route('servicios')->with('success', 'El servicio se desactivó correctamente ya que tiene tickets asociados.');
            } catch (\Exception $e) {
                DB::Rollback();
                Log::error('Error al desactivar servicio: ' . $e->getMessage());

                return redirect()->route('servicios')->with('failure', 'Error al desactivar el servicio.');
            }
        } else {
            try {
                $servicio->delete();

                DB::Commit();

                return redirect()->route('servicios')->with('success', 'Servicio eliminado correctamente.');
            } catch (\Exception $e) {
                DB::Rollback();
                Log::error('Error al eliminar servicio: ' . $e->getMessage());

                return redirect()->route('servicios')->with('failure', 'Error al eliminar el servicio.');
            }
        }
    }

    public function reactivar($id)
    {
        $servicio = Servicio::findOrFail($id);

        DB::BeginTransaction();

        try {
            $servicio->activo = 1;
            $servicio->save();

            DB::Commit();
            return redirect()->route('servicios')->with('success', 'Servicio reactivado correctamente.');
        } catch (\Exception $e) {
            DB::Rollback();
            Log::error('Error al reactivar servicio: ' . $e->getMessage());

            return redirect()->route('servicios')->with('failure', 'Error al reactivar el servicio.');
        }
    }
}