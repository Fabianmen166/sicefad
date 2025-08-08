<?php

namespace Modules\LSCEFA\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\LSCEFA\Models\Process;
use Modules\LSCEFA\Models\Service;
use Modules\LSCEFA\Models\ServiceProcessDetail;
use Modules\LSCEFA\Entities\BoronAnalysis;
use Modules\LSCEFA\Entities\BoronAnalysisDetail;
use Modules\LSCEFA\Entities\AnalyticalControl;
use Illuminate\Support\Facades\Auth;

class BoronAnalysisController extends Controller
{
    public function index()
    {
        $processes = Process::with(['serviceProcessDetails.service'])
            ->whereHas('serviceProcessDetails', function ($query) {
                $query->whereHas('service', function ($serviceQuery) {
                    $serviceQuery->where(function($q) {
                        $q->whereRaw('LOWER(descripcion) LIKE ?', ['%boro%'])
                          ->orWhereRaw('LOWER(descripcion) LIKE ?', ['%boron%'])
                          ->orWhereRaw('LOWER(descripcion) LIKE ?', ['%boron%']);
                    });
                })->where('status', 'pending');
            })
            ->get();

        return view('lscefa::analyses.boron.index', compact('processes'));
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

        return view('lscefa::analyses.boron.process', compact('process', 'service', 'pendingItems'));
    }

