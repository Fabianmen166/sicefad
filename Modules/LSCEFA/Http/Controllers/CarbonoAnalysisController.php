<?php

namespace Modules\LSCEFA\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\LSCEFA\Entities\AnalyticalControl;
use Modules\LSCEFA\Models\Process;
use Modules\LSCEFA\Models\Service;
use Modules\LSCEFA\Entities\CarbonoAnalysis;

class CarbonoAnalysisController extends Controller
{
  public function index()
{
    try {
        Log::info('Usuario accede a index de análisis de Carbono', [
            'user_id' => Auth::id(),
            'role' => Auth::user()->role ?? 'N/A'
        ]);

        // Obtener procesos pendientes con servicios de carbono
        $processes = Process::where('status', 'pending')
            ->with(['services' => function($query) {
                $query->where('descripcion', 'like', '%Carbono%')
                      ->wherePivot('status', 'pending');
            }])
            ->whereHas('services', function($query) {
                $query->where('descripcion', 'like', '%Carbono%');
            })
            ->get();

        // Verificar si hay procesos
        $hasProcesses = $processes->isNotEmpty();

        return view('lscefa::analyses.carbon.index', [
            'processes' => $processes,
            'hasProcesses' => $hasProcesses
        ]);

    } catch (\Exception $e) {
        Log::error('Error al cargar el índice de análisis de Carbono: ' . $e->getMessage());
        return view('lscefa::analyses.carbon.index', [
            'processes' => collect(), // Colección vacía
            'hasProcesses' => false,
            'error' => 'Ocurrió un error al cargar los procesos. Por favor intente nuevamente.'
        ]);
    }
}

