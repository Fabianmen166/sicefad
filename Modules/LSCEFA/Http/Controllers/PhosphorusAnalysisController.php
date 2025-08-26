<?php

namespace Modules\LSCEFA\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Modules\LSCEFA\Models\Process;
use Modules\LSCEFA\Models\Service;
use Modules\LSCEFA\Models\ServiceProcessDetail;
use Modules\LSCEFA\Entities\PhosphorusAnalysis;
use Modules\LSCEFA\Entities\AnalyticalControl;
use Illuminate\Support\Facades\Auth;

class PhosphorusAnalysisController extends Controller
{
    public function index()
    {
        $processes = Process::with(['serviceProcessDetails.service'])
            ->whereHas('serviceProcessDetails', function ($query) {
                $query->whereHas('service', function ($serviceQuery) {
                    $serviceQuery->where('descripcion', 'like', '%fósforo%')
                                ->orWhere('descripcion', 'like', '%fosforo%')
                                ->orWhere('descripcion', 'like', '%phosphorus%');
                })->where('status', 'pending');
            })
            ->get();

        return view('lscefa::analyses.phosphorus.index', compact('processes'));
    }

    public function process($processId, $serviceId)
    {
        $process = Process::with(['serviceProcessDetails.service'])->findOrFail($processId);
        $service = Service::findOrFail($serviceId);
        
        // Get pending items for this process and service
        $pendingItems = [];
        $serviceProcessDetail = $process->serviceProcessDetails()
            ->where('service_id', $serviceId)
            ->where('status', 'pending')
            ->first();

        if ($serviceProcessDetail) {
            // For now, create a default item
            $pendingItems[] = [
                'identificacion' => 'Muestra 1',
                'peso' => '',
                'vol_naoh_muestra' => '',
                'vol_naoh_blanco' => '',
                'humedad' => '',
                'valor_leido' => ''
            ];
        }

        return view('lscefa::analyses.phosphorus.process', compact('process', 'service', 'pendingItems'));
    }

