<?php

namespace Modules\LSCEFA\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Modules\LSCEFA\Models\Process;
use Modules\LSCEFA\Models\Service;
use Modules\LSCEFA\Models\ServiceProcessDetail;
use Modules\LSCEFA\Models\ConductivityAnalysis;
use Illuminate\Routing\Controller;

class ConductivityAnalysisController extends Controller
{
    /**
     * Display a listing of pending conductivity analyses
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            Log::info('User accessing ConductivityAnalysisController@index:', [
                'user_id' => Auth::id(),
                'user_role' => Auth::user()->role ?? 'N/A',
            ]);

            // Obtener procesos con análisis de conductividad pendientes
            $processes = \Modules\LSCEFA\Models\Process::where('status', 'pending')
                ->whereHas('serviceProcessDetails', function($query) {
                    $query->where('status', 'pending')
                          ->whereHas('service', function($q) {
                              $q->whereRaw('LOWER(descripcion) LIKE ?', ['%conductividad%']);
                          });
                })
                ->with(['serviceProcessDetails' => function($query) {
                    $query->where('status', 'pending')
                          ->whereHas('service', function($q) {
                              $q->whereRaw('LOWER(descripcion) LIKE ?', ['%conductividad%']);
                          })
                          ->with('service');
                }])
                ->get();

            Log::info('Procesos con análisis de conductividad pendientes:', [
                'count' => $processes->count()
            ]);

            return view('lscefa::conductivity_analyses.index', [
                'processes' => $processes
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in ConductivityAnalysisController@index: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('lscefa.technical.analyses.index')
                           ->with('error', 'Error al cargar los análisis de conductividad: ' . $e->getMessage());
        }
    }

    public function batchConductivityAnalysis(Request $request)
    {
        $request->validate([
            'analyses' => 'required|array|max:20',
            'analyses.*' => 'exists:service_process_details,id',
        ]);
        session(['batch_conductivity_analysis_ids' => $request->input('analyses')]);
        return redirect()->route('lscefa.conductivity_analysis.process_all');
    }

    public function processAll()
    {
        $selectedIds = session('batch_conductivity_analysis_ids', []);
        if (!empty($selectedIds)) {
            $analyses = ServiceProcessDetail::with(['process', 'service', 'conductivityAnalysis'])
                ->whereIn('id', $selectedIds)
                ->get();
            $processes = $analyses->groupBy('process_id')->map(function($items, $processId) {
                $process = Process::where('process_id', $processId)
                    ->with(['serviceProcessDetails' => function($query) use ($items) {
                        $query->whereIn('id', $items->pluck('id')->toArray());
                    }])
                    ->first();
                return $process;
            })->filter();
        } else {
            $processes = Process::where('status', 'pending')
                ->with(['serviceProcessDetails' => function ($query) {
                    $query->where('status', 'pending')
                        ->whereHas('service', function ($serviceQuery) {
                            $serviceQuery->where('descripcion', 'like', '%conductividad%');
                        })
                        ->with(['service', 'process', 'conductivityAnalysis']);
                }])
                ->get()
                ->filter(function ($process) {
                    return $process->serviceProcessDetails->isNotEmpty();
                });
        }
        if ($processes->isEmpty()) {
            return redirect()->route('lscefa.conductivity_analysis.index')
                ->with('error', 'No hay análisis de conductividad pendientes para procesar.');
        }
        // Preparar los datos para la vista de procesamiento por lotes
        $pendingAnalyses = collect();
        $pendingItems = [];
        foreach ($processes as $process) {
            $analyses = $process->serviceProcessDetails;
            foreach ($analyses as $analysis) {
                $pendingAnalyses->push($analysis);
                $conductivityAnalysis = $analysis->conductivityAnalysis;
                if ($conductivityAnalysis && isset($conductivityAnalysis->items_ensayo)) {
                    foreach ($conductivityAnalysis->items_ensayo as $index => $item) {
                        $isPending = false;
                        // Considera pendiente si valor_leido o lectura_uscm están vacíos
                        if ((isset($item['valor_leido']) && $item['valor_leido'] === '') || !isset($item['valor_leido'])) {
                            $isPending = true;
                        }
                        if ((isset($item['lectura_uscm']) && $item['lectura_uscm'] === '') || !isset($item['lectura_uscm'])) {
                            $isPending = true;
                        }
                        if ($isPending) {
                            $pendingItems[] = array_merge($item, [
                                'analysis_id' => $analysis->id,
                                'codigo_item' => $process->item_code ?? null
                            ]);
                        }
                    }
                } else {
                    $pendingItems[] = [
                        'identificacion' => 'Muestra ' . (count($pendingItems) + 1),
                        'peso' => '',
                        'volumen_agua' => '',
                        'temperatura' => '',
                        'valor_leido' => '',
                        'valor_leido_dsm' => '',
                        'observaciones' => '',
                        'analysis_id' => $analysis->id,
                        'codigo_item' => $process->item_code ?? null,
                    ];
                }
            }
        }
        return view('lscefa::conductivity_analyses.process', [
            'pendingAnalyses' => $pendingAnalyses,
            'pendingItems' => $pendingItems,
            'user' => Auth::user(),
            'codigo_item' => isset($processes[0]) ? $processes[0]->item_code : null,
        ]);
    }

    public function show($processId, $serviceId)
    {
        $process = Process::where('process_id', $processId)->firstOrFail();
        $service = Service::findOrFail($serviceId);
        $analysis = ServiceProcessDetail::where('process_id', $processId)
            ->where('service_id', $serviceId)
            ->firstOrFail();
        $conductivityAnalysis = ConductivityAnalysis::where('analysis_id', $analysis->id)->first();
        $pendingItems = [];
        // La vista espera $pendingAnalyses para construir inputs ocultos y filtrar filas por analysis_id
        $pendingAnalyses = collect([$analysis]);

        $consecutivoNo = null;
        $nombreMetodo = null;
        if ($conductivityAnalysis) {
            if (!empty($conductivityAnalysis->consecutivo_no)) {
                // Traer todos los análisis de conductividad con el mismo consecutivo
                $siblings = ConductivityAnalysis::where('consecutivo_no', $conductivityAnalysis->consecutivo_no)->get();
                $consecutivoNo = $conductivityAnalysis->consecutivo_no;
                $nombreMetodo = $conductivityAnalysis->nombre_metodo ?? null;
                $siblingAnalysisIds = $siblings->pluck('analysis_id')->unique()->values();
                if ($siblingAnalysisIds->isNotEmpty()) {
                    $pendingAnalyses = ServiceProcessDetail::with(['process','service','conductivityAnalysis'])
                        ->whereIn('id', $siblingAnalysisIds)
                        ->get();
                }
                // Unir todos los items_ensayo de los hermanos y asegurar analysis_id y codigo_item
                foreach ($siblings as $sib) {
                    $spd = $pendingAnalyses->firstWhere('id', $sib->analysis_id);
                    $itemCode = $spd && $spd->process ? $spd->process->item_code : ($process->item_code ?? null);
                    if ($nombreMetodo === null && !empty($sib->nombre_metodo)) {
                        $nombreMetodo = $sib->nombre_metodo;
                    }
                    if (isset($sib->items_ensayo) && is_array($sib->items_ensayo)) {
                        foreach ($sib->items_ensayo as $item) {
                            $pendingItems[] = array_merge($item, [
                                'analysis_id' => $sib->analysis_id,
                                'codigo_item' => $itemCode,
                            ]);
                        }
                    } else {
                        // Si no tiene items, agregar una fila por defecto para ese análisis
                        $pendingItems[] = [
                            'identificacion' => 'Muestra',
                            'peso' => '',
                            'volumen_agua' => '',
                            'temperatura' => '',
                            'valor_leido' => '',
                            'valor_leido_dsm' => '',
                            'observaciones' => '',
                            'analysis_id' => $sib->analysis_id,
                            'codigo_item' => $itemCode,
                        ];
                    }
                }
            } else if (isset($conductivityAnalysis->items_ensayo) && is_array($conductivityAnalysis->items_ensayo)) {
                // Sin consecutivo: usar los items del análisis actual
                $consecutivoNo = $conductivityAnalysis->consecutivo_no ?? null;
                $nombreMetodo = $conductivityAnalysis->nombre_metodo ?? null;
                foreach ($conductivityAnalysis->items_ensayo as $item) {
                    $pendingItems[] = array_merge($item, [
                        'analysis_id' => $analysis->id,
                        'codigo_item' => $process->item_code ?? null,
                    ]);
                }
            }
        }
        // Si no hay items construidos, agregar uno por defecto del análisis actual
        if (empty($pendingItems)) {
            $pendingItems[] = [
                'identificacion' => 'Muestra',
                'peso' => '',
                'volumen_agua' => '',
                'temperatura' => '',
                'valor_leido' => '',
                'valor_leido_dsm' => '',
                'observaciones' => '',
                'analysis_id' => $analysis->id,
                'codigo_item' => $process->item_code ?? null,
            ];
        }
        return view('lscefa::conductivity_analyses.process', [
            'process' => $process,
            'service' => $service,
            'analysis' => $analysis,
            'conductivityAnalysis' => $conductivityAnalysis,
            'pendingAnalyses' => $pendingAnalyses,
            'pendingItems' => $pendingItems,
            'user' => Auth::user(),
            'consecutivo_no' => $consecutivoNo,
            'nombre_metodo' => $nombreMetodo,
        ]);
    }

    public function storeConductivityAnalysis(Request $request)
    {
        $validated = $request->validate([
            'consecutivo_no' => 'required|string|max:255',
            'fecha_analisis' => 'required|date',
            // Ítems de ensayo
            'items_ensayo' => 'required|array|min:1',
            'items_ensayo.*.identificacion' => 'required|string',
            'items_ensayo.*.peso' => 'required|numeric',
            'items_ensayo.*.volumen_agua' => 'required|numeric',
            'items_ensayo.*.temperatura' => 'required|numeric',
            'items_ensayo.*.valor_leido' => 'required|numeric',
            'items_ensayo.*.valor_leido_dsm' => 'required|numeric',
            // Blanco del proceso
            'blanco_valor_leido' => 'required|numeric',
            // Precisión (duplicados)
            'duplicado_a_valor_leido' => 'required|numeric',
            'duplicado_b_valor_leido' => 'required|numeric',
            // Veracidad
            'veracidad' => 'required|array|min:2',
            'veracidad.0.valor_esperado' => 'required|numeric',
            'veracidad.0.valor_leido' => 'required|numeric',
            'veracidad.1.valor_esperado' => 'required|numeric',
            'veracidad.1.valor_leido' => 'required|numeric',
            // Análisis asociados
            'analyses' => 'required|array|min:1',
            'analyses.*.analysis_id' => 'required|exists:service_process_details,id',
        ]);

        try {
            DB::beginTransaction();
            
            // Calcular aceptabilidad de blanco
            $blancoValor = floatval($request->blanco_valor_leido ?? 0);
            $blancoAceptable = ($blancoValor <= 0.1) ? 'Aceptable' : 'No aceptable';
            
            // Calcular precisión analítica (convertir µS/cm a mS/m dividiendo por 10)
            $duplicadoA = floatval($request->duplicado_a_valor_leido ?? 0);
            $duplicadoB = floatval($request->duplicado_b_valor_leido ?? 0);
            $dupAPeso = $request->duplicado_peso ?? null; // A
            $dupAVol = $request->duplicado_volumen_agua ?? null; // A
            $dupATemp = $request->duplicado_temperatura ?? null; // A
            $dupBPeso = $request->duplicado_b_peso ?? null; // B
            $dupBVol = $request->duplicado_b_volumen_agua ?? null; // B
            $dupBTemp = $request->duplicado_b_temperatura ?? null; // B
            $dupObs = $request->duplicado_observaciones ?? null;
            // Mapear identificaciones si solo viene un campo común
            $dupIdent = $request->duplicado_identificacion ?? null;
            $dupAIdent = $request->duplicado_a_identificacion ?? $dupIdent;
            $dupBIdent = $request->duplicado_b_identificacion ?? $dupIdent;
            // Convertir a mS/m para aplicar los límites definidos en la metodología
            $a_msm = $duplicadoA / 10; // µS/cm -> mS/m
            $b_msm = $duplicadoB / 10;
            $promedio = ($a_msm + $b_msm) / 2; // mS/m
            $diferencia = abs($a_msm - $b_msm); // mS/m
            
            // Calcular si la precisión es aceptable (en mS/m):
            // <= 50 mS/m: límite 5 mS/m
            // > 50 y <= 200 mS/m: límite 20 mS/m
            // > 200 mS/m: límite 10% del promedio
            if ($promedio <= 50) {
                $precisionAceptable = ($diferencia <= 5) ? 'Aceptable' : 'No aceptable';
            } elseif ($promedio <= 200) {
                $precisionAceptable = ($diferencia <= 20) ? 'Aceptable' : 'No aceptable';
            } else {
                $porcentajeDiferencia = $promedio > 0 ? ($diferencia / $promedio) * 100 : 0;
                $precisionAceptable = ($porcentajeDiferencia <= 10) ? 'Aceptable' : 'No aceptable';
            }
            
            // Preparar controles analíticos
            $controlesAnaliticos = [
                [
                    'tipo' => 'blanco',
                    'identificacion' => $request->blanco_identificacion ?? null,
                    'valor_leido' => $blancoValor,
                    'aceptable' => $blancoAceptable,
                    'observaciones' => $request->blanco_observaciones ?? null,
                ]
            ];

            // Preparar precisión analítica
            $precisionAnalitica = [
                'duplicado_a' => [
                    'identificacion' => $dupAIdent,
                    'peso' => $dupAPeso,
                    'volumen_agua' => $dupAVol,
                    'temperatura' => $dupATemp,
                    'valor_leido' => $duplicadoA,
                ],
                'duplicado_b' => [
                    'identificacion' => $dupBIdent,
                    'peso' => $dupBPeso,
                    'volumen_agua' => $dupBVol,
                    'temperatura' => $dupBTemp,
                    'valor_leido' => $duplicadoB,
                ],
                'promedio' => $promedio,
                'diferencia' => $diferencia,
                'aceptable' => $precisionAceptable,
                'observaciones' => $dupObs,
            ];

            // Preparar veracidad (controles de calidad)
            $veracidadInput = $request->input('veracidad', []);
            \Log::info('Conductivity store: veracidad payload received', [
                'veracidad' => $veracidadInput
            ]);
            $veracidadAnalitica = [];
            foreach ([0,1] as $idx) {
                $row = $veracidadInput[$idx] ?? [];
                $esperado = isset($row['valor_esperado']) ? (float)$row['valor_esperado'] : null;
                $leido = isset($row['valor_leido']) ? (float)$row['valor_leido'] : null;
                $recuperacion = (is_numeric($esperado) && $esperado != 0 && is_numeric($leido))
                    ? ($leido / $esperado) * 100
                    : null;
                $aceptable = null;
                if (!is_null($recuperacion)) {
                    $aceptable = ($recuperacion >= 70 && $recuperacion <= 130) ? 'Aceptable' : 'No aceptable';
                }
                $veracidadAnalitica[$idx] = [
                    'identificacion' => $row['identificacion'] ?? null,
                    'peso' => $row['peso'] ?? null,
                    'volumen_agua' => $row['volumen_agua'] ?? null,
                    'temperatura' => $row['temperatura'] ?? null,
                    'valor_esperado' => $esperado,
                    'valor_leido' => $leido,
                    'recuperacion' => $recuperacion,
                    'aceptable' => $aceptable,
                    'observaciones' => $row['observaciones'] ?? null,
                ];
            }
            \Log::info('Conductivity store: computed veracidad_analitica', [
                'veracidad_analitica' => $veracidadAnalitica
            ]);

            // Validación QC: bloquear si algún control o precisión/veracidad es No aceptable
            $hayVeracidadNoAceptable = false;
            foreach ($veracidadAnalitica as $va) {
                if (isset($va['aceptable']) && strtolower($va['aceptable']) === 'no aceptable') {
                    $hayVeracidadNoAceptable = true;
                    break;
                }
            }
            if (strtolower($blancoAceptable) === 'no aceptable' || strtolower($precisionAnalitica['aceptable']) === 'no aceptable' || $hayVeracidadNoAceptable) {
                DB::rollBack();
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'No es posible enviar el análisis: existe al menos un control (blanco/veracidad) o la precisión marcada como "No aceptable".');
            }

            // Guardar análisis
            $itemsEnsayo = $request->items_ensayo;
            $analyses = $request->input('analyses', []);
            
            foreach ($analyses as $analysisData) {
                $analysisId = $analysisData['analysis_id'];
                $analysis = ServiceProcessDetail::findOrFail($analysisId);
                
                $conductivityAnalysis = ConductivityAnalysis::updateOrCreate(
                    ['analysis_id' => $analysisId],
                    [
                        'consecutivo_no' => $request->consecutivo_no,
                        'nombre_metodo' => $request->nombre_metodo ?? null,
                        'fecha_analisis' => $request->fecha_analisis,
                        'user_id' => Auth::id(),
                        // Guardar en las columnas correctas
                        'equipo_utilizado' => $request->equipo_utilizado ?? null,
                        'resolucion_instrumental' => $request->resolucion_instrumental ?? null,
                        'unidades_reporte' => $request->unidades_reporte ?? null,
                        'intervalo_metodo' => $request->intervalo_metodo ?? null,
                        // Compatibilidad hacia atrás por si alguna vista consume los otros campos
                        'codigo_equipo' => $request->equipo_utilizado ?? ($request->codigo_equipo ?? null),
                        'serial_conductimetro' => $request->resolucion_instrumental ?? ($request->serial_conductimetro ?? null),
                        'serial_sonda_temperatura' => $request->unidades_reporte ?? ($request->serial_sonda_temperatura ?? null),
                        'controles_analiticos' => $controlesAnaliticos,
                        'precision_analitica' => $precisionAnalitica,
                        'veracidad_analitica' => $veracidadAnalitica,
                        'items_ensayo' => $itemsEnsayo,
                        // Mapear observaciones del analista
                        'observaciones' => $request->observaciones_analista ?? $request->observaciones ?? null,
                        'review_status' => 'pending',
                    ]
                );
                
                $analysis->status = 'completed';
                $analysis->save();
            }
            
            DB::commit();
            return redirect()->route('lscefa.technical.analyses.index')
                ->with('success', 'Análisis de conductividad guardados exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in ConductivityAnalysisController@storeConductivityAnalysis: ' . $e->getMessage(), [
                'stack_trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            // En caso de error, redirigir de vuelta con los datos de entrada
            return redirect()->back()
                ->withInput()
                ->with('error', 'Ocurrió un error al guardar el análisis: ' . $e->getMessage());
        }
    }
} 