    public function carbonoAnalysis($processId, $serviceId)
    {
        try {
            $process = Process::with(['quote.customer', 'services'])
                ->findOrFail($processId);

            $service = Service::findOrFail($serviceId);

            return view('lscefa::analyses.carbon.process', [
                'process' => $process,
                'service' => $service,
                'serviceId' => $serviceId,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al cargar el formulario de análisis: ' . $e->getMessage());
            return back()->with('error', 'No se pudo cargar el formulario.');
        }
    }

    public function storeCarbonoAnalysis(Request $request, $processId, $serviceId)
    {
        try {
            // Normalizar campos numéricos (convertir comas a puntos)
            $numericFields = [
                'peso_muestra',
               
                'volumen_sulfato_blanco',
                'volumen_sulfato_muestra',
                'volumen_dicromato',
                'molaridad_sulfato',
                'co_total_porcentaje',
                'cot_porcentaje',
                'mo_porcentaje',
                'valor_cot_leido',
                'valor_leido',
                'porcentaje_humedad',
            ];

            foreach ($numericFields as $field) {
                if ($request->has($field)) {
                    $value = str_replace(',', '.', $request->input($field));
                    $request->merge([$field => $value]);
                }
            }

            // Validación completa de todos los campos
            $validated = $request->validate([
                'consecutivo_no' => 'required|string|max:255',
                'fecha_analisis' => 'required|date',
                'nombre_metodo' => 'required|string|max:255',
                'equipo_utilizado' => 'required|string|max:255',
                'intervalo_metodo' => 'required|string|max:255',
                'unidades_reporte_equipo' => 'required|string|max:255',
                'resolucion_instrumental' => 'nullable|string|max:255',
                'codigo_interno' => 'required|string|max:255',
                'peso_muestra' => 'required|numeric',
                'volumen_sulfato_blanco' => 'required|numeric',
                'volumen_sulfato_muestra' => 'required|numeric',
                'volumen_dicromato' => 'required|numeric',
                'molaridad_sulfato' => 'nullable|numeric',
                'porcentaje_co_total' => 'nullable|numeric',
                'porcentaje_cot' => 'nullable|numeric',

                'porcentaje_mo' => 'nullable|numeric',
                'valor_cot_leido' => 'nullable|numeric',
                'valor_leido' => 'nullable|numeric',
                'fortificado' => 'nullable',
                'cot_muestra' => 'nullable|numeric',
                'error_analitico' => 'nullable|numeric',
                'porcentaje_humedad' => 'nullable|numeric',
                'observaciones' => 'nullable|string',
                
                // Validación para controles analíticos
                'controles_analiticos' => 'nullable|array',
                'controles_analiticos.identificacion_mf' => 'nullable|string|max:255',
                'controles_analiticos.identificacion_mr' => 'nullable|string|max:255',
                'controles_analiticos.identificacion_dm' => 'nullable|string|max:255',
                'controles_analiticos.identificacion_bm' => 'nullable|string|max:255',
                'controles_analiticos.valor_referencia' => 'nullable|numeric',
                'controles_analiticos.valor_obtenido' => 'nullable|numeric',
                'controles_analiticos.valor_leido' => 'nullable|numeric',
                'controles_analiticos.blanco_metodo' => 'nullable|numeric',
                'controles_analiticos.recuperacion' => 'nullable|numeric',
                'controles_analiticos.limite_cuantificacion_metodo' => 'nullable|string|max:255',
                'controles_analiticos.replica_1' => 'nullable|numeric',
                'controles_analiticos.replica_2' => 'nullable|numeric',
                'controles_analiticos.dpr' => 'nullable|numeric',
                'controles_analiticos.aceptable' => 'nullable|string|in:aceptable,no_aceptable',
                'controles_analiticos.observaciones' => 'nullable|string',
            ], [
                'consecutivo_no.required' => 'El consecutivo es obligatorio.',
                'fecha_analisis.required' => 'La fecha del análisis es obligatoria.',
                'nombre_metodo.required' => 'El nombre del método es obligatorio.',
                'equipo_utilizado.required' => 'El equipo utilizado es obligatorio.',
                'intervalo_metodo.required' => 'El intervalo del método es obligatorio.',
                'peso_muestra.required' => 'El peso de la muestra es obligatorio.',
                'volumen_sulfato_blanco.required' => 'El volumen de sulfato blanco es obligatorio.',
                'volumen_sulfato_muestra.required' => 'El volumen de sulfato muestra es obligatorio.',
                'volumen_dicromato.required' => 'El volumen de dicromato es obligatorio.',
            ]);

            // Iniciar transacción
            DB::beginTransaction();

            try {
                // Obtener el proceso y servicio
                $process = Process::findOrFail($processId);
                $service = Service::findOrFail($serviceId);

                // Preparar datos para carbono_analysis
                $carbonoData = [
                    'process_id' => $processId,
                    'service_id' => $serviceId,
                    'user_id' => auth()->id(),
                    'consecutivo_no' => $validated['consecutivo_no'],
                    'fecha_analisis' => $validated['fecha_analisis'],
                    'nombre_metodo' => $validated['nombre_metodo'],
                    'equipo_utilizado' => $validated['equipo_utilizado'],
                    'intervalo_metodo' => $validated['intervalo_metodo'],
                    'unidades_reporte_equipo' => $validated['unidades_reporte_equipo'],
                    'resolucion_instrumental' => $validated['resolucion_instrumental'] ?? null,
                    'codigo_interno' => $validated['codigo_interno'],
                    'peso_muestra' => $validated['peso_muestra'],
                    'volumen_sulfato_blanco' => $validated['volumen_sulfato_blanco'],
                    'volumen_sulfato_muestra' => $validated['volumen_sulfato_muestra'],
                    'volumen_dicromato' => $validated['volumen_dicromato'],
                    'molaridad_sulfato' => $validated['molaridad_sulfato'] ?? null,
                    'porcentaje_cot_total' => $validated['porcentaje_cot_total'] ?? null,
                    'porcentaje_cot' => $validated['porcentaje_cot'] ?? null,
                    'porcentaje_mo' => $validated['porcentaje_mo'] ?? null,
                    'fortificado' => $validated['fortificado'] ?? false,
                    'cot_muestra' => $validated['cot_muestra'] ?? null,
                    'porcentaje_humedad' => $validated['porcentaje_humedad'] ?? null,
                    'error_analitico' => $validated['error_analitico'] ?? null,
                    'valor_cot_leido' => $validated['valor_cot_leido'] ?? null,
                    'valor_leido' => $validated['valor_leido'] ?? null,
                    'observaciones' => $validated['observaciones'] ?? null,
                ];

                // Crear el registro en carbono_analysis
                $carbonoAnalysis = CarbonoAnalysis::create($carbonoData);

                // Crear controles analíticos si existen
                if (!empty($validated['controles_analiticos'])) {
                    $analyticalData = [
                        'process_id' => $processId,
                        'service_id' => $serviceId,
                        'identificacion_mf' => $validated['controles_analiticos']['identificacion_mf'] ?? null,
                        'identificacion_mr' => $validated['controles_analiticos']['identificacion_mr'] ?? null,
                        'identificacion_dm' => $validated['controles_analiticos']['identificacion_dm'] ?? null,
                        'identificacion_bm' => $validated['controles_analiticos']['identificacion_bm'] ?? null,
                        'valor_referencia' => $validated['controles_analiticos']['valor_referencia'] ?? null,
                        'valor_obtenido' => $validated['controles_analiticos']['valor_obtenido'] ?? null,
                        'valor_leido' => $validated['controles_analiticos']['valor_leido'] ?? null,
                        'blanco_metodo' => $validated['controles_analiticos']['blanco_metodo'] ?? null,
                        'recuperacion' => $validated['controles_analiticos']['recuperacion'] ?? null,
                        'limite_cuantificacion_metodo' => $validated['controles_analiticos']['limite_cuantificacion_metodo'] ?? null,
                        'replica_1' => $validated['controles_analiticos']['replica_1'] ?? null,
                        'replica_2' => $validated['controles_analiticos']['replica_2'] ?? null,
                        'dpr' => $validated['controles_analiticos']['dpr'] ?? null,
                        'estado' => isset($validated['controles_analiticos']['aceptable']) ? 
                                      ($validated['controles_analiticos']['aceptable'] === 'aceptable' ? 'Aceptable' : 'No Aceptable') : null,
                        'observaciones' => $validated['controles_analiticos']['observaciones'] ?? null,
                    ];

                    AnalyticalControl::create($analyticalData);
                }

                // Actualizar el estado del servicio en el proceso
                $process->services()->updateExistingPivot($serviceId, ['status' => 'completed']);

                // Verificar si todos los servicios están completados
                $pendingServices = $process->services()->wherePivot('status', 'pending')->count();
                if ($pendingServices === 0) {
                    $process->status = 'completed';
                    $process->save();
                }

                // Confirmar la transacción
                DB::commit();

                return redirect()->route('carbono.index')
                    ->with('success', 'Análisis de carbono registrado correctamente.');

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error al guardar análisis de carbono (transacción): ' . $e->getMessage());
                throw $e;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::debug('Errores de validación:', $e->errors());
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Error al guardar análisis de carbono: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Hubo un error al guardar el análisis de carbono: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $carbonoAnalysis = CarbonoAnalysis::findOrFail($id);
        return view('lscefa::analyses.carbon.edit', compact('carbonoAnalysis'));
    }

    public function update(Request $request, $id)
    {
        $carbonoAnalysis = CarbonoAnalysis::findOrFail($id);
        $carbonoAnalysis->update($request->all());

        return redirect()->route('carbon_analysis.index')
            ->with('success', 'Análisis actualizado.');
    }

    public function review(Request $request, $id)
    {
        $carbonoAnalysis = CarbonoAnalysis::findOrFail($id);

        $carbonoAnalysis->update([
            'review_status' => $request->review_status,
            'reviewed_by' => Auth::id(),
            'reviewer_role' => Auth::user()->role,
            'review_date' => now(),
            'review_observations' => $request->review_observations,
        ]);

        return redirect()->route('carbon_analysis.index')
            ->with('success', 'Revisión registrada.');
    }

    public function destroy($id)
    {
        $carbonoAnalysis = CarbonoAnalysis::findOrFail($id);
        $carbonoAnalysis->delete();

        return back()->with('success', 'Análisis eliminado.');
    }
}