    public function storePhosphorusAnalysis(Request $request)
    {
        $processId = $request->input('process_id');
        $serviceId = $request->input('service_id');
        
        try {
            DB::beginTransaction();

            $request->validate([
                'consecutivo_no' => 'required|string',
                'fecha_analisis' => 'required|date',
                'equipo_utilizado' => 'nullable|string',
                'intervalo_metodo' => 'nullable|string',
                'analista' => 'nullable|string',
                'controles_analiticos' => 'required|array',
                'controles_analiticos.*.identificacion' => 'required|string',
                'controles_analiticos.*.valor_esperado' => 'nullable|numeric|min:0',
                'controles_analiticos.*.valor_leido' => 'nullable|numeric|min:0',
                'controles_analiticos.*.porcentaje_error' => 'nullable|numeric',
                'controles_analiticos.*.aceptabilidad_error' => 'nullable|string',
                'controles_analiticos.*.porcentaje_recuperacion' => 'nullable|numeric',
                'controles_analiticos.*.aceptabilidad_recuperacion' => 'nullable|string',
                'controles_analiticos.*.porcentaje_dpr' => 'nullable|numeric',
                'controles_analiticos.*.aceptabilidad_dpr' => 'nullable|string',
                'items' => 'required|array',
                'items.*.codigo_interno' => 'nullable|string',
                'items.*.peso_muestra' => 'nullable|numeric|min:0',
                'items.*.pw' => 'nullable|numeric|min:0',
                'items.*.v_extractante' => 'nullable|numeric|min:0',
                'items.*.lectura_blanco' => 'nullable|numeric|min:0',
                'items.*.factor_dilucion' => 'nullable|numeric|min:0',
                'items.*.fosforo_disponible_mg_l' => 'nullable|numeric|min:0',
                'items.*.fosforo_disponible_mg_kg' => 'nullable|numeric',
                'items.*.observaciones_item' => 'nullable|string',
            ]);

            Log::info('Iniciando guardado de análisis de fósforo', [
                'process_id' => $processId,
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);

            // Inicializar la variable analyticalControl
            $analyticalControl = null;

            // Verificar si ya existe un control analítico para este proceso
            $existingControl = AnalyticalControl::where('process_id', $processId)->first();
            if ($existingControl) {
                Log::warning('Ya existe un control analítico para este proceso, actualizando...', [
                    'process_id' => $processId,
                    'control_id' => $existingControl->id
                ]);
                // En lugar de bloquear, actualizar el control existente
                $existingControl->update([
                    'analysis_type' => 'phosphorus',
                    'controles_analiticos' => $request->controles_analiticos,
                    'dpr_duplicado_a' => $request->input('duplicado_a'),
                    'dpr_duplicado_b' => $request->input('duplicado_b'),
                    'dpr_resultado' => $request->input('dpr_resultado'),
                    'dpr_aceptabilidad' => $request->input('dpr_aceptabilidad'),
                ]);
                Log::info('Control analítico actualizado con ID: ' . $existingControl->id);
                $analyticalControl = $existingControl;
            } else {
                // Guardar controles analíticos
                $controlData = [
                    'process_id' => $processId,
                    'analysis_type' => 'phosphorus',
                    'controles_analiticos' => $request->controles_analiticos,
                    'dpr_duplicado_a' => $request->input('duplicado_a'),
                    'dpr_duplicado_b' => $request->input('duplicado_b'),
                    'dpr_resultado' => $request->input('dpr_resultado'),
                    'dpr_aceptabilidad' => $request->input('dpr_aceptabilidad'),
                ];

                Log::info('Guardando control analítico', $controlData);

                $analyticalControl = AnalyticalControl::create($controlData);

                Log::info('Control analítico creado con ID: ' . $analyticalControl->id);
            }

            // Guardar múltiples análisis de fósforo (uno por cada fila de resultados)
            $phosphorusAnalyses = [];
            $items = $request->input('items', []);
            $valuesKg = [];
            $valuesL = [];

            Log::info('Guardando análisis de fósforo', [
                'total_rows' => count($items),
                'items' => $items
            ]);

            foreach ($items as $index => $item) {
                // Saltar filas vacías (sin datos relevantes)
                $hasMeaningfulData = false;
                if (is_array($item)) {
                    $checkFields = [
                        'codigo_interno','peso_muestra','pw','v_extractante',
                        'lectura_blanco','factor_dilucion','fosforo_disponible_mg_l',
                        'fosforo_disponible_mg_kg','observaciones_item'
                    ];
                    foreach ($checkFields as $f) {
                        if (isset($item[$f]) && $item[$f] !== '' && $item[$f] !== null) {
                            $hasMeaningfulData = true;
                            break;
                        }
                    }
                }

                if (!$hasMeaningfulData) {
                    Log::info("Fila de ítem {$index} omitida por no contener datos.", ['item' => $item]);
                    continue;
                }

                $analysisData = [
                    'process_id' => (string)$processId,
                    'service_id' => $serviceId,
                ];

                // Map fields to whichever column exists (EN preferred)
                // Usar exactamente el consecutivo ingresado en el formulario
                $consecValue = $request->input('consecutivo_no');
                if (Schema::hasColumn('phosphorus_analyses', 'consecutive_no')) {
                    $analysisData['consecutive_no'] = $consecValue;
                } elseif (Schema::hasColumn('phosphorus_analyses', 'consecutivo_no')) {
                    $analysisData['consecutivo_no'] = $consecValue;
                }

                $dateValue = $request->input('fecha_analisis');
                if (Schema::hasColumn('phosphorus_analyses', 'analysis_date')) {
                    $analysisData['analysis_date'] = $dateValue;
                } elseif (Schema::hasColumn('phosphorus_analyses', 'fecha_analisis')) {
                    $analysisData['fecha_analisis'] = $dateValue;
                }

                $equipValue = $request->input('equipo_utilizado');
                if (Schema::hasColumn('phosphorus_analyses', 'equipment_used')) {
                    $analysisData['equipment_used'] = $equipValue;
                } elseif (Schema::hasColumn('phosphorus_analyses', 'equipo_utilizado')) {
                    $analysisData['equipo_utilizado'] = $equipValue;
                }

                $intervalValue = $request->input('intervalo_metodo');
                if (Schema::hasColumn('phosphorus_analyses', 'method_interval')) {
                    $analysisData['method_interval'] = $intervalValue;
                } elseif (Schema::hasColumn('phosphorus_analyses', 'intervalo_metodo')) {
                    $analysisData['intervalo_metodo'] = $intervalValue;
                }

                $analystValue = $request->input('analista');
                if (Schema::hasColumn('phosphorus_analyses', 'analyst_name')) {
                    $analysisData['analyst_name'] = $analystValue;
                } elseif (Schema::hasColumn('phosphorus_analyses', 'nombre_analista')) {
                    $analysisData['nombre_analista'] = $analystValue;
                }

                $observationsMain = $request->input('observaciones', '');
                if (Schema::hasColumn('phosphorus_analyses', 'observations')) {
                    $analysisData['observations'] = $observationsMain;
                } elseif (Schema::hasColumn('phosphorus_analyses', 'observaciones')) {
                    $analysisData['observaciones'] = $observationsMain;
                }

                // Items mapping
                $internalCode = isset($item['codigo_interno']) ? trim($item['codigo_interno']) : '';
                if (Schema::hasColumn('phosphorus_analyses', 'internal_code')) {
                    $analysisData['internal_code'] = $internalCode;
                } elseif (Schema::hasColumn('phosphorus_analyses', 'codigo_interno')) {
                    $analysisData['codigo_interno'] = $internalCode;
                }

                $sampleWeight = $item['peso_muestra'] ?? 0;
                if (Schema::hasColumn('phosphorus_analyses', 'sample_weight')) {
                    $analysisData['sample_weight'] = $sampleWeight;
                } elseif (Schema::hasColumn('phosphorus_analyses', 'peso_muestra')) {
                    $analysisData['peso_muestra'] = $sampleWeight;
                }

                // pw has same name in both schemas
                if (Schema::hasColumn('phosphorus_analyses', 'pw')) {
                    $analysisData['pw'] = $item['pw'] ?? 0;
                }

                $extractVol = $item['v_extractante'] ?? 0;
                if (Schema::hasColumn('phosphorus_analyses', 'extractant_volume')) {
                    $analysisData['extractant_volume'] = $extractVol;
                } elseif (Schema::hasColumn('phosphorus_analyses', 'v_extractante')) {
                    $analysisData['v_extractante'] = $extractVol;
                }

                $blankRead = $item['lectura_blanco'] ?? 0;
                if (Schema::hasColumn('phosphorus_analyses', 'blank_reading')) {
                    $analysisData['blank_reading'] = $blankRead;
                } elseif (Schema::hasColumn('phosphorus_analyses', 'lectura_blanco')) {
                    $analysisData['lectura_blanco'] = $blankRead;
                }

                $dilFactor = $item['factor_dilucion'] ?? 0;
                if (Schema::hasColumn('phosphorus_analyses', 'dilution_factor')) {
                    $analysisData['dilution_factor'] = $dilFactor;
                } elseif (Schema::hasColumn('phosphorus_analyses', 'factor_dilucion')) {
                    $analysisData['factor_dilucion'] = $dilFactor;
                }

                $availMgL = $item['fosforo_disponible_mg_l'] ?? 0;
                if (Schema::hasColumn('phosphorus_analyses', 'available_phosphorus_mg_l')) {
                    $analysisData['available_phosphorus_mg_l'] = $availMgL;
                } elseif (Schema::hasColumn('phosphorus_analyses', 'fosforo_disponible_mg_l')) {
                    $analysisData['fosforo_disponible_mg_l'] = $availMgL;
                }

                $availMgKg = $item['fosforo_disponible_mg_kg'] ?? 0;
                if (Schema::hasColumn('phosphorus_analyses', 'available_phosphorus_mg_kg')) {
                    $analysisData['available_phosphorus_mg_kg'] = $availMgKg;
                } elseif (Schema::hasColumn('phosphorus_analyses', 'fosforo_disponible_mg_kg')) {
                    $analysisData['fosforo_disponible_mg_kg'] = $availMgKg;
                }

                $itemObs = $item['observaciones_item'] ?? '';
                if (Schema::hasColumn('phosphorus_analyses', 'item_observations')) {
                    $analysisData['item_observations'] = $itemObs;
                } elseif (Schema::hasColumn('phosphorus_analyses', 'observaciones_item')) {
                    $analysisData['observaciones_item'] = $itemObs;
                }

                Log::info("Creando análisis {$index}", $analysisData);

                $phosphorusAnalysis = PhosphorusAnalysis::create($analysisData);
                
                $phosphorusAnalyses[] = $phosphorusAnalysis;
                // Acumular valores para resultado (preferir mg/kg)
                $valKg = $analysisData['available_phosphorus_mg_kg']
                    ?? $analysisData['fosforo_disponible_mg_kg']
                    ?? null;
                $valL = $analysisData['available_phosphorus_mg_l']
                    ?? $analysisData['fosforo_disponible_mg_l']
                    ?? null;
                if ($valKg !== null && $valKg !== '') { $valuesKg[] = (float)$valKg; }
                if ($valL !== null && $valL !== '') { $valuesL[] = (float)$valL; }
                
                Log::info("Análisis {$index} creado con ID: {$phosphorusAnalysis->id}");
            }

            // Actualizar el estado del servicio a 'completed' usando el mismo service_id del análisis
            $serviceProcessDetail = ServiceProcessDetail::where('process_id', $processId)
                ->where('service_id', $serviceId)
                ->first();

            if ($serviceProcessDetail) {
                // Calcular resultado a guardar en SPD (promedio). Preferir mg/kg.
                $resultValue = null;
                if (count($valuesKg) > 0) {
                    $resultValue = round(array_sum($valuesKg) / count($valuesKg), 2);
                } elseif (count($valuesL) > 0) {
                    $resultValue = round(array_sum($valuesL) / count($valuesL), 2);
                }
                $updatedRows = ServiceProcessDetail::where('process_id', $processId)
                    ->where('service_id', $serviceId)
                    ->update([
                        'status' => 'completed',
                        'result' => $resultValue !== null ? (string)$resultValue : 'Análisis de fósforo completado',
                        'observations' => 'Análisis guardado exitosamente con ' . count($phosphorusAnalyses) . ' muestras'
                    ]);

                Log::info('Actualización del estado del servicio', [
                    'process_id' => $processId,
                    'service_id' => $serviceId,
                    'rows_updated' => $updatedRows,
                    'service_process_detail_id' => $serviceProcessDetail->id
                ]);

                // Verificar si todos los servicios del proceso están completados
                $allServicesCompleted = ServiceProcessDetail::where('process_id', $processId)
                    ->where('status', '!=', 'completed')
                    ->count() === 0;

                // Si todos los servicios están completados, actualizar el estado del proceso
                if ($allServicesCompleted) {
                    $processUpdated = Process::where('process_id', $processId)
                        ->update(['status' => 'completed']);

                    Log::info('Actualización del estado del proceso', [
                        'process_id' => $processId,
                        'process_updated' => $processUpdated,
                        'all_services_completed' => $allServicesCompleted
                    ]);
                }
            } else {
                Log::warning('ServiceProcessDetail no encontrado para actualizar', [
                    'process_id' => $processId,
                    'service_id' => $serviceId
                ]);
            }

            DB::commit();

            Log::info('Análisis de fósforo guardado exitosamente', [
                'user_id' => Auth::id(),
                'process_id' => $processId,
                'analyses_count' => count($phosphorusAnalyses),
                'analytical_control_id' => $analyticalControl ? $analyticalControl->id : null
            ]);

            return redirect()->route('lscefa.technical.analyses.phosphorus.index')
                ->with('success', 'Análisis de fósforo guardado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar análisis de fósforo', [
                'user_id' => Auth::id(),
                'process_id' => $processId,
                'error' => $e->getMessage()
            ]);

            return back()->withInput()->with('error', 'Error al guardar el análisis de fósforo: ' . $e->getMessage());
        }
    }

    public function batchProcess(Request $request)
    {
        // Obtener los IDs de procesos seleccionados desde la URL
        $selectedProcessIds = [];
        if ($request->has('processes')) {
            $selectedProcessIds = explode(',', $request->processes);
        }

        // Si no hay procesos seleccionados, redirigir al index
        if (empty($selectedProcessIds)) {
            return redirect()->route('lscefa.technical.analyses.phosphorus.index')
                ->with('error', 'No se seleccionaron procesos para procesar.');
        }

        // Obtener solo los procesos seleccionados que estén pendientes
        $pendingProcesses = Process::with(['serviceProcessDetails.service'])
            ->whereIn('process_id', $selectedProcessIds)
            ->whereHas('serviceProcessDetails', function ($query) {
                $query->whereHas('service', function ($serviceQuery) {
                    $serviceQuery->where('descripcion', 'like', '%fósforo%');
                })->where('status', 'pending');
            })
            ->get();

        // Verificar que todos los procesos seleccionados se encontraron
        if ($pendingProcesses->count() !== count($selectedProcessIds)) {
            $foundIds = $pendingProcesses->pluck('process_id')->toArray();
            $missingIds = array_diff($selectedProcessIds, $foundIds);
            
            if (!empty($missingIds)) {
                return redirect()->route('lscefa.technical.analyses.phosphorus.index')
                    ->with('error', 'Algunos procesos seleccionados no están disponibles: ' . implode(', ', $missingIds));
            }
        }

        return view('lscefa::analyses.phosphorus.batch_process', compact('pendingProcesses'));
    }

    public function batchStore(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'process_ids' => 'required|array',
                'process_ids.*' => 'required|string',
                'consecutivo_no' => 'nullable|string',
                'fecha_analisis' => 'nullable|date',
                'equipo_utilizado' => 'nullable|string',
                'intervalo_metodo' => 'nullable|string',
                'nombre_analista' => 'nullable|string',
                'curva_valor_leido' => 'nullable|numeric',
                'curva_error_porcentaje' => 'nullable|numeric',
                'controles_analiticos' => 'required|array',
                'controles_analiticos.*.identificacion' => 'required|string',
                'controles_analiticos.*.valor_esperado' => 'nullable|numeric|min:0',
                'controles_analiticos.*.valor_leido' => 'nullable|numeric|min:0',
                'controles_analiticos.*.porcentaje_error' => 'nullable|numeric',
                'controles_analiticos.*.aceptabilidad_error' => 'nullable|string',
                'controles_analiticos.*.porcentaje_recuperacion' => 'nullable|numeric',
                'controles_analiticos.*.aceptabilidad_recuperacion' => 'nullable|string',
                'controles_analiticos.*.porcentaje_dpr' => 'nullable|numeric',
                'controles_analiticos.*.aceptabilidad_dpr' => 'nullable|string',
                'duplicado_a' => 'nullable|numeric',
                'duplicado_b' => 'nullable|numeric',
                'dpr_resultado' => 'nullable|numeric',
                'dpr_aceptabilidad' => 'nullable|string',
                'items_ensayo' => 'required|array',
                'items_ensayo.*.codigo_interno' => 'nullable|string',
                'items_ensayo.*.peso_muestra' => 'nullable|numeric|min:0',
                'items_ensayo.*.pw' => 'nullable|numeric|min:0',
                'items_ensayo.*.v_extractante' => 'nullable|numeric|min:0',
                'items_ensayo.*.lectura_blanco' => 'nullable|numeric|min:0',
                'items_ensayo.*.factor_dilucion' => 'nullable|numeric|min:0',
                'items_ensayo.*.fosforo_disponible_mg_l' => 'nullable|numeric|min:0',
                'items_ensayo.*.fosforo_disponible_mg_kg' => 'nullable|numeric',
                'items_ensayo.*.observaciones_item' => 'nullable|string',
            ]);

