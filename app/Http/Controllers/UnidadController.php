<?php

namespace App\Http\Controllers;

use App\Models\Unidad;
use App\Models\MaterialRequisicion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UnidadController extends Controller
{
    public function index()
    {
        $unidades = Unidad::where('activo', 1)->orderBy('nombre')->get();
        foreach ($unidades as $u) {
            $u->en_uso = $u->estaEnUso();
        }

        $unidadesInactivas = Unidad::where('activo', 0)->orderBy('nombre')->get();

        return view('paginas.unidades', compact('unidades', 'unidadesInactivas'));
    }

    public function create()
    {
        $unidad = new Unidad();
        return view('paginas.unidades-form', compact('unidad'));
    }

    public function store(Request $request)
    {
        $this->validar($request);

        DB::beginTransaction();

        try {
            Unidad::create([
                'nombre'      => trim($request->nombre),
                'abreviatura' => trim($request->abreviatura),
                'activo'      => 1,
            ]);

            DB::commit();

            return redirect()->route('unidades')->with('success', 'Unidad de medida agregada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear unidad de medida: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->withErrors(['nombre' => 'Ocurrió un error inesperado al guardar la unidad de medida. Inténtalo de nuevo.']);
        }
    }

    public function edit($id)
    {
        $unidad = Unidad::findOrFail($id);
        return view('paginas.unidades-form', compact('unidad'));
    }

    public function update(Request $request, $id)
    {
        $unidad = Unidad::findOrFail($id);

        $this->validar($request, $id);

        DB::beginTransaction();

        try {
            $abrevAnterior = $unidad->abreviatura;
            $nuevaAbrev    = trim($request->abreviatura);

            $unidad->nombre      = trim($request->nombre);
            $unidad->abreviatura = $nuevaAbrev;
            $unidad->save();

            // Si se modificó la abreviatura, actualizar las referencias existentes en cascada lógica
            if ($abrevAnterior !== $nuevaAbrev) {
                MaterialRequisicion::where('unidad', $abrevAnterior)->update(['unidad' => $nuevaAbrev]);
            }

            DB::commit();

            return redirect()->route('unidades')->with('success', 'Unidad de medida actualizada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar unidad de medida: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->withErrors(['nombre' => 'Ocurrió un error inesperado al actualizar la unidad de medida. Inténtalo de nuevo.']);
        }
    }

    /**
     * Valida los datos del formulario de Unidades (store y update).
     */
    private function validar(Request $request, $id = null)
    {
        $request->validate(
            [
                'nombre'      => 'required|string|max:100',
                'abreviatura' => 'required|string|max:20',
            ],
            [
                'nombre.required'      => 'El nombre de la unidad es obligatorio.',
                'nombre.max'           => 'El nombre no puede superar los 100 caracteres.',
                'abreviatura.required' => 'La abreviatura es obligatoria.',
                'abreviatura.max'      => 'La abreviatura no puede superar los 20 caracteres.',
            ]
        );

        $nombre      = trim($request->nombre);
        $abreviatura = trim($request->abreviatura);

        // Validar duplicado por nombre
        $existenteNombre = Unidad::where('nombre', $nombre)
            ->when($id, fn($q) => $q->where('id_unidad', '!=', $id))
            ->first();

        if ($existenteNombre) {
            $msg = $existenteNombre->activo == 0
                ? 'Ya existe una unidad desactivada con el nombre "' . $nombre . '". Puedes reactivarla desde la lista de desactivadas.'
                : 'Ya existe una unidad de medida con ese nombre. Elige uno diferente.';

            throw \Illuminate\Validation\ValidationException::withMessages([
                'nombre' => $msg,
            ]);
        }

        // Validar duplicado por abreviatura
        $existenteAbrev = Unidad::where('abreviatura', $abreviatura)
            ->when($id, fn($q) => $q->where('id_unidad', '!=', $id))
            ->first();

        if ($existenteAbrev) {
            $msg = $existenteAbrev->activo == 0
                ? 'Ya existe una unidad desactivada con la abreviatura "' . $abreviatura . '". Puedes reactivarla desde la lista de desactivadas.'
                : 'Ya existe una unidad de medida con esa abreviatura. Elige una diferente.';

            throw \Illuminate\Validation\ValidationException::withMessages([
                'abreviatura' => $msg,
            ]);
        }
    }

    public function destroy($id)
    {
        $unidad = Unidad::findOrFail($id);
        $enUso  = $unidad->estaEnUso();

        DB::beginTransaction();

        if ($enUso) {
            try {
                $unidad->activo = 0;
                $unidad->save();

                DB::commit();

                return redirect()->route('unidades')->with('success', 'La unidad de medida se desactivó correctamente ya que tiene materiales asociados.');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error al desactivar unidad: ' . $e->getMessage());

                return redirect()->route('unidades')->with('failure', 'Error al desactivar la unidad de medida.');
            }
        } else {
            try {
                $unidad->delete();

                DB::commit();

                return redirect()->route('unidades')->with('success', 'Unidad de medida eliminada correctamente.');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error al eliminar unidad: ' . $e->getMessage());

                return redirect()->route('unidades')->with('failure', 'Error al eliminar la unidad de medida.');
            }
        }
    }

    public function reactivar($id)
    {
        $unidad = Unidad::findOrFail($id);

        DB::beginTransaction();

        try {
            $unidad->activo = 1;
            $unidad->save();

            DB::commit();

            return redirect()->route('unidades')->with('success', 'Unidad de medida reactivada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al reactivar unidad: ' . $e->getMessage());

            return redirect()->route('unidades')->with('failure', 'Error al reactivar la unidad de medida.');
        }
    }
}
