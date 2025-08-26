<?php

namespace Modules\LSCEFA\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Modules\LSCEFA\Models\Process;
use Modules\LSCEFA\Models\Service;
use Modules\LSCEFA\Models\ServiceProcessDetail;
use Modules\LSCEFA\Models\PhAnalysis;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

use Illuminate\Routing\Controller;

class PhAnalysisController extends Controller
{
    /**
     * Display a listing of pH analyses for technical staff, including pending and returned (not approved) analyses.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            Log::info('User accessing PhAnalysisController@index:', [
                'user_id' => Auth::id(),
                'user_role' => Auth::user()->role ?? 'N/A',
            ]);

            // 1. Obtener servicios que contienen 'ph' en la descripción
            $phServices = \Modules\LSCEFA\Models\Service::whereRaw('LOWER(descripcion) LIKE ?', ['%ph%'])
                ->get(['services_id', 'descripcion']);
                
            Log::info('Servicios con "ph" en la descripción:', [
                'count' => $phServices->count(),
                'services' => $phServices->toArray()
            ]);

            // 2. Obtener análisis de pH rechazados que además hayan sido devueltos por revisión
            $rejectedAnalyses = \Modules\LSCEFA\Models\ServiceProcessDetail::with(['process', 'service'])
                ->where('status', 'rejected')
                ->whereHas('service', function($q) {
                    $q->whereRaw('LOWER(descripcion) LIKE ?', ['%ph%']);
                })
                ->whereHas('phAnalysis', function($q) {
                    $q->where('review_status', 'returned');
                })
                ->get();

            Log::info('Análisis de pH rechazados:', [
                'count' => $rejectedAnalyses->count()
            ]);

            // 3. Obtener procesos con análisis de pH pendientes
            $processes = \Modules\LSCEFA\Models\Process::where('status', 'pending')
                ->whereHas('serviceProcessDetails', function($query) {
                    $query->where('status', 'pending')
                          ->whereHas('service', function($q) {
                              $q->whereRaw('LOWER(descripcion) LIKE ?', ['%ph%']);
                          });
                })
                ->with(['serviceProcessDetails' => function($query) {
                    $query->where('status', 'pending')
                          ->whereHas('service', function($q) {
                              $q->whereRaw('LOWER(descripcion) LIKE ?', ['%ph%']);
                          })
                          ->with('service');
                }])
                ->get();

            Log::info('Procesos con análisis de pH pendientes:', [
                'count' => $processes->count()
            ]);

            return view('lscefa::ph_analyses.index', [
                'processes' => $processes,
                'phAnalyses' => $rejectedAnalyses
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in PhAnalysisController@index: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('lscefa.technical.analyses.index')
                           ->with('error', 'Error al cargar la gestión de análisis de pH: ' . $e->getMessage());
        }
    }

    /**
     * Recibe los IDs seleccionados, los guarda en sesión y redirige a processAll
     */
    public function batchPhAnalysis(Request $request)
    {
        $request->validate([
            'analyses' => 'required|array|max:20',
            'analyses.*' => 'exists:service_process_details,id',
        ]);
        session(['batch_ph_analysis_ids' => $request->input('analyses')]);
        return redirect()->route('lscefa.ph_analysis.process_all');
    }

