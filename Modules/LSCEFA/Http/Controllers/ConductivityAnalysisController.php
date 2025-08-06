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
        if ($conductivityAnalysis && isset($conductivityAnalysis->items_ensayo)) {
            foreach ($conductivityAnalysis->items_ensayo as $index => $item) {
                if (!isset($item['valor_leido']) || $item['valor_leido'] === '') {
                    $pendingItems[$index] = $item;
                }
            }
        }
        return view('lscefa::conductivity_analyses.process', [
            'process' => $process,
            'service' => $service,
            'analysis' => $analysis,
            'conductivityAnalysis' => $conductivityAnalysis,
            'pendingItems' => $pendingItems,
            'user' => Auth::user(),
        ]);
    }

    public function storeConductivityAnalysis(Request $request)
    {
        $validated = $request->validate([
            'consecutivo_no' => 'required|string|max:255',
            'fecha_analisis' => 'required|date',
            'items_ensayo' => 'required|array|min:1',
            'items_ensayo.*.identificacion' => 'required|string',
            'items_ensayo.*.peso' => 'required|numeric',
            'items_ensayo.*.volumen_agua' => 'required|numeric',
            'items_ensayo.*.temperatura' => 'required|numeric',
            'items_ensayo.*.valor_leido' => 'required|numeric',
            'items_ensayo.*.valor_leido_dsm' => 'required|numeric',
            'analyses' => 'required|array|min:1',
            'analyses.*.analysis_id' => 'required|exists:service_process_details,id',
        ]);

        try {
            DB::beginTransaction();
            
            // Calcular aceptabilidad de blanco
            $blancoValor = floatval($request->blanco_valor_leido ?? 0);
            $blancoAceptable = ($blancoValor <= 0.1) ? 'Aceptable' : 'No aceptable';
            
            // Calcular precisión analítica
            $duplicadoA = floatval($request->duplicado_a_valor_leido ?? 0);
            $duplicadoB = floatval($request->duplicado_b_valor_leido ?? 0);
            $promedio = ($duplicadoA + $duplicadoB) / 2;
            $diferencia = abs($duplicadoA - $duplicadoB);
            
            // Calcular si la precisión es aceptable
            if ($promedio <= 50000) {
                $precisionAceptable = ($diferencia <= 5000) ? 'Aceptable' : 'No aceptable';
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
                    'identificacion' => $request->duplicado_a_identificacion ?? null,
                    'valor_leido' => $duplicadoA,
                ],
                'duplicado_b' => [
                    'identificacion' => $request->duplicado_b_identificacion ?? null,
                    'valor_leido' => $duplicadoB,
                ],
                'promedio' => $promedio,
                'diferencia' => $diferencia,
                'aceptable' => $precisionAceptable
            ];

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
                        'fecha_analisis' => $request->fecha_analisis,
                        'user_id' => Auth::id(),
                        'codigo_equipo' => $request->equipo_utilizado ?? 'N/A',
                        'serial_conductimetro' => $request->resolucion_instrumental ?? 'N/A',
                        'serial_sonda_temperatura' => $request->unidades_reporte ?? 'N/A',
                        'controles_analiticos' => $controlesAnaliticos,
                        'precision_analitica' => $precisionAnalitica,
                        'items_ensayo' => $itemsEnsayo,
                        'observaciones' => $request->observaciones,
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