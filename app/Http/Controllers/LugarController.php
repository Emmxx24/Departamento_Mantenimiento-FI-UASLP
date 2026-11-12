<?php

namespace App\Http\Controllers;

use App\Models\Lugar;
use App\Models\Aula;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LugarController extends Controller
{
    public function index()
    {
        $lugares = Lugar::where('activo', 1)->withExists('aulas')->get();
        $lugaresInactivos = Lugar::where('activo', 0)->get();
        return view('paginas.lugares', compact('lugares', 'lugaresInactivos'));
    }

    public function create()
    {
        $lugar = new Lugar();
        return view('paginas.lugares-form', compact('lugar'));
    }

    public function store(Request $request)
    {
        $this->validar($request);

        DB::beginTransaction();

        try {
            Lugar::create(['nombre' => $request->nombre]);

            DB::commit();
            return redirect()->route('lugares')->with('success', 'Lugar agregado correctamente.');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al crear lugar: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->withErrors(['nombre' => 'Ocurrió un error al guardar el lugar. Inténtalo de nuevo.']);
        }
    }

    public function edit($id)
    {
        $lugar = Lugar::findOrFail($id);
        return view('paginas.lugares-form', compact('lugar'));
    }

    public function update(Request $request, $id)
    {
        $lugar = Lugar::findOrFail($id);

        $this->validar($request, $id);

        DB::beginTransaction();

        try {
            $lugar->nombre = $request->nombre;
            $lugar->save();

            DB::commit();
            return redirect()->route('lugares')->with('success', 'Lugar actualizado correctamente.');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al actualizar lugar: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->withErrors(['nombre' => 'Ocurrió un error al actualizar el lugar. Inténtalo de nuevo.']);
        }
    }

    /**
     * Valida los datos del formulario de Lugares (store y update).
     */
    private function validar(Request $request, $id = null)
    {
        $request->validate(
            [
                'nombre' => 'required|string|max:100',
            ],
            [
                'nombre.required' => 'El nombre del lugar es obligatorio.',
                'nombre.max'      => 'El nombre no puede superar los 100 caracteres.',
            ]
        );

        $existente = Lugar::where('nombre', $request->nombre)
            ->when($id, fn($q) => $q->where('id_lugar', '!=', $id))
            ->first();

        if ($existente) {
            $mensaje = $existente->activo == 0
                ? 'Ya existe un lugar desactivado con el nombre "' . $request->nombre . '". Puedes reactivarlo desde la lista de desactivados.'
                : 'Ya existe un lugar con ese nombre. Elige uno diferente.';

            throw \Illuminate\Validation\ValidationException::withMessages([
                'nombre' => $mensaje,
            ]);
        }
    }

    public function destroy($id)
    {
        $lugar = Lugar::findOrFail($id);

        $tieneAulas = Aula::where('id_lugar', $id)->exists();

        DB::beginTransaction();

        if ($tieneAulas) {
            try {
                $lugar->activo = 0;
                $lugar->save();
                DB::commit();
                return redirect()->route('lugares')->with('success', 'El lugar se desactivó correctamente ya que tiene aulas asociadas.');
            } catch (\Exception $e) {
                DB::rollback();
                Log::error('Error al desactivar lugar: ' . $e->getMessage());
                return redirect()->route('lugares')->with('failure', 'Error al desactivar el lugar.');
            }
        } else {
            try {
                $lugar->delete();
                DB::commit();
                return redirect()->route('lugares')->with('success', 'Lugar eliminado correctamente.');
            } catch (\Exception $e) {
                DB::rollback();
                Log::error('Error al eliminar lugar: ' . $e->getMessage());
                return redirect()->route('lugares')->with('failure', 'Error al eliminar el lugar.');
            }
        }
    }

    public function reactivar($id)
    {
        $lugar = Lugar::findOrFail($id);

        DB::beginTransaction();

        try {
            $lugar->activo = 1;
            $lugar->save();

            DB::commit();
            return redirect()->route('lugares')->with('success', 'Lugar reactivado correctamente.');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al reactivar lugar: ' . $e->getMessage());

            return redirect()->route('lugares')->with('failure', 'Error al reactivar el lugar.');
        }
    }
}