            Log::info('Iniciando guardado de análisis de fósforo por lotes', [
                'user_id' => Auth::id(),
                'total_processes' => count($request->process_ids),
                'request_data' => $request->all()
            ]);

            // Proporcionar valores por defecto para campos requeridos
            $consecutivoNo = $request->consecutivo_no ?: 'BATCH-' . date('Ymd-His');
            $fechaAnalisis = $request->fecha_analisis ?: date('Y-m-d');

            $savedCount = 0;
            $errors = [];

            foreach ($request->process_ids as $index => $processId) {
                try {
                    Log::info("Procesando proceso {$index}: {$processId}");

                    // Buscar el servicio de fósforo para este proceso
                    $phosphorusService = Service::whereRaw('LOWER(descripcion) LIKE ?', ['%fósforo%'])
                                            ->orWhereRaw('LOWER(descripcion) LIKE ?', ['%fosforo%'])
                                            ->orWhereRaw('LOWER(descripcion) LIKE ?', ['%phosphorus%'])
                                            ->first();

                    if (!$phosphorusService) {
                        Log::warning('No se encontró el servicio de fósforo', ['process_id' => $processId]);
                        $errors[] = "Proceso {$processId}: No se encontró el servicio de fósforo";
                        continue;
                    }

                    $serviceId = $phosphorusService->services_id;

                    // Inicializar la variable analyticalControl
                    $analyticalControl = null;

                    // Verificar si ya existe un control analítico para este proceso
                    $existingControl = AnalyticalControl::where('process_id', $processId)->first();
                    if ($existingControl) {
                        Log::warning('Ya existe un control analítico para este proceso, actualizando...', [
                            'process_id' => $processId,
                            'control_id' => $existingControl->id
                        ]);
                        // En lugar de bloquear, actualizar el control existente
                        $existingControl->update([
                            'analysis_type' => 'phosphorus',
                            'controles_analiticos' => $request->controles_analiticos,
                            'curva_valor_leido' => $request->input('curva_valor_leido') ?? 0,
                            'curva_error_porcentaje' => $request->input('curva_error_porcentaje') ?? 0,
                            'dpr_duplicado_a' => $request->input('duplicado_a') ?? 0,
                            'dpr_duplicado_b' => $request->input('duplicado_b') ?? 0,
                            'dpr_resultado' => $request->input('dpr_resultado') ?? 0,
                            'dpr_aceptabilidad' => $request->input('dpr_aceptabilidad') ?? '',
                        ]);
                        Log::info('Control analítico actualizado con ID: ' . $existingControl->id);
                        $analyticalControl = $existingControl;
                    } else {
                        // Guardar control analítico (usando los datos del formulario de lote)
                        $controlData = [
                            'process_id' => $processId,
                            'analysis_type' => 'phosphorus',
                            'controles_analiticos' => $request->controles_analiticos,
                            'curva_valor_leido' => $request->input('curva_valor_leido') ?? 0,
                            'curva_error_porcentaje' => $request->input('curva_error_porcentaje') ?? 0,
                            'dpr_duplicado_a' => $request->input('duplicado_a') ?? 0,
                            'dpr_duplicado_b' => $request->input('duplicado_b') ?? 0,
                            'dpr_resultado' => $request->input('dpr_resultado') ?? 0,
                            'dpr_aceptabilidad' => $request->input('dpr_aceptabilidad') ?? '',
                        ];

                        Log::info('Guardando control analítico para proceso', [
                            'process_id' => $processId,
                            'control_data' => $controlData
                        ]);

                        $analyticalControl = AnalyticalControl::create($controlData);

                        Log::info('Control analítico creado con ID: ' . $analyticalControl->id);
                    }

                    // Guardar análisis de fósforo (una fila por proceso en el formulario)
                    $phosphorusAnalyses = [];
                    $item = $request->input("items_ensayo.{$index}", []);

                    Log::info('Guardando análisis de fósforo para proceso', [
                        'process_id' => $processId,
                        'item' => $item
                    ]);

                    // Saltar si el ítem no contiene datos relevantes
                    $hasMeaningfulData = false;
                    $hasPositiveNumeric = false;
                    if (is_array($item)) {
                        $checkFields = [
                            'codigo_interno','peso_muestra','pw','v_extractante',
                            'lectura_blanco','factor_dilucion','fosforo_disponible_mg_l',
                            'fosforo_disponible_mg_kg','observaciones_item'
                        ];
                        foreach ($checkFields as $f) {
                            if (isset($item[$f]) && $item[$f] !== '' && $item[$f] !== null) {
                                $hasMeaningfulData = true;
                                if (in_array($f, ['peso_muestra','pw','v_extractante','lectura_blanco','factor_dilucion','fosforo_disponible_mg_l','fosforo_disponible_mg_kg'], true)) {
                                    $val = (float)str_replace(',', '.', (string)$item[$f]);
                                    if ($val > 0) { $hasPositiveNumeric = true; }
                                }
                            }
                        }
                    }

                    if (!$hasMeaningfulData || !$hasPositiveNumeric) {
                        Log::info("Ítem del proceso {$processId} omitido por no contener datos.", ['item' => $item]);
                        // No crear análisis vacío para este proceso
                    } else {
                        // Mapear dinámicamente a columnas ES/EN existentes y poblar ambas si existen
                        $analysisData = [
                            'process_id' => (string)$processId,
                            'service_id' => $serviceId,
                        ];

                        // consecutivo/consecutive
                        if (Schema::hasColumn('phosphorus_analyses', 'consecutivo_no')) {
                            $analysisData['consecutivo_no'] = $consecutivoNo;
                        }
                        if (Schema::hasColumn('phosphorus_analyses', 'consecutive_no')) {
                            $analysisData['consecutive_no'] = $consecutivoNo;
                        }

                        // fecha/analysis_date
                        if (Schema::hasColumn('phosphorus_analyses', 'fecha_analisis')) {
                            $analysisData['fecha_analisis'] = $fechaAnalisis;
                        }
                        if (Schema::hasColumn('phosphorus_analyses', 'analysis_date')) {
                            $analysisData['analysis_date'] = $fechaAnalisis;
                        }

                        // equipo/equipment
                        $equipValue = $request->equipo_utilizado ?? '';
                        if (Schema::hasColumn('phosphorus_analyses', 'equipo_utilizado')) {
                            $analysisData['equipo_utilizado'] = $equipValue;
                        }
                        if (Schema::hasColumn('phosphorus_analyses', 'equipment_used')) {
                            $analysisData['equipment_used'] = $equipValue;
                        }

                        // intervalo/method_interval
                        $intervalValue = $request->intervalo_metodo ?? '';
                        if (Schema::hasColumn('phosphorus_analyses', 'intervalo_metodo')) {
                            $analysisData['intervalo_metodo'] = $intervalValue;
                        }
                        if (Schema::hasColumn('phosphorus_analyses', 'method_interval')) {
                            $analysisData['method_interval'] = $intervalValue;
                        }

                        // analista/analyst
                        $analystValue = $request->nombre_analista ?? '';
                        if (Schema::hasColumn('phosphorus_analyses', 'nombre_analista')) {
                            $analysisData['nombre_analista'] = $analystValue;
                        }
                        if (Schema::hasColumn('phosphorus_analyses', 'analyst_name')) {
                            $analysisData['analyst_name'] = $analystValue;
                        }

                        // observaciones
                        $obsValue = $request->observaciones ?? '';
                        if (Schema::hasColumn('phosphorus_analyses', 'observaciones')) {
                            $analysisData['observaciones'] = $obsValue;
                        }
                        if (Schema::hasColumn('phosphorus_analyses', 'observations')) {
                            $analysisData['observations'] = $obsValue;
                        }

                        // Ítems
                        $internalCode = $item['codigo_interno'] ?? '';
                        if (Schema::hasColumn('phosphorus_analyses', 'codigo_interno')) {
                            $analysisData['codigo_interno'] = $internalCode;
                        }
                        if (Schema::hasColumn('phosphorus_analyses', 'internal_code')) {
                            $analysisData['internal_code'] = $internalCode;
                        }

                        $sampleWeight = $item['peso_muestra'] ?? 0;
                        if (Schema::hasColumn('phosphorus_analyses', 'peso_muestra')) {
                            $analysisData['peso_muestra'] = $sampleWeight;
                        }
                        if (Schema::hasColumn('phosphorus_analyses', 'sample_weight')) {
                            $analysisData['sample_weight'] = $sampleWeight;
                        }

                        if (Schema::hasColumn('phosphorus_analyses', 'pw')) {
                            $analysisData['pw'] = $item['pw'] ?? 0;
                        }

                        $extractVol = $item['v_extractante'] ?? 0;
                        if (Schema::hasColumn('phosphorus_analyses', 'v_extractante')) {
                            $analysisData['v_extractante'] = $extractVol;
                        }
                        if (Schema::hasColumn('phosphorus_analyses', 'extractant_volume')) {
                            $analysisData['extractant_volume'] = $extractVol;
                        }

                        $blankRead = $item['lectura_blanco'] ?? 0;
                        if (Schema::hasColumn('phosphorus_analyses', 'lectura_blanco')) {
                            $analysisData['lectura_blanco'] = $blankRead;
                        }
                        if (Schema::hasColumn('phosphorus_analyses', 'blank_reading')) {
                            $analysisData['blank_reading'] = $blankRead;
                        }

                        $dilFactor = $item['factor_dilucion'] ?? 0;
                        if (Schema::hasColumn('phosphorus_analyses', 'factor_dilucion')) {
                            $analysisData['factor_dilucion'] = $dilFactor;
                        }
                        if (Schema::hasColumn('phosphorus_analyses', 'dilution_factor')) {
                            $analysisData['dilution_factor'] = $dilFactor;
                        }

                        // Valores base
                        $availMgL = $item['fosforo_disponible_mg_l']
                            ?? $item['available_phosphorus_mg_l']
                            ?? 0;
                        if (Schema::hasColumn('phosphorus_analyses', 'fosforo_disponible_mg_l')) {
                            $analysisData['fosforo_disponible_mg_l'] = $availMgL;
                        }
                        if (Schema::hasColumn('phosphorus_analyses', 'available_phosphorus_mg_l')) {
                            $analysisData['available_phosphorus_mg_l'] = $availMgL;
                        }

                        // Calcular mg/kg en servidor si no viene: ((mg/L * fd) - blanco) * Vext / peso * (100 + pw)/100
                        $availMgKg = $item['fosforo_disponible_mg_kg']
                            ?? $item['available_phosphorus_mg_kg']
                            ?? null;
                        if ($availMgKg === null || $availMgKg === '' || (float)$availMgKg == 0.0) {
                            $pesoMuestra    = (float)str_replace(',', '.', (string)($item['peso_muestra'] ?? 0));
                            $vExtractante   = (float)str_replace(',', '.', (string)($item['v_extractante'] ?? 0));
                            $factorDilucion = (float)str_replace(',', '.', (string)($item['factor_dilucion'] ?? 0));
                            $pw             = (float)str_replace(',', '.', (string)($item['pw'] ?? 0));
                            $lecturaBlanco  = (float)str_replace(',', '.', (string)($item['lectura_blanco'] ?? 0));
                            $mgL            = (float)str_replace(',', '.', (string)$availMgL);
                            if ($mgL > 0 && $pesoMuestra > 0) {
                                $calc = (($mgL * $factorDilucion) - $lecturaBlanco) * $vExtractante / $pesoMuestra * (100 + $pw) / 100;
                                $availMgKg = round($calc, 2);
                            }
                        }
                        if (Schema::hasColumn('phosphorus_analyses', 'fosforo_disponible_mg_kg')) {
                            $analysisData['fosforo_disponible_mg_kg'] = $availMgKg ?? 0;
                        }
                        if (Schema::hasColumn('phosphorus_analyses', 'available_phosphorus_mg_kg')) {
                            $analysisData['available_phosphorus_mg_kg'] = $availMgKg ?? 0;
                        }

                        $itemObs = $item['observaciones_item'] ?? '';
                        if (Schema::hasColumn('phosphorus_analyses', 'observaciones_item')) {
                            $analysisData['observaciones_item'] = $itemObs;
                        }
                        if (Schema::hasColumn('phosphorus_analyses', 'item_observations')) {
                            $analysisData['item_observations'] = $itemObs;
                        }

                        Log::info("Creando análisis para proceso {$processId}", $analysisData);

                        $phosphorusAnalysis = PhosphorusAnalysis::create($analysisData);
                        $phosphorusAnalyses[] = $phosphorusAnalysis;

                        Log::info("Análisis creado con ID: {$phosphorusAnalysis->id}");
                    }

                    // Actualizar el estado del servicio a 'completed'
                    $serviceProcessDetail = ServiceProcessDetail::where('process_id', $processId)
                        ->where('service_id', $phosphorusService->services_id)
                        ->first();

                    if ($serviceProcessDetail) {
                        // Determinar resultado a guardar para este proceso (preferir mg/kg)
                        $batchResultValue = null;
                        $valKgKeys = ['fosforo_disponible_mg_kg', 'available_phosphorus_mg_kg'];
                        $valLKeys  = ['fosforo_disponible_mg_l',  'available_phosphorus_mg_l'];
                        foreach ($valKgKeys as $k) {
                            if (isset($item[$k]) && $item[$k] !== '' && $item[$k] !== null) {
                                $batchResultValue = (float)str_replace(',', '.', (string)$item[$k]);
                                break;
                            }
                        }
                        if ($batchResultValue === null) {
                            foreach ($valLKeys as $k) {
                                if (isset($item[$k]) && $item[$k] !== '' && $item[$k] !== null) {
                                    $batchResultValue = (float)str_replace(',', '.', (string)$item[$k]);
                                    break;
                                }
                            }
                        }
                        // Si aún es null, intenta usar el valor calculado en analysisData
                        if ($batchResultValue === null && isset($analysisData)) {
                            $fromData = $analysisData['available_phosphorus_mg_kg']
                                ?? $analysisData['fosforo_disponible_mg_kg']
                                ?? $analysisData['available_phosphorus_mg_l']
                                ?? $analysisData['fosforo_disponible_mg_l']
                                ?? null;
                            if ($fromData !== null && $fromData !== '') {
                                $batchResultValue = (float)$fromData;
                            }
                        }
                        // Fallback: si no vino en request, tomar del análisis creado
                        if ($batchResultValue === null && !empty($phosphorusAnalyses)) {
                            $created = $phosphorusAnalyses[0] ?? null;
                            if ($created) {
                                $createdKg = $created->available_phosphorus_mg_kg ?? $created->fosforo_disponible_mg_kg ?? null;
                                $createdL  = $created->available_phosphorus_mg_l  ?? $created->fosforo_disponible_mg_l  ?? null;
                                if ($createdKg !== null && $createdKg !== '') {
                                    $batchResultValue = (float)$createdKg;
                                } elseif ($createdL !== null && $createdL !== '') {
                                    $batchResultValue = (float)$createdL;
                                }
                            }
                        }
                        $updatedRows = ServiceProcessDetail::where('process_id', $processId)
                            ->where('service_id', $phosphorusService->services_id)
                            ->update([
                                'status' => 'completed',
                                'result' => $batchResultValue !== null ? (string)round($batchResultValue, 2) : 'Análisis de fósforo completado',
                                'observations' => 'Análisis guardado exitosamente con ' . count($phosphorusAnalyses) . ' muestras'
                            ]);

                        Log::info('Actualización del estado del servicio', [
                            'process_id' => $processId,
                            'service_id' => $phosphorusService->services_id,
                            'rows_updated' => $updatedRows,
                            'service_process_detail_id' => $serviceProcessDetail->id
                        ]);

                        // Verificar si todos los servicios del proceso están completados
                        $allServicesCompleted = ServiceProcessDetail::where('process_id', $processId)
                            ->where('status', '!=', 'completed')
                            ->count() === 0;
                        
                        // Si todos los servicios están completados, actualizar el estado del proceso
                        if ($allServicesCompleted) {
                            $processUpdated = Process::where('process_id', $processId)
                                ->update(['status' => 'completed']);
                            
                            Log::info('Actualización del estado del proceso', [
                                'process_id' => $processId,
                                'process_updated' => $processUpdated,
                                'all_services_completed' => $allServicesCompleted
                            ]);
                        }
                    } else {
                        Log::warning('ServiceProcessDetail no encontrado para actualizar', [
                            'process_id' => $processId,
                            'service_id' => $phosphorusService->services_id
                        ]);
                    }

                    $savedCount++;

                    Log::info('Análisis de fósforo guardado exitosamente en lote', [
                        'process_id' => $processId,
                        'analyses_count' => count($phosphorusAnalyses),
                        'analytical_control_id' => $analyticalControl ? $analyticalControl->id : null
                    ]);

                } catch (\Exception $e) {
                    Log::error('Error al guardar análisis de fósforo en lote', [
                        'process_id' => $processId,
                        'error' => $e->getMessage()
                    ]);
                    $errors[] = "Error en proceso {$processId}: " . $e->getMessage();
                }
            }

            DB::commit();

            Log::info('Procesamiento por lotes completado', [
                'user_id' => Auth::id(),
                'saved_count' => $savedCount,
                'total_errors' => count($errors)
            ]);

            $message = "Se guardaron exitosamente {$savedCount} análisis de fósforo.";
            if (!empty($errors)) {
                $message .= " Errores: " . implode(', ', $errors);
            }

            return redirect()->route('lscefa.technical.analyses.phosphorus.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar análisis de fósforo por lotes', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return back()->withInput()->with('error', 'Error al guardar análisis de fósforo por lotes: ' . $e->getMessage());
        }
    }
} 