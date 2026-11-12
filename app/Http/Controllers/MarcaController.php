<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MarcaController extends Controller
{
    public function index()
    {
        $marcas = Marca::where('activo', 1)->withExists('materiales')->orderBy('nombre')->get();
        $marcasInactivos = Marca::where('activo', 0)->orderBy('nombre')->get();

        return view('paginas.marcas', compact('marcas', 'marcasInactivos'));
    }

    public function create()
    {
        $marca = new Marca();

        return view('paginas.marcas-form', compact('marca'));
    }

    public function store(Request $request)
    {
        $this->validar($request);

        DB::beginTransaction();

        try {
            Marca::create([
                'nombre' => trim($request->nombre),
                'activo' => 1,
            ]);

            DB::commit();

            return redirect()->route('marcas')->with('success', 'Marca agregada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear marca: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->withErrors(['nombre' => 'Ocurrió un error inesperado al guardar la marca. Inténtalo de nuevo.']);
        }
    }

    public function edit($id)
    {
        $marca = Marca::findOrFail($id);

        return view('paginas.marcas-form', compact('marca'));
    }

    public function update(Request $request, $id)
    {
        $marca = Marca::findOrFail($id);

        $this->validar($request, $id);

        DB::beginTransaction();

        try {
            $marca->nombre = trim($request->nombre);
            $marca->save();

            DB::commit();

            return redirect()->route('marcas')->with('success', 'Marca actualizada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar marca: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->withErrors(['nombre' => 'Ocurrió un error inesperado al actualizar la marca. Inténtalo de nuevo.']);
        }
    }

    /**
     * Valida los datos del formulario de Marcas (store y update).
     */
    private function validar(Request $request, $id = null)
    {
        $request->validate(
            [
                'nombre' => 'required|string|max:100',
            ],
            [
                'nombre.required' => 'El nombre de la marca es obligatorio.',
                'nombre.max' => 'El nombre no puede superar los 100 caracteres.',
            ]
        );

        $nombre = trim($request->nombre);

        $existente = Marca::where('nombre', $nombre)
            ->when($id, fn ($q) => $q->where('id_marca', '!=', $id))
            ->first();

        if ($existente) {
            $mensaje = $existente->activo == 0
                ? 'Ya existe una marca desactivada con el nombre "' . $nombre . '". Puedes reactivarla desde la lista de desactivadas.'
                : 'Ya existe una marca con ese nombre. Elige uno diferente.';

            throw \Illuminate\Validation\ValidationException::withMessages([
                'nombre' => $mensaje,
            ]);
        }
    }

    public function destroy($id)
    {
        $marca = Marca::findOrFail($id);
        $tieneMateriales = $marca->materiales()->exists();

        DB::beginTransaction();

        if ($tieneMateriales) {
            try {
                $marca->activo = 0;
                $marca->save();

                DB::commit();

                return redirect()->route('marcas')->with('success', 'La marca se desactivó correctamente ya que tiene materiales asociados en el inventario.');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error al desactivar marca: ' . $e->getMessage());

                return redirect()->route('marcas')->with('failure', 'Error al desactivar la marca.');
            }
        } else {
            try {
                $marca->delete();

                DB::commit();

                return redirect()->route('marcas')->with('success', 'Marca eliminada permanentemente.');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error al eliminar marca: ' . $e->getMessage());

                return redirect()->route('marcas')->with('failure', 'Error al eliminar la marca.');
            }
        }
    }

    public function reactivar($id)
    {
        $marca = Marca::findOrFail($id);

        DB::beginTransaction();

        try {
            $marca->activo = 1;
            $marca->save();

            DB::commit();

            return redirect()->route('marcas')->with('success', 'Marca reactivada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al reactivar marca: ' . $e->getMessage());

            return redirect()->route('marcas')->with('failure', 'Error al reactivar la marca.');
        }
    }
}