    public function storeBoronAnalysis(Request $request)
    {
        $processId = $request->input('process_id');
        $serviceId = $request->input('service_id');
        
        try {
            DB::beginTransaction();

            $request->validate([
                'consecutivo_no' => 'required|string',
                'metodologia_aplicada' => 'nullable|string',
                'intervalo_metodo' => 'nullable|string',
                'fecha_analisis' => 'required|date',
                'equipo_utilizado' => 'nullable|string',
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
                'items.*.boro_disponible_mg_l' => 'nullable|numeric|min:0',
                'items.*.boro_disponible_mg_kg' => 'nullable|numeric',
                'items.*.observaciones_item' => 'nullable|string',
            ]);

            Log::info('Iniciando guardado de análisis de boro', [
                'process_id' => $processId,
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);

            // Verificar si ya existe un análisis de boro para este proceso
            $existingAnalysis = BoronAnalysisDetail::where('process_id', $processId)->first();
            
            if ($existingAnalysis) {
                Log::warning('Ya existe un análisis de boro para este proceso, actualizando...', [
                    'process_id' => $processId,
                    'analysis_id' => $existingAnalysis->id
                ]);
            }

            // Preparar datos de controles analíticos
            $controlesAnaliticos = $request->input('controles_analiticos', []);
            $standardA = $controlesAnaliticos[0] ?? [];
            $standardB = $controlesAnaliticos[1] ?? [];

            // Preparar datos de curva de calibración y duplicados
            $curvaData = [
                'value' => 0.995, // Valor fijo de la curva
                'read_value' => $request->input('curva_valor_leido'),
                'error_percentage' => $request->input('curva_error_porcentaje'),
                'acceptability' => $request->input('curva_aceptabilidad')
            ];

            $duplicateData = [
                'a_value' => $request->input('duplicado_a'),
                'b_value' => $request->input('duplicado_b'),
                'dpr_percentage' => $request->input('dpr_resultado'),
                'dpr_acceptability' => $request->input('dpr_aceptabilidad')
            ];

            // Preparar items de ensayo
            $testItems = [];
            $items = $request->input('items', []);

            foreach ($items as $item) {
                $testItems[] = [
                    'internal_code' => $item['codigo_interno'] ?? '',
                    'sample_weight' => $item['peso_muestra'] ?? 0,
                    'pw' => $item['pw'] ?? 0,
                    'extractant_volume' => $item['v_extractante'] ?? 0,
                    'blank_reading' => $item['lectura_blanco'] ?? 0,
                    'dilution_factor' => $item['factor_dilucion'] ?? 0,
                    'available_boron_mg_l' => $item['boro_disponible_mg_l'] ?? 0,
                    'available_boron_mg_kg' => $item['boro_disponible_mg_kg'] ?? 0,
                    'observations' => $item['observaciones_item'] ?? ''
                ];
            }

            // Crear o actualizar el análisis de boro
            $analysisData = [
                'process_id' => (string)$processId,
                'service_id' => $serviceId,
                'consecutive_no' => $request->consecutivo_no,
                'applied_methodology' => $request->metodologia_aplicada,
                'method_interval' => $request->intervalo_metodo,
                'analysis_date' => $request->fecha_analisis,
                'equipment_used' => $request->equipo_utilizado,
                'analyst_name' => $request->analista,
                
                // Controles analíticos - Estándar A
                'standard_a_identification' => $standardA['identificacion'] ?? '',
                'standard_a_expected_value' => $standardA['valor_esperado'] ?? 0,
                'standard_a_read_value' => $standardA['valor_leido'] ?? 0,
                'standard_a_error_percentage' => $standardA['porcentaje_error'] ?? 0,
                'standard_a_error_acceptability' => $standardA['aceptabilidad_error'] ?? '',
                'standard_a_recovery_percentage' => $standardA['porcentaje_recuperacion'] ?? 0,
                'standard_a_recovery_acceptability' => $standardA['aceptabilidad_recuperacion'] ?? '',
                'standard_a_dpr_percentage' => $standardA['porcentaje_dpr'] ?? 0,
                'standard_a_dpr_acceptability' => $standardA['aceptabilidad_dpr'] ?? '',
                
                // Controles analíticos - Estándar B
                'standard_b_identification' => $standardB['identificacion'] ?? '',
                'standard_b_expected_value' => $standardB['valor_esperado'] ?? 0,
                'standard_b_read_value' => $standardB['valor_leido'] ?? 0,
                'standard_b_error_percentage' => $standardB['porcentaje_error'] ?? 0,
                'standard_b_error_acceptability' => $standardB['aceptabilidad_error'] ?? '',
                'standard_b_recovery_percentage' => $standardB['porcentaje_recuperacion'] ?? 0,
                'standard_b_recovery_acceptability' => $standardB['aceptabilidad_recuperacion'] ?? '',
                'standard_b_dpr_percentage' => $standardB['porcentaje_dpr'] ?? 0,
                'standard_b_dpr_acceptability' => $standardB['aceptabilidad_dpr'] ?? '',
                
                // Curva de calibración
                'calibration_curve_value' => $curvaData['value'],
                'calibration_curve_read_value' => $curvaData['read_value'],
                'calibration_curve_error_percentage' => $curvaData['error_percentage'],
                'calibration_curve_acceptability' => $curvaData['acceptability'],
                
                // Duplicados
                'duplicate_a_value' => $duplicateData['a_value'],
                'duplicate_b_value' => $duplicateData['b_value'],
                'duplicate_dpr_percentage' => $duplicateData['dpr_percentage'],
                'duplicate_dpr_acceptability' => $duplicateData['dpr_acceptability'],
                
                // Items de ensayo
                'test_items' => $testItems,
                
                // Observaciones generales
                'general_observations' => $request->observaciones ?? ''
            ];

            Log::info('Guardando análisis de boro detallado', [
                'process_id' => $processId,
                'total_items' => count($testItems),
                'analysis_data_keys' => array_keys($analysisData)
            ]);

            if ($existingAnalysis) {
                $existingAnalysis->update($analysisData);
                $boronAnalysis = $existingAnalysis;
                Log::info('Análisis de boro actualizado con ID: ' . $boronAnalysis->id);
            } else {
                $boronAnalysis = BoronAnalysisDetail::create($analysisData);
                Log::info('Análisis de boro creado con ID: ' . $boronAnalysis->id);
            }

            // Actualizar el estado del servicio a 'completed'
            $boronService = Service::whereRaw('LOWER(descripcion) LIKE ?', ['%boro%'])
                                ->orWhereRaw('LOWER(descripcion) LIKE ?', ['%boron%'])
                                ->first();

            Log::info('Buscando servicio de boro', [
                'process_id' => $processId,
                'boron_service_found' => $boronService ? true : false,
                'service_id' => $boronService ? $boronService->services_id : null,
                'service_descripcion' => $boronService ? $boronService->descripcion : null
            ]);

            if ($boronService) {
                // Verificar que el ServiceProcessDetail existe antes de actualizar
                $serviceProcessDetail = ServiceProcessDetail::where('process_id', $processId)
                    ->where('service_id', $boronService->services_id)
                    ->first();

                if ($serviceProcessDetail) {
                    $updatedRows = ServiceProcessDetail::where('process_id', $processId)
                        ->where('service_id', $boronService->services_id)
                        ->update([
                            'status' => 'completed',
                            'result' => 'Análisis de boro completado',
                            'observations' => 'Análisis guardado exitosamente con ' . count($boronAnalyses) . ' muestras'
                        ]);

                    Log::info('Actualización del estado del servicio', [
                        'process_id' => $processId,
                        'service_id' => $boronService->services_id,
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
                        'service_id' => $boronService->services_id
                    ]);
                }
            } else {
                Log::warning('No se encontró el servicio de boro para actualizar estado');
            }

            DB::commit();

            Log::info('Análisis de boro guardado exitosamente', [
                'user_id' => Auth::id(),
                'process_id' => $processId,
                'analysis_id' => $boronAnalysis->id,
                'total_items' => count($testItems)
            ]);

            return redirect()->route('lscefa.technical.analyses.boron.index')
                ->with('success', 'Análisis de boro guardado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar análisis de boro', [
                'user_id' => Auth::id(),
                'process_id' => $processId,
                'error' => $e->getMessage()
            ]);

            return back()->withInput()->with('error', 'Error al guardar el análisis de boro: ' . $e->getMessage());
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
            return redirect()->route('lscefa.technical.analyses.boron.index')
                ->with('error', 'No se seleccionaron procesos para procesar.');
        }

        // Obtener solo los procesos seleccionados que estén pendientes
        $pendingProcesses = Process::with(['serviceProcessDetails.service'])
            ->whereIn('process_id', $selectedProcessIds)
            ->whereHas('serviceProcessDetails', function ($query) {
                $query->whereHas('service', function ($serviceQuery) {
                    $serviceQuery->where(function($q) {
                        $q->whereRaw('LOWER(descripcion) LIKE ?', ['%boro%'])
                          ->orWhereRaw('LOWER(descripcion) LIKE ?', ['%boron%']);
                    });
                })->where('status', 'pending');
            })
            ->get();

        // Verificar que todos los procesos seleccionados se encontraron
        if ($pendingProcesses->count() !== count($selectedProcessIds)) {
            $foundIds = $pendingProcesses->pluck('process_id')->toArray();
            $missingIds = array_diff($selectedProcessIds, $foundIds);
            
            if (!empty($missingIds)) {
                return redirect()->route('lscefa.technical.analyses.boron.index')
                    ->with('error', 'Algunos procesos seleccionados no están disponibles: ' . implode(', ', $missingIds));
            }
        }

        return view('lscefa::analyses.boron.batch_process', compact('pendingProcesses'));
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
                'items_ensayo.*.boro_disponible_mg_l' => 'nullable|numeric|min:0',
                'items_ensayo.*.boro_disponible_mg_kg' => 'nullable|numeric',
                'items_ensayo.*.observaciones_item' => 'nullable|string',
            ]);