    /**
     * Show the form for processing all pending pH analyses across all processes.
     *
     * @return \Illuminate\View\View
     */
    public function processAll(Request $request)
    {
        try {
            // Si hay IDs en sesión, solo procesar esos análisis
            $selectedIds = session('batch_ph_analysis_ids', []);
            if (!empty($selectedIds)) {
                $analyses = \Modules\LSCEFA\Models\ServiceProcessDetail::with(['process', 'service', 'phAnalysis'])
                    ->whereIn('id', $selectedIds)
                    ->get();
                // Agrupar por proceso
                $processes = $analyses->groupBy('process_id')->map(function($items, $processId) {
                    $process = \Modules\LSCEFA\Models\Process::where('process_id', $processId)
                        ->with(['serviceProcessDetails' => function($query) use ($items) {
                            $query->whereIn('id', $items->pluck('id')->toArray());
                        }])
                        ->first();
                    return $process;
                })->filter();
            } else {
                // Lógica para todos los procesos con análisis de pH pendientes (case-insensitive match)
                $processes = Process::where('status', 'pending')
                    ->with([
                        'serviceProcessDetails' => function ($query) {
                            $query->where('status', 'pending')
                                ->whereHas('service', function($q) {
                                    $q->whereRaw('LOWER(descripcion) LIKE ?', ['%ph%']);
                                })
                                ->with(['service', 'process', 'phAnalysis']);
                        },
                    ])
                    ->get()
                    ->filter(function ($process) {
                        return $process->serviceProcessDetails->isNotEmpty();
                    });
            }
            if ($processes->isEmpty()) {
                return redirect()->route('lscefa.ph_analysis.index')
                                ->with('error', 'No hay análisis de pH pendientes para procesar.');
            }

            // Initialize an array to hold all pending items across all analyses
            $pendingItems = [];
            $pendingAnalyses = collect();
            foreach ($processes as $process) {
                $analyses = $process->serviceProcessDetails;
                foreach ($analyses as $analysis) {
                    $pendingAnalyses->push($analysis);
                    $phAnalysis = $analysis->phAnalysis;
                    if ($phAnalysis && isset($phAnalysis->items_ensayo)) {
                        foreach ($phAnalysis->items_ensayo as $index => $item) {
                            if (!isset($item['valor_leido']) || $item['valor_leido'] === '') {
                                $pendingItems[] = $item + ['analysis_id' => $analysis->id];
                            }
                        }
                    } else {
                        // Add default item if no PhAnalysis exists
                        $pendingItems[] = [
                            'identificacion' => 'Muestra ' . (count($pendingItems) + 1),
                            'peso' => '',
                            'volumen_agua' => '',
                            'temperatura' => '',
                            'valor_leido' => '',
                            'observaciones' => '',
                            'analysis_id' => $analysis->id,
                        ];
                    }
                }
            }

            // Nota: Se removió un bloque de debug que hacía referencia a $controles no definido en este método.

            Log::info('PhAnalysisController@processAll loaded data:', [
                'pending_analyses_count' => $pendingAnalyses->count(),
                'pending_items_count' => count($pendingItems),
            ]);

            // Determinar consecutivo_no para prellenar si existe en alguno de los análisis
            $consecutivoNo = null;
            foreach ($pendingAnalyses as $pa) {
                if (isset($pa->phAnalysis) && !empty($pa->phAnalysis->consecutivo_no)) {
                    $consecutivoNo = $pa->phAnalysis->consecutivo_no;
                    break;
                }
            }
            // Fallback: si no se encontró en los pendientes, buscar en cualquier análisis (cualquier estado) del/los procesos cargados
            if ($consecutivoNo === null) {
                try {
                    $processIds = collect($processes)->pluck('process_id')->filter()->unique()->values();
                    if ($processIds->isNotEmpty()) {
                        $relatedAnalysisIds = ServiceProcessDetail::whereIn('process_id', $processIds)
                            ->whereHas('service', function($q){
                                $q->whereRaw('LOWER(descripcion) LIKE ?', ['%ph%']);
                            })
                            ->pluck('id');
                        if ($relatedAnalysisIds->isNotEmpty()) {
                            $existing = PhAnalysis::whereIn('analysis_id', $relatedAnalysisIds)
                                ->whereNotNull('consecutivo_no')
                                ->where('consecutivo_no', '!=', '')
                                ->orderByDesc('id')
                                ->first();
                            if ($existing) {
                                $consecutivoNo = $existing->consecutivo_no;
                            }
                        }
                    }
                } catch (\Throwable $t) {
                    Log::warning('processAll fallback consecutivo lookup failed', ['message' => $t->getMessage()]);
                }
            }

            // Permitir prellenar por query param, igual que en otras pantallas (?consecutivo=XXXX)
            $queryConsecutivo = trim((string) $request->query('consecutivo', ''));
            if ($queryConsecutivo !== '') {
                $consecutivoNo = $queryConsecutivo;
            }

            Log::info('PhAnalysisController@processAll consecutivo prefill', [ 'consecutivo_no' => $consecutivoNo, 'from_query' => $queryConsecutivo !== '' ? 'yes' : 'no' ]);

            // Mantener old() para que, en caso de error al guardar, el formulario conserve los datos ingresados

            return view('lscefa::ph_analyses.process', [
                'pendingAnalyses' => $pendingAnalyses,
                'pendingItems' => $pendingItems,
                'user' => Auth::user(),
                'consecutivo_no' => $consecutivoNo,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in PhAnalysisController@processAll: ' . $e->getMessage(), [
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('lscefa.ph_analysis.index')
                           ->with('error', 'Error al cargar el formulario de análisis de pH: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for processing a specific pH analysis with pending items.
     *
     * @param string $processId
     * @param int $serviceId
     * @return \Illuminate\View\View
     */
    public function phAnalysis($processId, $serviceId, Request $request)
    {
        try {
            // Obtener el detalle del proceso de servicio que tenga un servicio con 'ph' en la descripción
            $serviceProcessDetail = ServiceProcessDetail::with(['process', 'service'])
                ->where('process_id', $processId)
                ->whereHas('service', function($q) use ($serviceId) {
                    $q->where('service_id', $serviceId)
                      ->whereRaw('LOWER(descripcion) LIKE ?', ['%ph%']);
                })
                ->firstOrFail();
            $phAnalysis = PhAnalysis::where('analysis_id', $serviceProcessDetail->id)->first();

            // Definir variables utilizadas por la vista
            $process = $serviceProcessDetail->process;
            $service = $serviceProcessDetail->service;
            $analysis = $serviceProcessDetail;

            // Construir ítems pendientes para la vista (cada ítem DEBE incluir analysis_id)
            $pendingItems = [];
            $pendingAnalyses = collect([$serviceProcessDetail]);

            // Determinar el consecutivo objetivo priorizando el query param
            $targetConsecutivo = null;
            $queryConsecutivoEarly = trim((string) $request->query('consecutivo', ''));
            if ($queryConsecutivoEarly !== '') {
                $targetConsecutivo = $queryConsecutivoEarly;
            } elseif ($phAnalysis && !empty($phAnalysis->consecutivo_no)) {
                $targetConsecutivo = $phAnalysis->consecutivo_no;
            }

            if (!empty($targetConsecutivo)) {
                // Cargar todos los análisis con el consecutivo indicado (de la URL o del registro actual)
                $siblings = PhAnalysis::where('consecutivo_no', $targetConsecutivo)->get();
                $siblingAnalysisIds = $siblings->pluck('analysis_id')->unique()->values();
                if ($siblingAnalysisIds->isNotEmpty()) {
                    $pendingAnalyses = ServiceProcessDetail::with(['process','service','phAnalysis'])
                        ->whereIn('id', $siblingAnalysisIds)
                        ->get();
                }
                foreach ($siblings as $sib) {
                    if (isset($sib->items_ensayo) && is_array($sib->items_ensayo)) {
                        foreach ($sib->items_ensayo as $item) {
                            $pendingItems[] = $item + ['analysis_id' => $sib->analysis_id];
                        }
                    }
                }
            } else if ($phAnalysis && isset($phAnalysis->items_ensayo) && is_array($phAnalysis->items_ensayo)) {
                // Sin consecutivo definido, usar solo los ítems del análisis actual
                foreach ($phAnalysis->items_ensayo as $item) {
                    $pendingItems[] = $item + ['analysis_id' => $serviceProcessDetail->id];
                }
            }

            // Si no hay registros existentes, preparar al menos una fila por defecto
            if (empty($pendingItems)) {
                $pendingItems[] = [
                    'identificacion' => $process->item_code ?? 'Muestra 1',
                    'peso' => '',
                    'volumen_agua' => '',
                    'temperatura' => '',
                    'valor_leido' => '',
                    'observaciones' => '',
                    'analysis_id' => $serviceProcessDetail->id,
                ];
            }

            Log::info('PhAnalysisController@phAnalysis loaded data:', [
                'process_id' => isset($process) ? $process->id : null,
                'process_process_id' => isset($process) ? $process->process_id : null,
                'service_services_id' => isset($service) ? $service->services_id : null,
                'analysis_id' => isset($analysis) ? $analysis->id : null,
                'phAnalysis_exists' => !is_null($phAnalysis),
                'pending_items_count' => count($pendingItems),
                'pending_analyses_ids' => $pendingAnalyses->pluck('id')->all(),
            ]);

            // Determinar consecutivo para prellenar (usar el mismo target priorizado arriba)
            $consecutivoNo = $targetConsecutivo;
            if ($consecutivoNo === null) {
                if ($phAnalysis && !empty($phAnalysis->consecutivo_no)) {
                    $consecutivoNo = $phAnalysis->consecutivo_no;
                } else {
                    foreach ($pendingAnalyses as $pa) {
                        if (isset($pa->phAnalysis) && !empty($pa->phAnalysis->consecutivo_no)) {
                            $consecutivoNo = $pa->phAnalysis->consecutivo_no;
                            break;
                        }
                    }
                }
            }
            // Releer query para logging de consistencia (ya aplicado si existía)
            $queryConsecutivo = $queryConsecutivoEarly;

            Log::info('PhAnalysisController@phAnalysis consecutivo prefill', [ 'consecutivo_no' => $consecutivoNo, 'from_query' => $queryConsecutivo !== '' ? 'yes' : 'no' ]);

            // Mantener old() para preservar los datos ingresados cuando hay errores de validación

            return view('lscefa::ph_analyses.process', [
                'process' => $process,
                'service' => $service,
                'analysis' => $analysis,
                'phAnalysis' => $phAnalysis,
                'pendingAnalyses' => $pendingAnalyses,
                'pendingItems' => $pendingItems,
                'user' => Auth::user(),
                'consecutivo_no' => $consecutivoNo,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in PhAnalysisController@phAnalysis: ' . $e->getMessage(), [
                'processId' => $processId,
                'serviceId' => $serviceId,
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('lscefa.ph_analysis.index')
                           ->with('error', 'Error al cargar el formulario de análisis de pH: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created pH analysis in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storePhAnalysis(Request $request)
    {
        $request->validate([
            'consecutivo_no' => 'required|string|max:255',
            'fecha_analisis' => 'required|date',
            'codigo_probeta' => 'required|string|max:255',
            'codigo_equipo' => 'required|string|max:255',
            'serial_electrodo' => 'required|string|max:255',
            'serial_sonda_temperatura' => 'required|string|max:255',
            'controles_analiticos' => 'required|array|min:1',
            'controles_analiticos.*.lote' => 'nullable|string',
            'controles_analiticos.*.valor_leido' => ['nullable','regex:/^-?\d+(\.\d{1,4})?$/'],
            'controles_analiticos.*.valor_esperado' => ['nullable','regex:/^-?\d+(\.\d{1,4})?$/'],
            'controles_analiticos.*.observaciones' => 'nullable|string',
            'precision_analitica' => 'required|array',
            'precision_analitica.duplicado_a.valor_leido' => ['required','regex:/^-?\d+(\.\d{1,5})?$/'],
            'precision_analitica.duplicado_b.valor_leido' => ['required','regex:/^-?\d+(\.\d{1,5})?$/'],
            'items_ensayo' => 'required|array|min:1',
            'items_ensayo.*.identificacion' => 'required|string',
            'items_ensayo.*.peso' => ['required','regex:/^-?\d+(\.\d{1,5})?$/'],
            'items_ensayo.*.volumen_agua' => ['required','regex:/^-?\d+(\.\d{1,5})?$/'],
            'items_ensayo.*.temperatura' => ['required','regex:/^-?\d+(\.\d{1,5})?$/'],
            'items_ensayo.*.valor_leido' => ['required','regex:/^-?\d+(\.\d{1,5})?$/'],
            'observaciones' => 'nullable|string',
        ]);

        // Validar que al menos un control tenga valor_leido y valor_esperado
        $controles = $request->input('controles_analiticos', []);
        $controlesCompletos = collect($controles)->filter(function($control) {
            return !empty($control['valor_leido']) && !empty($control['valor_esperado']);
        });
        if ($controlesCompletos->isEmpty()) {
            return back()->withErrors(['controles_analiticos' => 'Debe completar al menos un control analítico con valor leído y valor esperado.'])->withInput();
        }

        // Filtrar controles vacíos antes de guardar
        $controles = $controlesCompletos->values()->all();

        // Incorporar la muestra de referencia dentro de controles_analiticos (para Veracidad)
        $muestraRefInput = $request->input('muestra_referencia', []);
        Log::info('storePhAnalysis - muestra_referencia input recibido', [ 'input' => $muestraRefInput ]);
        // Insertar si cualquier campo relevante viene con valor
        $camposRelevantes = ['lote','peso','volumen_agua','temperatura','valor_leido','valor_esperado','observaciones'];
        $tieneAlguno = false;
        if (is_array($muestraRefInput)) {
            foreach ($camposRelevantes as $k) {
                if (isset($muestraRefInput[$k]) && $muestraRefInput[$k] !== '' && $muestraRefInput[$k] !== null) {
                    $tieneAlguno = true;
                    break;
                }
            }
        }
        if (is_array($muestraRefInput) && $tieneAlguno) {
            // Normalizar campos y calcular métricas si es posible
            $valorLeidoRef = isset($muestraRefInput['valor_leido']) && $muestraRefInput['valor_leido'] !== '' ? (float) $muestraRefInput['valor_leido'] : null;
            $valorEsperadoRef = isset($muestraRefInput['valor_esperado']) && $muestraRefInput['valor_esperado'] !== '' ? (float) $muestraRefInput['valor_esperado'] : null;

            $errorRef = null;
            $aceptabilidadRef = null;
            if ($valorLeidoRef !== null && $valorEsperadoRef !== null && $valorEsperadoRef != 0) {
                $errorRef = abs(($valorLeidoRef - $valorEsperadoRef) / $valorEsperadoRef) * 100;
                $aceptabilidadRef = ($errorRef >= 0 && $errorRef <= 20) ? 'Aceptable' : 'No aceptable';
            }

            $muestraRef = [
                'tipo' => 'muestra_referencia',
                'identificacion' => $muestraRefInput['identificacion'] ?? 'Muestra de referencia o MRC',
                'lote' => $muestraRefInput['lote'] ?? null,
                'peso' => $muestraRefInput['peso'] ?? null,
                'volumen_agua' => $muestraRefInput['volumen_agua'] ?? null,
                'temperatura' => $muestraRefInput['temperatura'] ?? null,
                'valor_leido' => $muestraRefInput['valor_leido'] ?? null,
                'valor_esperado' => $muestraRefInput['valor_esperado'] ?? null,
                'error' => $errorRef,
                'aceptabilidad' => $aceptabilidadRef,
                'observaciones' => $muestraRefInput['observaciones'] ?? null,
            ];

            // Insertar en el índice 3 para mantener compatibilidad con vistas existentes
            $insertIndex = 3;
            if ($insertIndex >= 0) {
                array_splice($controles, $insertIndex, 0, [$muestraRef]);
            } else {
                $controles[] = $muestraRef;
            }

            // Log de verificación de inserción
            Log::info('storePhAnalysis - muestra_referencia preparada e insertada en controles_analiticos', [
                'insert_index' => $insertIndex,
                'muestra_referencia' => $muestraRef,
                'total_controles_post_insert' => count($controles),
            ]);
        }

        try {
            DB::beginTransaction();

            // Procesar controles analíticos
            foreach ($controles as &$control) {
                if (!empty($control['valor_leido']) && !empty($control['valor_esperado'])) {
                    $valorLeido = floatval($control['valor_leido']);
                    $valorEsperado = floatval($control['valor_esperado']);
                    $control['error'] = ($valorEsperado != 0) ? abs(($valorLeido - $valorEsperado) / $valorEsperado) * 100 : 0;
                    $control['aceptabilidad'] = ($control['error'] >= 0 && $control['error'] <= 20) ? 'Aceptable' : 'No aceptable';
                }
            }

            // Log resumen de controles antes de guardar
            try {
                $hasRef = false;
                $refIdx = null;
                foreach ($controles as $idx => $ctrl) {
                    if (is_array($ctrl) && (($ctrl['tipo'] ?? null) === 'muestra_referencia')) {
                        $hasRef = true;
                        $refIdx = $idx;
                        break;
                    }
                }
                Log::info('storePhAnalysis - resumen antes de persistir', [
                    'controles_count' => count($controles),
                    'has_muestra_referencia' => $hasRef ? 'sí' : 'no',
                    'muestra_referencia_index' => $refIdx,
                ]);
            } catch (\Throwable $e) {
                Log::warning('storePhAnalysis - error al generar resumen de controles: ' . $e->getMessage());
            }

            // Procesar precisión analítica
            $precision = $request->precision_analitica;
            $duplicadoA = floatval($precision['duplicado_a']['valor_leido']);
            $duplicadoB = floatval($precision['duplicado_b']['valor_leido']);
            $precision['promedio'] = ($duplicadoA + $duplicadoB) / 2;
            $precision['diferencia'] = abs($duplicadoA - $duplicadoB);
            $limite = 0.15;
            if ($precision['promedio'] > 7.00 && $precision['promedio'] < 7.5) {
                $limite = 0.20;
            } elseif ($precision['promedio'] >= 7.5 && $precision['promedio'] <= 8.00) {
                $limite = 0.30;
            } elseif ($precision['promedio'] > 8.00) {
                $limite = 0.40;
            }
            $precision['aceptabilidad'] = ($precision['diferencia'] <= $limite) ? 'Aceptable' : 'No aceptable';

            // Validación QC: bloquear envío si algún control o la precisión es No aceptable
            $hayControlNoAceptable = collect($controles)->contains(function($c){
                return isset($c['aceptabilidad']) && strtolower($c['aceptabilidad']) === 'no aceptable';
            });
            if ($hayControlNoAceptable || strtolower($precision['aceptabilidad']) === 'no aceptable') {
                DB::rollBack();
                return back()
                    ->withInput()
                    ->with('error', 'No es posible enviar el análisis: existe al menos un control o la precisión analítica marcada como "No aceptable".');
            }

            // Procesar ítems de ensayo
            $itemsEnsayo = $request->items_ensayo;
            $analyses = $request->input('analyses', []);

            foreach ($analyses as $analysisData) {
                $analysisId = $analysisData['analysis_id'];
                $analysis = ServiceProcessDetail::findOrFail($analysisId);

                // Filtrar los ítems de ensayo que corresponden a este análisis
                $analysisItems = array_filter($itemsEnsayo, function ($item) use ($analysisId) {
                    return $item['analysis_id'] == $analysisId;
                });

                // Reindexar los ítems para evitar problemas con claves
                $analysisItems = array_values($analysisItems);

                // Crear o actualizar el PhAnalysis
                $phAnalysis = PhAnalysis::updateOrCreate(
                    ['analysis_id' => $analysisId],
                    [
                        'consecutivo_no' => $request->consecutivo_no,
                        'fecha_analisis' => $request->fecha_analisis,
                        'user_id' => Auth::id(),
                        'codigo_probeta' => $request->codigo_probeta,
                        'codigo_equipo' => $request->codigo_equipo,
                        'serial_electrodo' => $request->serial_electrodo,
                        'serial_sonda_temperatura' => $request->serial_sonda_temperatura,
                        'controles_analiticos' => $controles,
                        'precision_analitica' => $precision,
                        'items_ensayo' => $analysisItems,
                        'observaciones' => $request->observaciones,
                        'review_status' => 'pending',
                    ]
                );

                // Actualizar el estado del análisis
                $analysis->status = 'completed';
                $analysis->save();
            }

            DB::commit();

            // Log post-guardado
            Log::info('storePhAnalysis - análisis de pH guardados OK', [
                'analyses_saved' => count($analyses),
            ]);

            return redirect()->route('lscefa.technical.analyses.index')
                            ->with('success', 'Análisis de pH guardados exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in PhAnalysisController@storePhAnalysis: ' . $e->getMessage(), [
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Error al guardar los análisis de pH: ' . $e->getMessage())->withInput();
        }
    }

    public function downloadPhReport($analysisId)
    {
        $phAnalysis = PhAnalysis::where('analysis_id', $analysisId)->firstOrFail();
        $analyst = \App\Models\User::find($phAnalysis->user_id);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'LABORATORIO DE CIENCIAS BÁSICAS');
        $sheet->setCellValue('A2', 'PROCEDIMIENTO DETERMINACIÓN DE pH EN SUELOS');
        $sheet->setCellValue('A3', 'FORMATO REPORTE RESULTADOS pH EN SUELOS');
        $sheet->setCellValue('D3', 'Versión: 6');
        $sheet->setCellValue('D4', 'Código: F-PSS-001');
        $sheet->setCellValue('D5', 'Página: 1 de 1');

        $sheet->setCellValue('A6', 'Consecutivo No.:');
        $sheet->setCellValue('B6', $phAnalysis->consecutivo_no);
        $sheet->setCellValue('A7', 'Fecha del análisis:');
        $sheet->setCellValue('B7', $phAnalysis->fecha_analisis);
        $sheet->setCellValue('A8', 'Nombre Analista:');
        $sheet->setCellValue('B8', $analyst->name);
        $sheet->setCellValue('A9', 'Código de la probeta:');
        $sheet->setCellValue('B9', $phAnalysis->codigo_probeta);
        $sheet->setCellValue('A10', 'Código Equipo potenciométrico:');
        $sheet->setCellValue('B10', $phAnalysis->codigo_equipo);
        $sheet->setCellValue('A11', 'Serial del electrodo:');
        $sheet->setCellValue('B11', $phAnalysis->serial_electrodo);
        $sheet->setCellValue('A12', 'Serial sonda de temperatura:');
        $sheet->setCellValue('B12', $phAnalysis->serial_sonda_temperatura);

        $sheet->setCellValue('A14', 'Controles analíticos');
        $sheet->setCellValue('A15', 'Identificación');
        $sheet->setCellValue('B15', 'Lote de identificación');
        $sheet->setCellValue('C15', 'Valor leído (Unidades de pH)');
        $sheet->setCellValue('D15', 'Valor esperado (Unidades de pH)');
        $sheet->setCellValue('E15', '% Error');
        $sheet->setCellValue('F15', 'Aceptabilidad del control');
        $sheet->setCellValue('G15', 'Observaciones');

        $row = 16;
        foreach ($phAnalysis->controles_analiticos as $control) {
            $sheet->setCellValue('A' . $row, $control['identificacion']);
            $sheet->setCellValue('B' . $row, $control['lote']);
            $sheet->setCellValue('C' . $row, $control['valor_leido']);
            $sheet->setCellValue('D' . $row, $control['valor_esperado']);
            $sheet->setCellValue('E' . $row, $control['error']);
            $sheet->setCellValue('F' . $row, $control['aceptabilidad']);
            $sheet->setCellValue('G' . $row, $control['observaciones']);
            $row++;
        }

        $sheet->setCellValue('A' . ($row + 1), 'Precisión analítica');
        $sheet->setCellValue('A' . ($row + 2), 'Identificación');
        $sheet->setCellValue('B' . ($row + 2), 'Valor leído (Unidades de pH)');
        $sheet->setCellValue('C' . ($row + 2), 'Promedio');
        $sheet->setCellValue('D' . ($row + 2), 'Diferencia');
        $sheet->setCellValue('E' . ($row + 2), 'Aceptabilidad');
        $sheet->setCellValue('F' . ($row + 2), 'Observaciones');

        $row += 3;
        $sheet->setCellValue('A' . $row, $phAnalysis->precision_analitica['duplicado_a']['identificacion']);
        $sheet->setCellValue('B' . $row, $phAnalysis->precision_analitica['duplicado_a']['valor_leido']);
        $sheet->setCellValue('C' . $row, $phAnalysis->precision_analitica['promedio']);
        $sheet->setCellValue('D' . $row, $phAnalysis->precision_analitica['diferencia']);
        $sheet->setCellValue('E' . $row, $phAnalysis->precision_analitica['aceptabilidad']);
        $sheet->setCellValue('F' . $row, $phAnalysis->precision_analitica['duplicado_a']['observaciones']);

        $row++;
        $sheet->setCellValue('A' . $row, $phAnalysis->precision_analitica['duplicado_b']['identificacion']);
        $sheet->setCellValue('B' . $row, $phAnalysis->precision_analitica['duplicado_b']['valor_leido']);
        $sheet->setCellValue('C' . $row, '');
        $sheet->setCellValue('D' . $row, '');
        $sheet->setCellValue('E' . $row, '');
        $sheet->setCellValue('F' . $row, $phAnalysis->precision_analitica['duplicado_b']['observaciones']);

        $row += 2;
        $sheet->setCellValue('A' . $row, 'Ítems de ensayo');
        $sheet->setCellValue('A' . ($row + 1), 'Identificación');
        $sheet->setCellValue('B' . ($row + 1), 'Peso (g)');
        $sheet->setCellValue('C' . ($row + 1), 'Volumen H₂O (mL)');
        $sheet->setCellValue('D' . ($row + 1), 'Temperatura (°C)');
        $sheet->setCellValue('E' . ($row + 1), 'Valor leído (Unidades de pH)');
        $sheet->setCellValue('F' . ($row + 1), 'Observaciones');

        $row += 2;
        foreach ($phAnalysis->items_ensayo as $item) {
            $sheet->setCellValue('A' . $row, $item['identificacion']);
            $sheet->setCellValue('B' . $row, $item['peso']);
            $sheet->setCellValue('C' . $row, $item['volumen_agua']);
            $sheet->setCellValue('D' . $row, $item['temperatura']);
            $sheet->setCellValue('E' . $row, $item['valor_leido']);
            $sheet->setCellValue('F' . $row, $item['observaciones']);
            $row++;
        }

        $sheet->setCellValue('A' . ($row + 1), 'Observaciones:');
        $sheet->setCellValue('A' . ($row + 2), $phAnalysis->observaciones);

        // Auto-size columns
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'reporte_ph_' . $phAnalysis->consecutivo_no . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
} 