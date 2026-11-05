<?php

namespace App\Http\Controllers;

use App\Models\Edificio;
use App\Models\Piso;
use App\Models\Aula;
use App\Models\Lugar;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EdificioController extends Controller
{
    public function index()
    {
        $edificios = Edificio::where('activo', 1)
            ->withExists('tickets')
            ->withCount([
                'pisos' => function ($q) {
                    $q->where('activo', 1);
                },
                'aulas' => function ($q) {
                    $q->where('CatalogoAula.activo', 1);
                }
            ])
            ->get();

        $edificiosInactivos = Edificio::where('activo', 0)
            ->withExists('tickets')
            ->withCount([
                'pisos',
                'aulas'
            ])
            ->get();

        return view('paginas.edificios', compact('edificios', 'edificiosInactivos'));
    }

    public function create()
    {
        $edificio = new Edificio();
        $lugares = Lugar::where('activo', 1)->orderBy('nombre')->get();
        return view('paginas.edificios-form', compact('edificio', 'lugares'));
    }

    public function store(Request $request)
    {
        $this->validarEdificio($request);

        DB::beginTransaction();

        try {
            $edificio = Edificio::create([
                'nombre' => $request->nombre,
                'activo' => 1,
            ]);

            $pisosData = $request->pisos;

            for ($i = 0; $i < (int)$request->cantidad_pisos; $i++) {
                $pisoData = $pisosData[$i] ?? [];
                $pisoActivo = isset($pisoData['activo']) ? (int)$pisoData['activo'] : 1;

                $piso = Piso::create([
                    'piso'        => $i,
                    'id_edificio' => $edificio->id_edificio,
                    'activo'      => $pisoActivo,
                ]);

                if (isset($pisoData['aulas'])) {
                    foreach ($pisoData['aulas'] as $aulaData) {
                        if (!empty($aulaData['nombre'])) {
                            Aula::create([
                                'nombre'   => trim($aulaData['nombre']),
                                'id_piso'  => $piso->id_piso,
                                'id_lugar' => $aulaData['id_lugar'],
                                'activo'   => 1,
                            ]);
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->route('edificios')->with('success', 'Edificio creado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear edificio: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->withErrors(['nombre' => 'Ocurrió un error al crear el edificio. Verifica que el nombre no esté duplicado.']);
        }
    }

    public function edit($id)
    {
        $edificio = Edificio::with([
            'pisos' => function ($q) {
                $q->orderBy('piso', 'asc');
            },
            'pisos.aulas' => function ($q) {
                $q->with('lugar');
            }
        ])->findOrFail($id);

        $lugares = Lugar::where('activo', 1)->orderBy('nombre')->get();

        // Identificar qué aulas tienen tickets para informar al frontend
        $todasAulasIds = $edificio->pisos->flatMap->aulas->pluck('id_aula')->toArray();
        $aulasConTickets = Ticket::whereIn('id_aula', $todasAulasIds)
            ->pluck('id_aula')
            ->flip()
            ->toArray();

        // Serializar datos de pisos para JS incluyendo activas e inactivas
        $pisosData = $edificio->pisos->sortBy('piso')->values()->map(function ($piso) use ($aulasConTickets) {
            return [
                'id_piso' => $piso->id_piso,
                'piso'    => $piso->piso,
                'nombre'  => $piso->nombre_display,
                'activo'  => (int)$piso->activo,
                'aulas'   => $piso->aulas->where('activo', 1)->values()->map(function ($aula) use ($aulasConTickets) {
                    return [
                        'id_aula'       => $aula->id_aula,
                        'nombre'        => $aula->nombre,
                        'id_lugar'      => $aula->id_lugar,
                        'tiene_tickets' => isset($aulasConTickets[$aula->id_aula]) ? 1 : 0,
                        'lugar_activo'  => $aula->lugar ? (int)$aula->lugar->activo : 1,
                    ];
                }),
                'aulasInactivas' => $piso->aulas->where('activo', 0)->values()->map(function ($aula) use ($aulasConTickets) {
                    return [
                        'id_aula'       => $aula->id_aula,
                        'nombre'        => $aula->nombre,
                        'id_lugar'      => $aula->id_lugar,
                        'lugar_nombre'  => $aula->lugar ? $aula->lugar->nombre : 'Sin tipo',
                        'lugar_activo'  => $aula->lugar ? (int)$aula->lugar->activo : 0,
                        'tiene_tickets' => isset($aulasConTickets[$aula->id_aula]) ? 1 : 0,
                    ];
                }),
            ];
        });

        return view('paginas.edificios-form', compact('edificio', 'lugares', 'pisosData'));
    }

    public function update(Request $request, $id)
    {
        $edificio = Edificio::findOrFail($id);

        $this->validarEdificio($request, $id);

        DB::beginTransaction();

        try {
            $edificio->nombre = $request->nombre;
            $edificio->save();

            $cantidadPisosNueva = (int)$request->cantidad_pisos;
            $pisosData = $request->pisos;

            // Pisos existentes actualmente en BD
            $pisosExistentes = $edificio->pisos()->orderBy('piso', 'asc')->get();
            $cantidadPisosExistente = $pisosExistentes->count();

            // 1. Si se reduce la cantidad de pisos, desactivar o eliminar los que quedan arriba
            if ($cantidadPisosNueva < $cantidadPisosExistente) {
                $pisosAEliminar = $pisosExistentes->where('piso', '>=', $cantidadPisosNueva);

                foreach ($pisosAEliminar as $pisoEliminar) {
                    $aulasPisoIds = Aula::where('id_piso', $pisoEliminar->id_piso)->pluck('id_aula')->toArray();
                    $tieneAulasEnUso = Ticket::whereIn('id_aula', $aulasPisoIds)->exists()
                        || Ticket::where('id_edificio', $id)->where('piso', $pisoEliminar->piso)->exists();

                    if ($tieneAulasEnUso) {
                        $pisoEliminar->activo = 0;
                        $pisoEliminar->save();
                        Aula::where('id_piso', $pisoEliminar->id_piso)->update(['activo' => 0]);
                    } else {
                        Aula::where('id_piso', $pisoEliminar->id_piso)->delete();
                        $pisoEliminar->delete();
                    }
                }
            }

            // 2. Si se agregan pisos, reactivar o crear
            if ($cantidadPisosNueva > $cantidadPisosExistente) {
                for ($i = $cantidadPisosExistente; $i < $cantidadPisosNueva; $i++) {
                    $pisoPrevio = Piso::where('id_edificio', $edificio->id_edificio)
                                      ->where('piso', $i)
                                      ->first();

                    if ($pisoPrevio) {
                        $pisoPrevio->activo = 1;
                        $pisoPrevio->save();
                    } else {
                        Piso::create([
                            'piso'        => $i,
                            'id_edificio' => $edificio->id_edificio,
                            'activo'      => 1,
                        ]);
                    }
                }
            }

            // 3. Sincronizar pisos y aulas vigentes
            $pisosActualizados = $edificio->pisos()
                ->where('piso', '<', $cantidadPisosNueva)
                ->orderBy('piso', 'asc')
                ->get();

            foreach ($pisosActualizados as $piso) {
                $indicePiso = $piso->piso;
                $dataDelPiso = $pisosData[$indicePiso] ?? null;

                // Actualizar estado manual del piso (activo o en mantenimiento)
                if ($dataDelPiso && isset($dataDelPiso['activo'])) {
                    $piso->activo = (int)$dataDelPiso['activo'];
                    $piso->save();
                }

                // Aulas activas enviadas por el formulario
                $aulasActivasEnviadas = collect($dataDelPiso['aulas'] ?? []);
                $idsAulasActivas = $aulasActivasEnviadas->pluck('id_aula')->filter()->map(fn($v) => (int)$v)->toArray();

                // Aulas inactivas que el usuario mantuvo en la lista de inactivas
                $idsAulasInactivasMantenidas = collect($dataDelPiso['aulas_inactivas'] ?? [])->filter()->map(fn($v) => (int)$v)->toArray();

                // Todas las aulas en BD de este piso
                $aulasExistentesBD = Aula::where('id_piso', $piso->id_piso)->get();

                foreach ($aulasExistentesBD as $aulaBD) {
                    $idAulaBD = (int)$aulaBD->id_aula;

                    if (in_array($idAulaBD, $idsAulasActivas)) {
                        // Está en la lista activa, se actualiza más adelante
                        continue;
                    }

                    if (in_array($idAulaBD, $idsAulasInactivasMantenidas)) {
                        // El usuario la puso o la dejó inactiva/clausurada
                        if ($aulaBD->activo != 0) {
                            $aulaBD->activo = 0;
                            $aulaBD->save();
                        }
                    } else {
                        // No está en activas ni en inactivas => se solicitó eliminar
                        $this->eliminarODesactivarAula($aulaBD);
                    }
                }

                // Crear o actualizar aulas activas
                foreach ($aulasActivasEnviadas as $aulaData) {
                    $nombre = trim($aulaData['nombre'] ?? '');
                    $idLugar = $aulaData['id_lugar'] ?? null;
                    $idAula = !empty($aulaData['id_aula']) ? (int)$aulaData['id_aula'] : null;

                    if (empty($nombre) || empty($idLugar)) {
                        continue;
                    }

                    if ($idAula) {
                        $aula = Aula::find($idAula);
                        if ($aula) {
                            $aula->nombre   = $nombre;
                            $aula->id_lugar = $idLugar;
                            $aula->id_piso  = $piso->id_piso;
                            $aula->activo   = 1;
                            $aula->save();
                        }
                    } else {
                        Aula::create([
                            'nombre'   => $nombre,
                            'id_piso'  => $piso->id_piso,
                            'id_lugar' => $idLugar,
                            'activo'   => 1,
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('edificios')->with('success', 'Edificio actualizado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar edificio: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->withErrors(['nombre' => 'Ocurrió un error al actualizar el edificio. Verifica que el nombre no esté duplicado.']);
        }
    }

    public function destroy($id)
    {
        $edificio = Edificio::findOrFail($id);
        $tieneTickets = Ticket::where('id_edificio', $id)->exists();

        DB::beginTransaction();

        if ($tieneTickets) {
            try {
                // Desactivar edificio (mantiene intactos los estados individuales de pisos y aulas)
                $edificio->activo = 0;
                $edificio->save();

                DB::commit();
                return redirect()->route('edificios')->with('success', 'El edificio se desactivó correctamente ya que tiene tickets asociados.');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error al desactivar edificio: ' . $e->getMessage());

                return redirect()->route('edificios')->with('failure', 'Error al desactivar el edificio.');
            }
        } else {
            try {
                // Eliminar en cascada
                $edificio->delete();

                DB::commit();
                return redirect()->route('edificios')->with('success', 'Edificio eliminado permanentemente.');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error al eliminar edificio: ' . $e->getMessage());

                return redirect()->route('edificios')->with('failure', 'Error al eliminar el edificio.');
            }
        }
    }

    public function reactivar($id)
    {
        $edificio = Edificio::findOrFail($id);

        DB::beginTransaction();

        try {
            // Reactivar edificio (mantiene los estados individuales previos de pisos y aulas)
            $edificio->activo = 1;
            $edificio->save();

            DB::commit();
            return redirect()->route('edificios')->with('success', 'Edificio reactivado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al reactivar edificio: ' . $e->getMessage());

            return redirect()->route('edificios')->with('failure', 'Error al reactivar el edificio.');
        }
    }

    /**
     * Valida la información del formulario de edificios (store y update).
     */
    private function validarEdificio(Request $request, $id = null)
    {
        $existente = Edificio::where('nombre', $request->nombre)
            ->when($id, fn($q) => $q->where('id_edificio', '!=', $id))
            ->first();

        if ($existente) {
            $mensaje = $existente->activo == 0
                ? 'Ya existe un edificio desactivado con el nombre "' . $request->nombre . '". Puedes reactivarlo desde la lista de edificios desactivados.'
                : 'Ya existe un edificio con ese nombre. Elige uno diferente.';

            throw \Illuminate\Validation\ValidationException::withMessages([
                'nombre' => $mensaje,
            ]);
        }

        $request->validate(
            [
                'nombre'         => 'required|string|max:100',
                'cantidad_pisos' => 'required|integer|min:1|max:20',
                'pisos'          => 'required|array',
            ],
            [
                'nombre.required'         => 'El nombre del edificio es obligatorio.',
                'nombre.max'              => 'El nombre no puede superar los 100 caracteres.',
                'cantidad_pisos.required' => 'Indica el número de pisos.',
                'cantidad_pisos.min'      => 'Debe haber al menos 1 piso.',
                'cantidad_pisos.max'      => 'No se pueden registrar más de 20 pisos.',
                'pisos.required'          => 'Debes registrar al menos un piso.',
            ]
        );

        $pisosData = $request->pisos;
        $lugaresActivos = Lugar::where('activo', 1)->pluck('id_lugar')->toArray();
        $duplicadosPorPiso = [];

        for ($i = 0; $i < (int)$request->cantidad_pisos; $i++) {
            $pisoData = $pisosData[$i] ?? [];
            $pisoActivo = isset($pisoData['activo']) ? (int)$pisoData['activo'] === 1 : true;
            $aulas = $pisoData['aulas'] ?? [];
            $nombreP = ($i === 0) ? 'Planta baja' : "Piso $i";

            // Si el piso está activo, debe tener al menos un aula activa
            if ($pisoActivo) {
                $aulasValidas = array_filter($aulas, fn($a) => !empty(trim($a['nombre'] ?? '')));
                if (empty($aulasValidas)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'pisos' => "El piso $nombreP está activo y debe tener al menos un aula registrada.",
                    ]);
                }
            }

            // Validar aulas activas enviadas para ESTE piso (unicidad por piso)
            $nombresAulasPiso = [];

            if (!empty($aulas)) {
                foreach ($aulas as $aulaData) {
                    $nombreAula = trim($aulaData['nombre'] ?? '');
                    if (empty($nombreAula)) {
                        continue;
                    }

                    if (mb_strlen($nombreAula) > 100) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'pisos' => "El nombre del aula '$nombreAula' supera los 100 caracteres.",
                        ]);
                    }

                    // Evitar nombres de aulas duplicados en el MISMO piso
                    $nombreNorm = mb_strtolower($nombreAula);
                    if (in_array($nombreNorm, $nombresAulasPiso)) {
                        $duplicadosPorPiso[] = [
                            'piso'   => $nombreP,
                            'nombre' => $nombreAula,
                        ];
                    } else {
                        $nombresAulasPiso[] = $nombreNorm;
                    }

                    // Validar tipo de lugar activo
                    $idLugar = $aulaData['id_lugar'] ?? null;
                    if (!$idLugar || !in_array((int)$idLugar, $lugaresActivos)) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'pisos' => "El tipo de lugar seleccionado para el aula '$nombreAula' no es válido o está desactivado.",
                        ]);
                    }
                }
            }
        }

        if (!empty($duplicadosPorPiso)) {
            $listaDuplicados = collect($duplicadosPorPiso)
                ->unique(fn($d) => $d['piso'] . '|' . mb_strtolower($d['nombre']))
                ->map(fn($d) => "• <strong>{$d['piso']}</strong>: &quot;" . htmlspecialchars($d['nombre'], ENT_QUOTES, 'UTF-8') . "&quot;")
                ->join("<br>");

            throw \Illuminate\Validation\ValidationException::withMessages([
                'pisos_duplicados' => $listaDuplicados,
            ]);
        }
    }

    /**
     * API: Devuelve pisos activos de un edificio (JSON).
     */
    public function getPisos($id)
    {
        $pisos = Piso::where('id_edificio', $id)
            ->where('activo', 1)
            ->orderBy('piso', 'asc')
            ->get()
            ->map(function ($piso) {
                return [
                    'id_piso' => $piso->id_piso,
                    'piso'    => $piso->piso,
                    'nombre'  => $piso->nombre_display,
                ];
            });

        return response()->json($pisos);
    }

    /**
     * API: Devuelve aulas activas de un piso (JSON).
     */
    public function getAulas($id_piso)
    {
        $aulas = Aula::where('id_piso', $id_piso)
            ->where('activo', 1)
            ->with('lugar')
            ->get()
            ->map(function ($aula) {
                return [
                    'id_aula'  => $aula->id_aula,
                    'nombre'   => $aula->nombre,
                    'id_lugar' => $aula->id_lugar,
                    'lugar'    => $aula->lugar->nombre ?? '',
                ];
            });

        return response()->json($aulas);
    }

    /**
     * Elimina o desactiva un aula según si está en uso.
     */
    private function eliminarODesactivarAula(Aula $aula)
    {
        $enUso = Ticket::where('id_aula', $aula->id_aula)->exists();

        if ($enUso) {
            $aula->activo = 0;
            $aula->save();
        } else {
            $aula->delete();
        }
    }
}