            Log::info('Iniciando guardado de análisis de boro por lotes', [
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

                    // Buscar el servicio de boro para este proceso
                    $boronService = Service::whereRaw('LOWER(descripcion) LIKE ?', ['%boro%'])
                                        ->orWhereRaw('LOWER(descripcion) LIKE ?', ['%boron%'])
                                        ->first();

                    if (!$boronService) {
                        Log::warning('No se encontró el servicio de boro', ['process_id' => $processId]);
                        $errors[] = "Proceso {$processId}: No se encontró el servicio de boro";
                        continue;
                    }

                    $serviceId = $boronService->services_id;

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

                    // Guardar múltiples análisis de boro (uno por cada fila de resultados)
                    $boronAnalyses = [];
                    $items = $request->input("items_ensayo.{$index}", []);

                    Log::info('Guardando análisis de boro para proceso', [
                        'process_id' => $processId,
                        'total_rows' => count($items),
                        'items' => $items
                    ]);

                    foreach ($items as $itemIndex => $item) {
                        $analysisData = [
                            'process_id' => (string)$processId,
                            'service_id' => $serviceId,
                            'consecutive_no' => $consecutivoNo,
                            'analysis_date' => $fechaAnalisis,
                            'equipment_used' => $request->equipo_utilizado ?? '',
                            'method_interval' => $request->intervalo_metodo ?? '',
                            'analyst_name' => $request->nombre_analista ?? '',
                            'observations' => $request->observaciones ?? '',
                            'internal_code' => $item['codigo_interno'] ?? '',
                            'sample_weight' => $item['peso_muestra'] ?? 0,
                            'pw' => $item['pw'] ?? 0,
                            'extractant_volume' => $item['v_extractante'] ?? 0,
                            'blank_reading' => $item['lectura_blanco'] ?? 0,
                            'dilution_factor' => $item['factor_dilucion'] ?? 0,
                            'available_boron_mg_l' => $item['boro_disponible_mg_l'] ?? 0,
                            'available_boron_mg_kg' => $item['boro_disponible_mg_kg'] ?? 0,
                            'item_observations' => $item['observaciones_item'] ?? '',
                        ];

                        Log::info("Creando análisis {$itemIndex} para proceso {$processId}", $analysisData);

                        $boronAnalysis = BoronAnalysis::create($analysisData);
                        $boronAnalyses[] = $boronAnalysis;
                        
                        Log::info("Análisis {$itemIndex} creado con ID: {$boronAnalysis->id}");
                    }

                    // Actualizar el estado del servicio a 'completed'
                    $serviceProcessDetail = ServiceProcessDetail::where('process_id', $processId)
                        ->where('service_id', $boronService->services_id)
                        ->first();

                    if ($serviceProcessDetail) {
                        $updatedRows = ServiceProcessDetail::where('process_id', $processId)
                            ->where('service_id', $boronService->services_id)
                            ->update([
                                'status' => 'completed',
                                'result' => 'Análisis de boro completado',
                                'observations' => 'Análisis guardado exitosamente con ' . count($boronAnalyses) . ' muestras'
                            ]);

                        Log::info('Actualización del estado del servicio', [
                            'process_id' => $processId,
                            'service_id' => $boronService->services_id,
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
                            'service_id' => $boronService->services_id
                        ]);
                    }

                    $savedCount++;

                    Log::info('Análisis de boro guardado exitosamente en lote', [
                        'process_id' => $processId,
                        'analyses_count' => count($boronAnalyses),
                        'analytical_control_id' => $analyticalControl ? $analyticalControl->id : null
                    ]);

                } catch (\Exception $e) {
                    Log::error('Error al guardar análisis de boro en lote', [
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

            $message = "Se guardaron exitosamente {$savedCount} análisis de boro.";
            if (!empty($errors)) {
                $message .= " Errores: " . implode(', ', $errors);
            }

            return redirect()->route('lscefa.technical.analyses.boron.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar análisis de boro por lotes', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return back()->withInput()->with('error', 'Error al guardar análisis de boro por lotes: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $boronAnalysis = BoronAnalysis::findOrFail($id);
        return view('lscefa::analyses.boron.show', compact('boronAnalysis'));
    }

    public function edit($id)
    {
        $boronAnalysis = BoronAnalysis::findOrFail($id);
        return view('lscefa::analyses.boron.edit', compact('boronAnalysis'));
    }

    public function update(Request $request, $id)
    {
        $boronAnalysis = BoronAnalysis::findOrFail($id);
        
        $request->validate([
            'consecutive_no' => 'required|string',
            'analysis_date' => 'required|date',
            'equipment_used' => 'nullable|string',
            'method_interval' => 'nullable|string',
            'analyst_name' => 'nullable|string',
            'observations' => 'nullable|string',
            'internal_code' => 'nullable|string',
            'sample_weight' => 'nullable|numeric|min:0',
            'pw' => 'nullable|numeric|min:0',
            'extractant_volume' => 'nullable|numeric|min:0',
            'blank_reading' => 'nullable|numeric|min:0',
            'dilution_factor' => 'nullable|numeric|min:0',
            'available_boron_mg_l' => 'nullable|numeric|min:0',
            'available_boron_mg_kg' => 'nullable|numeric',
            'item_observations' => 'nullable|string',
        ]);

        $boronAnalysis->update($request->all());

        return redirect()->route('lscefa.technical.analyses.boron.index')
            ->with('success', 'Análisis de boro actualizado exitosamente.');
    }

    public function destroy($id)
    {
        $boronAnalysis = BoronAnalysis::findOrFail($id);
        $boronAnalysis->delete();

        return redirect()->route('lscefa.technical.analyses.boron.index')
            ->with('success', 'Análisis de boro eliminado exitosamente.');
    }

    public function report($id)
    {
        $boronAnalysis = BoronAnalysis::findOrFail($id);
        return view('lscefa::analyses.boron.report', compact('boronAnalysis'));
    }
}
