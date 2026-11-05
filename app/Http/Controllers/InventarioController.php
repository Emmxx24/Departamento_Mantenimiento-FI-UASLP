<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use App\Models\MarcaMaterial;
use App\Models\Material;
use App\Models\MaterialRequisicion;
use App\Models\Unidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class InventarioController extends Controller
{
    public function index()
    {
        $materiales = Material::with(['marcas', 'unidad'])
            ->withExists('materialRequisiciones')
            ->where('activo', 1)
            ->get();

        $materialesInactivos = Material::with(['marcas', 'unidad'])
            ->withExists('materialRequisiciones')
            ->where('activo', 0)
            ->get();

        return view('paginas.inventario', compact('materiales', 'materialesInactivos'));
    }

    public function create()
    {
        $material = new Material();
        $marcas = Marca::where('activo', 1)->orderBy('nombre')->get();
        $marcaActual = null;
        $unidades = Unidad::where('activo', 1)->orderBy('nombre')->get();

        return view('paginas.inventario-form', compact('material', 'marcas', 'marcaActual', 'unidades'));
    }

    public function store(Request $request)
    {
        $this->validar($request);

        DB::beginTransaction();

        try {
            // Crear marca nueva si se escribió una
            if ($request->filled('marca_nueva')) {
                $marca = Marca::create(['nombre' => $request->marca_nueva]);
                $marcaId = $marca->id_marca;
            } else {
                $marcaId = $request->marca_id;
            }

            $material = new Material();
            $material->nombre = $request->nombre;
            $material->descripcion = $request->descripcion;
            $material->cantidad = $request->cantidad;
            $material->id_unidad = $request->id_unidad;
            $material->activo = true;

            if ($request->hasFile('imagen')) {
                // Se guarda en storage/app/public/materiales/ para acceso estático rápido
                $ruta = $request->file('imagen')->store('materiales', 'public');
                $material->imagen = $ruta;
            }
            $material->save();

            // Relacionar con la marca en MarcaMaterial
            if ($marcaId) {
                MarcaMaterial::create([
                    'id_material' => $material->id_material,
                    'id_marca' => $marcaId,
                ]);
            }

            DB::commit();

            return redirect()->route('inventario')->with('success', 'Material agregado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear el registro de inventario: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->withErrors(['marca_nueva' => 'Ocurrió un error al agregar el material.']);
        }
    }

    public function edit($id)
    {
        $material = Material::findOrFail($id);
        $marcaActual = $material->marcas()->first()?->id_marca;
        $marcas = Marca::where('activo', 1)
            ->when($marcaActual, fn($q) => $q->orWhere('id_marca', $marcaActual))
            ->orderBy('nombre')
            ->get();
        $unidades = Unidad::where('activo', 1)
            ->when($material->id_unidad, fn($q) => $q->orWhere('id_unidad', $material->id_unidad))
            ->orderBy('nombre')
            ->get();

        return view('paginas.inventario-form', compact('material', 'marcas', 'marcaActual', 'unidades'));
    }

    public function update(Request $request, $id)
    {
        $material = Material::findOrFail($id);

        $this->validar($request, $id);

        DB::beginTransaction();
        try {
            $material->nombre = $request->nombre;
            $material->descripcion = $request->descripcion;
            $material->cantidad = $request->cantidad;
            $material->id_unidad = $request->id_unidad;

            if ($request->hasFile('imagen')) {
                if ($material->imagen && Storage::disk('public')->exists($material->imagen)) {
                    Storage::disk('public')->delete($material->imagen);
                }

                $ruta = $request->file('imagen')->store('materiales', 'public');
                $material->imagen = $ruta;
            }
            $material->save();

            // Determinar la marca a asignar
            if ($request->filled('marca_nueva')) {
                $marca = Marca::create(['nombre' => $request->marca_nueva]);
                $marcaId = $marca->id_marca;
            } else {
                $marcaId = $request->marca_id ?: null;
            }

            // Actualizar relación MarcaMaterial (eliminar la anterior y crear la nueva)
            MarcaMaterial::where('id_material', $material->id_material)->delete();
            if ($marcaId) {
                MarcaMaterial::create([
                    'id_material' => $material->id_material,
                    'id_marca' => $marcaId,
                ]);
            }

            DB::commit();

            return redirect()->route('inventario')->with('success', 'Material actualizado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar inventario: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->withErrors(['nombre' => 'Ocurrió un error al actualizar el material.']);
        }
    }

    /**
     * Valida los datos del formulario de inventario (store y update).
     */
    private function validar(Request $request, $id = null)
    {
        if ($request->marca_id === 'nueva') {
            $request->merge(['marca_id' => null]);
        }

        $request->validate(
            [
                'nombre' => 'required|string|max:100',
                'descripcion' => 'nullable|string|max:255',
                'cantidad' => 'required|integer|min:0',
                'id_unidad' => 'required|exists:CatalogoUnidad,id_unidad',
                'imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'marca_id' => 'nullable|exists:Marca,id_marca',
                'marca_nueva' => 'nullable|string|max:100|unique:Marca,nombre',
            ],
            [
                'nombre.required' => 'El nombre del material es obligatorio.',
                'nombre.max' => 'El nombre no puede superar los 100 caracteres.',
                'cantidad.required' => 'La cantidad es obligatoria.',
                'cantidad.min' => 'La cantidad no puede ser negativa.',
                'id_unidad.required' => 'Selecciona una unidad de medida.',
                'id_unidad.exists' => 'La unidad de medida seleccionada no es válida.',
                'imagen.image' => 'El archivo debe ser una imagen.',
                'imagen.max' => 'La imagen no puede superar los 2 MB.',
                'marca_nueva.max' => 'El nombre de la marca no puede superar los 100 caracteres.',
                'marca_nueva.unique' => 'Ya existe una marca con ese nombre. Selecciona la existente de la lista.',
            ]
        );

        // Validación de duplicados: Mismo nombre, unidad y marca
        $marcaIdVerificar = $request->filled('marca_nueva') ? null : $request->marca_id;
        
        $query = Material::where('nombre', $request->nombre)
                         ->where('id_unidad', $request->id_unidad)
                         ->when($id, fn($q) => $q->where('id_material', '!=', $id));
        
        if ($marcaIdVerificar) {
            $query->whereHas('marcas', function ($q) use ($marcaIdVerificar) {
                $q->where('Marca.id_marca', $marcaIdVerificar);
            });
        } elseif ($request->filled('marca_nueva')) {
            $marcaExiste = Marca::where('nombre', $request->marca_nueva)->first();
            if ($marcaExiste) {
                $query->whereHas('marcas', function ($q) use ($marcaExiste) {
                    $q->where('Marca.id_marca', $marcaExiste->id_marca);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        } else {
            $query->whereDoesntHave('marcas');
        }

        $materialExistente = $query->first();

        if ($materialExistente) {
            $mensaje = $materialExistente->activo == 0
                ? 'Ya existe un material desactivado con el mismo nombre, unidad y marca. Puedes reactivarlo desde la lista de materiales desactivados.'
                : 'Ya existe un material con el mismo nombre, unidad y marca.';

            throw \Illuminate\Validation\ValidationException::withMessages([
                'nombre' => $mensaje,
            ]);
        }
    }

    public function destroy($id)
    {
        $material = Material::findOrFail($id);
        $tieneRequisiciones = MaterialRequisicion::where('id_material', $id)->exists();

        DB::beginTransaction();
        if ($tieneRequisiciones) {
            try {
                $material->activo = false;
                $material->save();

                DB::commit();

                return redirect()->route('inventario')->with('success', 'El material se desactivó correctamente ya que está asociado a requisiciones de tickets.');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error al desactivar material: ' . $e->getMessage());

                return redirect()->route('inventario')->with('failure', 'Error al desactivar el material.');
            }
        } else {
            try {
                if ($material->imagen && Storage::disk('public')->exists($material->imagen)) {
                    Storage::disk('public')->delete($material->imagen);
                }
                MarcaMaterial::where('id_material', $material->id_material)->delete();
                $material->delete();

                DB::commit();

                return redirect()->route('inventario')->with('success', 'Material eliminado permanentemente.');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error al eliminar material: ' . $e->getMessage());

                return redirect()->route('inventario')->with('failure', 'Error al eliminar el material.');
            }
        }
    }

    public function reactivar($id)
    {
        $material = Material::findOrFail($id);

        DB::beginTransaction();
        try {
            $material->activo = true;
            $material->save();

            DB::commit();

            return redirect()->route('inventario')->with('success', 'Material reactivado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al reactivar material: ' . $e->getMessage());

            return redirect()->route('inventario')->with('failure', 'Error al reactivar el material.');
        }
    }

    public function mostrarImagen(Material $material)
    {
        if(! $material->imagen){
            abort(404);
        }
        session_write_close();

        return response()->file(storage_path('app/public/' . $material->imagen), [
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }
}
