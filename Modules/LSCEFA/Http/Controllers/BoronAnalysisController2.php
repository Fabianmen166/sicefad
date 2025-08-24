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

class BoronAnalysisController2 extends Controller
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
                'items_ensayo' => 'required|array',
                'items_ensayo.*.codigo_interno' => 'nullable|string',
                'items_ensayo.*.peso_muestra' => 'nullable|numeric|min:0',
                'items_ensayo.*.pw' => 'nullable|numeric|min:0',
                'items_ensayo.*.v_extractante' => 'nullable|numeric|min:0',
                'items_ensayo.*.lectura_blanco' => 'nullable|numeric|min:0',
                'items_ensayo.*.factor_dilucion' => 'nullable|numeric|min:0',
                'items_ensayo.*.boro_disponible_mg_l' => 'nullable|numeric|min:0',
                'items_ensayo.*.boro_disponible_mg_kg' => 'nullable|numeric',
                'items_ensayo.*.observaciones' => 'nullable|string',
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

            // Preparar datos de controles analíticos (enfoque robusto)
            // No acceder directamente a índices que pueden no existir

            // Preparar datos de curva de calibración y duplicados
            $curvaData = [
                'value' => $request->input('curva_valor', 0.995), // Permitir entrada del usuario
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
            $items = $request->input('items_ensayo', []);

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
                    'observations' => $item['observaciones'] ?? ''
                ];
            }

            // Obtener controles analíticos de manera segura
            $controlesAnaliticos = $request->input('controles_analiticos', []);
            
            // Buscar Estándar A y B por identificación en lugar de por índice
            $standardA = null;
            $standardB = null;
            
            foreach ($controlesAnaliticos as $control) {
                if (isset($control['identificacion'])) {
                    if (stripos($control['identificacion'], 'estándar a') !== false || 
                        stripos($control['identificacion'], 'standard a') !== false ||
                        stripos($control['identificacion'], 'estandar a') !== false) {
                        $standardA = $control;
                    } elseif (stripos($control['identificacion'], 'estándar b') !== false || 
                             stripos($control['identificacion'], 'standard b') !== false ||
                             stripos($control['identificacion'], 'estandar b') !== false) {
                        $standardB = $control;
                    }
                }
            }
            
            // Si no se encontraron por identificación, usar los primeros dos controles disponibles
            if (!$standardA && isset($controlesAnaliticos[0])) {
                $standardA = $controlesAnaliticos[0];
            }
            if (!$standardB && isset($controlesAnaliticos[1])) {
                $standardB = $controlesAnaliticos[1];
            }

            // Crear o actualizar el análisis de boro
            $analysisData = [
                'process_id' => (string)$processId,
                'service_id' => $serviceId,
                'consecutivo_no' => $request->input('consecutivo_no'),
                'applied_methodology' => $request->input('metodologia_aplicada'),
                'method_interval' => $request->input('intervalo_metodo'),
                'analysis_date' => $request->input('fecha_analisis'),
                'equipment_used' => $request->input('equipo_utilizado'),
                'analyst_name' => $request->input('analista'),
                
                // Controles analíticos - Estándar A (enfoque robusto)
                'standard_a_identification' => $standardA ? ($standardA['identificacion'] ?? 'Estándar A') : 'Estándar A',
                'standard_a_expected_value' => $standardA ? ($standardA['valor_esperado'] ?? 0) : 0,
                'standard_a_read_value' => $standardA ? ($standardA['valor_leido'] ?? 0) : 0,
                'standard_a_error_percentage' => $standardA ? ($standardA['porcentaje_error'] ?? 0) : 0,
                'standard_a_error_acceptability' => $standardA ? ($standardA['aceptabilidad_error'] ?? '') : '',
                'standard_a_recovery_percentage' => $standardA ? ($standardA['porcentaje_recuperacion'] ?? 0) : 0,
                'standard_a_recovery_acceptability' => $standardA ? ($standardA['aceptabilidad_recuperacion'] ?? '') : '',
                'standard_a_dpr_percentage' => $standardA ? ($standardA['porcentaje_dpr'] ?? 0) : 0,
                'standard_a_dpr_acceptability' => $standardA ? ($standardA['aceptabilidad_dpr'] ?? '') : '',
                
                // Controles analíticos - Estándar B (enfoque robusto)
                'standard_b_identification' => $standardB ? ($standardB['identificacion'] ?? 'Estándar B') : 'Estándar B',
                'standard_b_expected_value' => $standardB ? ($standardB['valor_esperado'] ?? 0) : 0,
                'standard_b_read_value' => $standardB ? ($standardB['valor_leido'] ?? 0) : 0,
                'standard_b_error_percentage' => $standardB ? ($standardB['porcentaje_error'] ?? 0) : 0,
                'standard_b_error_acceptability' => $standardB ? ($standardB['aceptabilidad_error'] ?? '') : '',
                'standard_b_recovery_percentage' => $standardB ? ($standardB['porcentaje_recuperacion'] ?? 0) : 0,
                'standard_b_recovery_acceptability' => $standardB ? ($standardB['aceptabilidad_recuperacion'] ?? '') : '',
                'standard_b_dpr_percentage' => $standardB ? ($standardB['porcentaje_dpr'] ?? 0) : 0,
                'standard_b_dpr_acceptability' => $standardB ? ($standardB['aceptabilidad_dpr'] ?? '') : '',
                
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
                'general_observations' => $request->input('observaciones'),
                
                // Estado de revisión
                'review_status' => 'pending'
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
                            'observations' => 'Análisis guardado exitosamente con ' . count($testItems) . ' muestras'
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
        // LOGGING DETALLADO PARA DEBUGGING
        Log::info('=== INICIO BATCHSTORE - DATOS COMPLETOS DEL REQUEST ===');
        Log::info('Request completo:', $request->all());
        Log::info('Controles analíticos:', $request->input('controles_analiticos'));
        Log::info('Items ensayo:', $request->input('items_ensayo'));
        Log::info('Process IDs:', $request->input('process_ids'));
        Log::info('=== FIN DATOS DEL REQUEST ===');
        
        try {
            DB::beginTransaction();

            // LOGGING ANTES DE LA VALIDACIÓN
            Log::info('=== ANTES DE VALIDACIÓN ===');
            Log::info('Datos a validar:', [
                'controles_analiticos' => $request->input('controles_analiticos'),
                'items_ensayo' => $request->input('items_ensayo'),
                'process_ids' => $request->input('process_ids')
            ]);

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
                'controles_analiticos' => 'nullable|array', // Cambiar a nullable
                'controles_analiticos.*.identificacion' => 'nullable|string',
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
                'items_ensayo.*.observaciones' => 'nullable|string',
            ]);
            
            // LOGGING DESPUÉS DE LA VALIDACIÓN
            Log::info('=== DESPUÉS DE VALIDACIÓN ===');
            Log::info('Validación exitosa', ['status' => 'success']);

            Log::info('Iniciando guardado de análisis de boro por lotes', [
                'user_id' => Auth::id(),
                'total_processes' => count($request->process_ids),
                'request_data' => $request->all()
            ]);

            // Proporcionar valores por defecto para campos requeridos
            // Usar el consecutivo del formulario si está disponible
            $consecutivoNo = $request->input('consecutivo_no');
            if (empty($consecutivoNo)) {
                $consecutivoNo = 'BATCH-' . date('Ymd-His');
            }
            $fechaAnalisis = $request->input('fecha_analisis') ?: date('Y-m-d');

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
                            'controles_analiticos' => $request->input('controles_analiticos', []),
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
                            'controles_analiticos' => $request->input('controles_analiticos', []),
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

                                         // Obtener items de ensayo - LÓGICA CORREGIDA
                     // El formulario envía items_ensayo como array simple, no como items_ensayo.{index}
                     $items = $request->input('items_ensayo', []);
                     
                     // Logging para debug
                     Log::info('Items de ensayo recibidos:', [
                         'items_ensayo_raw' => $items,
                         'tipo' => gettype($items),
                         'es_array' => is_array($items),
                         'count' => is_array($items) ? count($items) : 'NO_ARRAY'
                     ]);
                     
                     // Asegurar que items sea un array
                     if (!is_array($items)) {
                         $items = [];
                     }
                     
                     // Si no hay items, crear uno por defecto para evitar testItems vacío
                     if (empty($items)) {
                         Log::warning('No se recibieron items de ensayo, creando item por defecto');
                         $items = [
                             [
                                 'codigo_interno' => 'MUESTRA-DEFAULT',
                                 'peso_muestra' => 0,
                                 'pw' => 0,
                                 'v_extractante' => 0,
                                 'lectura_blanco' => 0,
                                 'factor_dilucion' => 0,
                                 'boro_disponible_mg_l' => 0,
                                 'boro_disponible_mg_kg' => 0,
                                 'observaciones' => 'Item creado automáticamente'
                             ]
                         ];
                     }

                    Log::info('Guardando análisis de boro para proceso', [
                        'process_id' => $processId,
                        'total_rows' => count($items),
                         'items' => $items,
                         'index_usado' => $index
                     ]);

                                         // Preparar datos para BoronAnalysisDetail (análisis completo con controles)
                     $testItems = [];
                     if (is_array($items)) {
                         foreach ($items as $item) {
                             if (is_array($item)) {
                                 $testItems[] = [
                                     'internal_code' => $item['codigo_interno'] ?? '',
                                     'sample_weight' => $item['peso_muestra'] ?? 0,
                                     'pw' => $item['pw'] ?? 0,
                                     'extractant_volume' => $item['v_extractante'] ?? 0,
                                     'blank_reading' => $item['lectura_blanco'] ?? 0,
                                     'dilution_factor' => $item['factor_dilucion'] ?? 0,
                                     'available_boron_mg_l' => $item['boro_disponible_mg_l'] ?? 0,
                                     'available_boron_mg_kg' => $item['boro_disponible_mg_kg'] ?? 0,
                                     'observations' => $item['observaciones'] ?? ''
                                 ];
                             }
                         }
                     } else {
                         // Si items no es un array, crear un item por defecto
                         $testItems[] = [
                             'internal_code' => $items['codigo_interno'] ?? 'MUESTRA-DEFAULT',
                             'sample_weight' => $items['peso_muestra'] ?? 0,
                             'pw' => $items['pw'] ?? 0,
                             'extractant_volume' => $items['v_extractante'] ?? 0,
                             'blank_reading' => $items['lectura_blanco'] ?? 0,
                             'dilution_factor' => $items['factor_dilucion'] ?? 0,
                             'available_boron_mg_l' => $items['boro_disponible_mg_l'] ?? 0,
                             'available_boron_mg_kg' => $items['boro_disponible_mg_kg'] ?? 0,
                             'observations' => $items['observaciones'] ?? 'Item por defecto'
                         ];
                     }
                     
                                         // LOGGING CRÍTICO PARA TESTITEMS
                    Log::info('=== TESTITEMS CREADOS ===');
                    Log::info('TestItems count:', ['count' => count($testItems)]);
                    Log::info('TestItems content:', ['testItems' => $testItems]);
                    Log::info('=== FIN TESTITEMS ===');
                    
                    // Definir observaciones del primer item AQUÍ para evitar error de variable indefinida
                    $firstItemObservations = !empty($testItems) ? ($testItems[0]['observations'] ?? '') : '';

                                         // LOGGING ESPECÍFICO PARA CONTROLES ANALÍTICOS (ENFOQUE SEGURO)
                     Log::info("=== PROCESANDO PROCESO {$processId} ===");
                     
                     // Obtener controles de manera segura para logging
                     $controlesParaLog = $request->input('controles_analiticos', []);
                     $controlesCount = count($controlesParaLog);
                     
                     Log::info("Controles analíticos completos:", [
                         'total_controles' => $controlesCount,
                         'data' => $controlesParaLog
                     ]);
                     
                     // Logging seguro sin acceso directo por índice
                     if ($controlesCount > 0) {
                         $primerControl = $controlesParaLog[0] ?? null;
                         $segundoControl = $controlesCount > 1 ? ($controlesParaLog[1] ?? null) : null;
                         
                         Log::info("Primer control:", [
                             'existe' => $primerControl ? 'SÍ' : 'NO',
                             'identificacion' => $primerControl ? ($primerControl['identificacion'] ?? 'NO_IDENTIFICACION') : 'NO_EXISTE'
                         ]);
                         
                         if ($segundoControl) {
                             Log::info("Segundo control:", [
                                 'existe' => 'SÍ',
                                 'identificacion' => $segundoControl['identificacion'] ?? 'NO_IDENTIFICACION'
                             ]);
                         } else {
                             Log::info("Segundo control: NO EXISTE");
                         }
                     } else {
                         Log::info("No hay controles analíticos disponibles");
                     }
                     
                     // LOG ADICIONAL PARA DEBUGGING CRÍTICO (ENFOQUE SEGURO)
                     Log::info("=== DEBUG CRÍTICO: ANTES DE CREAR BORONANALYSISDETAIL ===");
                     Log::info("Tipo de controles_analiticos:", [
                         'type' => gettype($controlesParaLog),
                         'is_array' => is_array($controlesParaLog),
                         'is_null' => is_null($controlesParaLog),
                         'count' => $controlesCount
                     ]);
                     Log::info("Verificación de existencia:", [
                         'has_controles_analiticos' => $request->has('controles_analiticos'),
                         'total_controles' => $controlesCount
                     ]);
                     Log::info("=== FIN DEBUG CRÍTICO ===");
                     
                     // Crear o actualizar BoronAnalysisDetail (análisis completo)
                     $boronAnalysisDetailData = [
                         'process_id' => $processId,
                         'service_id' => $serviceId,
                         'consecutive_no' => $request->input('consecutivo_no'),
                         'applied_methodology' => $request->input('metodologia_aplicada'),
                         'method_interval' => $request->input('intervalo_metodo'),
                         'analysis_date' => $fechaAnalisis,
                         'equipment_used' => $request->input('equipo_utilizado'),
                         'analyst_name' => $request->input('nombre_analista'), // Corregido: nombre_analista en lugar de analista
                     ];
                     
                     // LOGGING CRÍTICO PARA DEBUGGEAR EL PROBLEMA
                     Log::info("=== DEBUG CRÍTICO: ANTES DE CREAR BORONANALYSISDETAIL ===");
                     Log::info("process_id:", ['value' => $processId, 'type' => gettype($processId)]);
                     Log::info("service_id:", ['value' => $serviceId, 'type' => gettype($serviceId)]);
                     Log::info("consecutive_no:", ['value' => $request->input('consecutivo_no'), 'type' => gettype($request->input('consecutivo_no'))]);
                     Log::info("metodologia_aplicada:", ['value' => $request->input('metodologia_aplicada'), 'type' => gettype($request->input('metodologia_aplicada'))]);
                     Log::info("intervalo_metodo:", ['value' => $request->input('intervalo_metodo'), 'type' => gettype($request->input('intervalo_metodo'))]);
                     Log::info("equipo_utilizado:", ['value' => $request->input('equipo_utilizado'), 'type' => gettype($request->input('equipo_utilizado'))]);
                     Log::info("nombre_analista:", ['value' => $request->input('nombre_analista'), 'type' => gettype($request->input('nombre_analista'))]);
                     Log::info("=== FIN DEBUG CRÍTICO ===");
                     
                     // LOGGING PARA DEBUGGEAR CAMPOS
                     Log::info("=== DEBUG CAMPOS BORONANALYSISDETAIL ===");
                     Log::info("consecutive_no:", ['value' => $request->input('consecutivo_no')]);
                     Log::info("metodologia_aplicada:", ['value' => $request->input('metodologia_aplicada')]);
                     Log::info("intervalo_metodo:", ['value' => $request->input('intervalo_metodo')]);
                     Log::info("equipo_utilizado:", ['value' => $request->input('equipo_utilizado')]);
                     Log::info("nombre_analista:", ['value' => $request->input('nombre_analista')]); // Corregido
                     Log::info("observaciones_item:", ['value' => $firstItemObservations]); // CORREGIDO
                     Log::info("=== FIN DEBUG CAMPOS ===");
                     
                     // LOG CRÍTICO: JUSTO ANTES DE LA ASIGNACIÓN PROBLEMÁTICA
                     Log::info("=== MOMENTO CRÍTICO: ANTES DE standard_a_identification ===");
                     Log::info("Intentando acceder a controles_analiticos de manera segura");
                     
                     // Obtener controles analíticos de manera segura
                     $controlesAnaliticos = $request->input('controles_analiticos', []);
                     
                     // Buscar Estándar A y B por identificación en lugar de por índice
                     $standardA = null;
                     $standardB = null;
                     
                     if (is_array($controlesAnaliticos) && !empty($controlesAnaliticos)) {
                         foreach ($controlesAnaliticos as $control) {
                             if (is_array($control) && isset($control['identificacion'])) {
                                 if (stripos($control['identificacion'], 'estándar a') !== false || 
                                     stripos($control['identificacion'], 'standard a') !== false ||
                                     stripos($control['identificacion'], 'estandar a') !== false) {
                                     $standardA = $control;
                                 } elseif (stripos($control['identificacion'], 'estándar b') !== false || 
                                          stripos($control['identificacion'], 'standard b') !== false ||
                                          stripos($control['identificacion'], 'estandar b') !== false) {
                                     $standardB = $control;
                                 }
                             }
                         }
                         
                         // Si no se encontraron por identificación, usar los primeros dos controles disponibles
                         if (!$standardA && isset($controlesAnaliticos[0]) && is_array($controlesAnaliticos[0])) {
                             $standardA = $controlesAnaliticos[0];
                         }
                         if (!$standardB && isset($controlesAnaliticos[1]) && is_array($controlesAnaliticos[1])) {
                             $standardB = $controlesAnaliticos[1];
                         }
                     }
                     
                     // Controles analíticos - Estándar A (enfoque robusto)
                     $boronAnalysisDetailData['standard_a_identification'] = $standardA ? ($standardA['identificacion'] ?? 'Estándar A') : 'Estándar A';
                     $boronAnalysisDetailData['standard_a_expected_value'] = $standardA ? ($standardA['valor_esperado'] ?? 0) : 0;
                     $boronAnalysisDetailData['standard_a_read_value'] = $standardA ? ($standardA['valor_leido'] ?? 0) : 0;
                     $boronAnalysisDetailData['standard_a_error_percentage'] = $standardA ? ($standardA['porcentaje_error'] ?? 0) : 0;
                     $boronAnalysisDetailData['standard_a_error_acceptability'] = $standardA ? ($standardA['aceptabilidad_error'] ?? '') : '';
                     $boronAnalysisDetailData['standard_a_recovery_percentage'] = $standardA ? ($standardA['porcentaje_recuperacion'] ?? 0) : 0;
                     $boronAnalysisDetailData['standard_a_recovery_acceptability'] = $standardA ? ($standardA['aceptabilidad_recuperacion'] ?? '') : '';
                     $boronAnalysisDetailData['standard_a_dpr_percentage'] = $standardA ? ($standardA['porcentaje_dpr'] ?? 0) : 0;
                     $boronAnalysisDetailData['standard_a_dpr_acceptability'] = $standardA ? ($standardA['aceptabilidad_dpr'] ?? '') : '';
                     
                     // Controles analíticos - Estándar B (enfoque robusto)
                     $boronAnalysisDetailData['standard_b_identification'] = $standardB ? ($standardB['identificacion'] ?? 'Estándar B') : 'Estándar B';
                     $boronAnalysisDetailData['standard_b_expected_value'] = $standardB ? ($standardB['valor_esperado'] ?? 0) : 0;
                     $boronAnalysisDetailData['standard_b_read_value'] = $standardB ? ($standardB['valor_leido'] ?? 0) : 0;
                     $boronAnalysisDetailData['standard_b_error_percentage'] = $standardB ? ($standardB['porcentaje_error'] ?? 0) : 0;
                     $boronAnalysisDetailData['standard_b_error_acceptability'] = $standardB ? ($standardB['aceptabilidad_error'] ?? '') : '';
                     $boronAnalysisDetailData['standard_b_recovery_percentage'] = $standardB ? ($standardB['porcentaje_recuperacion'] ?? 0) : 0;
                     $boronAnalysisDetailData['standard_b_recovery_acceptability'] = $standardB ? ($standardB['aceptabilidad_recuperacion'] ?? '') : '';
                     $boronAnalysisDetailData['standard_b_dpr_percentage'] = $standardB ? ($standardB['porcentaje_dpr'] ?? 0) : 0;
                     $boronAnalysisDetailData['standard_b_dpr_acceptability'] = $standardB ? ($standardB['aceptabilidad_dpr'] ?? '') : '';
                     
                     // Curva de calibración
                     $boronAnalysisDetailData['calibration_curve_value'] = 0.995; // Valor fijo
                     $boronAnalysisDetailData['calibration_curve_read_value'] = $request->input("curva_valor_leido") ?? 0;
                     $boronAnalysisDetailData['calibration_curve_error_percentage'] = $request->input("curva_error_porcentaje") ?? 0;
                     $boronAnalysisDetailData['calibration_curve_acceptability'] = $request->input("curva_aceptabilidad") ?? '';
                     
                     // Duplicados
                     $boronAnalysisDetailData['duplicate_a_value'] = $request->input("duplicado_a") ?? 0;
                     $boronAnalysisDetailData['duplicate_b_value'] = $request->input("duplicado_b") ?? 0;
                     $boronAnalysisDetailData['duplicate_dpr_percentage'] = $request->input("dpr_resultado") ?? 0;
                     $boronAnalysisDetailData['duplicate_dpr_acceptability'] = $request->input("dpr_aceptabilidad") ?? '';
                     
                     // Items de ensayo
                     $boronAnalysisDetailData['test_items'] = $testItems;
                     
                     // Observaciones generales - CORREGIDO: usar observaciones_item del primer item
                     $boronAnalysisDetailData['general_observations'] = $firstItemObservations;
                     
                     // Estado de revisión
                     $boronAnalysisDetailData['review_status'] = 'pending';
                     
                     // LOGGING PARA DEBUGGEAR CAMPOS
                     Log::info("=== DEBUG CAMPOS BORONANALYSISDETAIL ===");
                     Log::info("consecutive_no:", ['value' => $request->input('consecutivo_no')]);
                     Log::info("metodologia_aplicada:", ['value' => $request->input('metodologia_aplicada')]);
                     Log::info("intervalo_metodo:", ['value' => $request->input('intervalo_metodo')]);
                     Log::info("equipo_utilizado:", ['value' => $request->input('equipo_utilizado')]);
                     Log::info("nombre_analista:", ['value' => $request->input('nombre_analista')]); // Corregido
                     Log::info("observaciones_item:", ['value' => $firstItemObservations]); // CORREGIDO
                     Log::info("=== FIN DEBUG CAMPOS ===");

                    // Verificar si es un análisis rechazado que se está actualizando
                    $rejectedAnalysisId = $request->input('rejected_analysis_id');
                    
                    if ($rejectedAnalysisId) {
                        // Es un análisis rechazado, actualizarlo
                        $boronAnalysisDetail = BoronAnalysisDetail::findOrFail($rejectedAnalysisId);
                        $boronAnalysisDetail->update($boronAnalysisDetailData);
                        
                        // Cambiar el estado de revisión a 'pending' para que vuelva a revisión
                        $boronAnalysisDetail->update([
                            'review_status' => 'pending',
                            'review_observations' => null,
                            'reviewed_by' => null,
                            'review_date' => null,
                        ]);
                        
                        Log::info('Análisis de boro rechazado actualizado: ' . $rejectedAnalysisId);
                    } else {
                        // Verificar si ya existe un BoronAnalysisDetail para este proceso
                        $existingBoronDetail = BoronAnalysisDetail::where('process_id', $processId)->first();
                        
                        if ($existingBoronDetail) {
                            $existingBoronDetail->update($boronAnalysisDetailData);
                            $boronAnalysisDetail = $existingBoronDetail;
                            Log::info('BoronAnalysisDetail actualizado para proceso: ' . $processId);
                        } else {
                            $boronAnalysisDetail = BoronAnalysisDetail::create($boronAnalysisDetailData);
                            Log::info('BoronAnalysisDetail creado para proceso: ' . $processId . ' con ID: ' . $boronAnalysisDetail->id);
                        }
                    }

                    // Crear solo 1 registro individual en BoronAnalysis por proceso (usando el primer item)
                    if (!empty($testItems)) {
                        $firstItem = $testItems[0];
                        $analysisData = [
                            'process_id' => (string)$processId,
                            'service_id' => $serviceId,
                            'consecutive_no' => $request->input('consecutivo_no'),
                            'analysis_date' => $fechaAnalisis,
                            'equipment_used' => $request->input('equipo_utilizado'),
                            'method_interval' => $request->input('intervalo_metodo'),
                            'analyst_name' => $request->input('nombre_analista'), // Corregido: nombre_analista en lugar de analista
                            'observations' => $firstItemObservations, // CORREGIDO: usar observaciones del primer item
                            'internal_code' => $firstItem['internal_code'] ?? '',
                            'sample_weight' => $firstItem['sample_weight'] ?? 0,
                            'pw' => $firstItem['pw'] ?? 0,
                            'extractant_volume' => $firstItem['extractant_volume'] ?? 0,
                            'blank_reading' => $firstItem['blank_reading'] ?? 0,
                            'dilution_factor' => $firstItem['dilution_factor'] ?? 0,
                            'available_boron_mg_l' => $firstItem['available_boron_mg_l'] ?? 0,
                            'available_boron_mg_kg' => $firstItem['available_boron_mg_kg'] ?? 0,
                            'item_observations' => $firstItem['observations'] ?? '',
                            'review_status' => 'pending', // Estado inicial de revisión
                        ];

                        Log::info("Creando análisis individual para proceso {$processId}", $analysisData);
                        
                        // LOGGING PARA DEBUGGEAR CAMPOS BORONANALYSIS
                        Log::info("=== DEBUG CAMPOS BORONANALYSIS ===");
                        Log::info("consecutive_no:", ['value' => $request->input('consecutivo_no')]);
                        Log::info("equipment_used:", ['value' => $request->input('equipo_utilizado')]);
                        Log::info("method_interval:", ['value' => $request->input('intervalo_metodo')]);
                        Log::info("nombre_analista:", ['value' => $request->input('nombre_analista')]); // Corregido
                        Log::info("observations:", ['value' => $firstItemObservations]); // CORREGIDO
                        Log::info("=== FIN DEBUG CAMPOS BORONANALYSIS ===");

                        $boronAnalysis = BoronAnalysis::create($analysisData);
                        $boronAnalyses = [$boronAnalysis]; // Solo 1 análisis por proceso
                        
                        Log::info("Análisis individual creado con ID: {$boronAnalysis->id}");
                    } else {
                        $boronAnalyses = [];
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
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
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
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
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

    public function editRejected($id)
    {
        $boronAnalysis = BoronAnalysisDetail::findOrFail($id);
        
        // Verificar que el análisis esté rechazado
        if ($boronAnalysis->review_status !== 'rejected') {
            return redirect()->route('lscefa.technical.analyses.boron.index')
                ->with('error', 'Solo se pueden editar análisis rechazados.');
        }
        
        // Obtener el proceso y servicio asociados
        $process = \Modules\LSCEFA\Models\Process::find($boronAnalysis->process_id);
        $service = \Modules\LSCEFA\Models\Service::find($boronAnalysis->service_id);

        // Crear un array de procesos con el proceso del análisis rechazado
        // La vista espera $pendingProcesses, no $processes
        $pendingProcesses = collect([$process]);

        // Pasar el análisis rechazado para que se pueda editar
        return view('lscefa::analyses.boron.batch_process', compact('pendingProcesses', 'boronAnalysis'));
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

    public function updateRejected(Request $request, $id)
    {
        $boronAnalysis = BoronAnalysis::findOrFail($id);
        
        // Verificar que el análisis esté rechazado
        if ($boronAnalysis->review_status !== 'rejected') {
            return redirect()->route('lscefa.technical.analyses.boron.index')
                ->with('error', 'Solo se pueden actualizar análisis rechazados.');
        }
        
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

        try {
            DB::beginTransaction();

            // Actualizar el análisis
            $boronAnalysis->update($request->all());
            
            // Cambiar el estado de revisión a 'pending' para que vuelva a revisión
            $boronAnalysis->update([
                'review_status' => 'pending',
                'review_observations' => null,
                'reviewed_by' => null,
                'review_date' => null,
            ]);

            DB::commit();

            Log::info('Análisis de boro rechazado corregido exitosamente', [
                'analysis_id' => $boronAnalysis->id,
                'user_id' => Auth::id(),
                'new_status' => 'pending'
            ]);

            return redirect()->route('lscefa.technical.analyses.boron.index')
                ->with('success', 'Análisis de boro corregido exitosamente. Ha sido enviado nuevamente para revisión.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al corregir análisis de boro rechazado', [
                'analysis_id' => $boronAnalysis->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return back()->withInput()->with('error', 'Error al corregir el análisis: ' . $e->getMessage());
        }
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

    /**
     * Descarga el informe de análisis de boro en formato Excel
     */
    public function downloadBoronReport($analysisId)
    {
        $boronAnalysis = BoronAnalysisDetail::findOrFail($analysisId);
        
        // Crear el archivo Excel usando PhpSpreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Configurar encabezados del informe
        $sheet->setCellValue('A1', 'LABORATORIO DE CIENCIAS BÁSICAS');
        $sheet->setCellValue('A2', 'PROCEDIMIENTO DETERMINACIÓN DE BORO DISPONIBLE EN SUELOS');
        $sheet->setCellValue('A3', 'FORMATO REPORTE RESULTADOS BORO DISPONIBLE EN SUELOS');
        $sheet->setCellValue('D3', 'Versión: 1');
        $sheet->setCellValue('D4', 'Código: F-BOR-001');
        $sheet->setCellValue('D5', 'Página: 1 de 1');

        // Información general del análisis
        $sheet->setCellValue('A6', 'Consecutivo No.:');
        $sheet->setCellValue('B6', $boronAnalysis->consecutive_no);
        $sheet->setCellValue('A7', 'Fecha del análisis:');
        $sheet->setCellValue('B7', $boronAnalysis->analysis_date);
        $sheet->setCellValue('A8', 'Nombre Analista:');
        $sheet->setCellValue('B8', $boronAnalysis->analyst_name);
        $sheet->setCellValue('A9', 'Metodología Utilizada:');
        $sheet->setCellValue('B9', $boronAnalysis->applied_methodology);
        $sheet->setCellValue('A10', 'Equipo Utilizado:');
        $sheet->setCellValue('B10', $boronAnalysis->equipment_used);
        $sheet->setCellValue('A11', 'Intervalo del Método:');
        $sheet->setCellValue('B11', $boronAnalysis->method_interval);

        // Controles analíticos - Estándar A
        $sheet->setCellValue('A13', 'Controles analíticos - Estándar A');
        $sheet->setCellValue('A14', 'Identificación');
        $sheet->setCellValue('B14', 'Valor esperado');
        $sheet->setCellValue('C14', 'Valor leído');
        $sheet->setCellValue('D14', '% Error');
        $sheet->setCellValue('E14', 'Aceptabilidad del error');
        $sheet->setCellValue('F14', '% Recuperación');
        $sheet->setCellValue('G14', 'Aceptabilidad de recuperación');
        $sheet->setCellValue('H14', '% DPR');
        $sheet->setCellValue('I14', 'Aceptabilidad DPR');

        $sheet->setCellValue('A15', $boronAnalysis->standard_a_identification ?? 'Estándar A');
        $sheet->setCellValue('B15', $boronAnalysis->standard_a_expected_value ?? '');
        $sheet->setCellValue('C15', $boronAnalysis->standard_a_read_value ?? '');
        $sheet->setCellValue('D15', $boronAnalysis->standard_a_error_percentage ?? '');
        $sheet->setCellValue('E15', $boronAnalysis->standard_a_error_acceptability ?? '');
        $sheet->setCellValue('F15', $boronAnalysis->standard_a_recovery_percentage ?? '');
        $sheet->setCellValue('G15', $boronAnalysis->standard_a_recovery_acceptability ?? '');
        $sheet->setCellValue('H15', $boronAnalysis->standard_a_dpr_percentage ?? '');
        $sheet->setCellValue('I15', $boronAnalysis->standard_a_dpr_acceptability ?? '');

        // Controles analíticos - Estándar B
        $sheet->setCellValue('A17', 'Controles analíticos - Estándar B');
        $sheet->setCellValue('A18', 'Identificación');
        $sheet->setCellValue('B18', 'Valor esperado');
        $sheet->setCellValue('C18', 'Valor leído');
        $sheet->setCellValue('D18', '% Error');
        $sheet->setCellValue('E18', 'Aceptabilidad del error');
        $sheet->setCellValue('F18', '% Recuperación');
        $sheet->setCellValue('G18', 'Aceptabilidad de recuperación');
        $sheet->setCellValue('H18', '% DPR');
        $sheet->setCellValue('I18', 'Aceptabilidad DPR');

        $sheet->setCellValue('A19', $boronAnalysis->standard_b_identification ?? 'Estándar B');
        $sheet->setCellValue('B19', $boronAnalysis->standard_b_expected_value ?? '');
        $sheet->setCellValue('C19', $boronAnalysis->standard_b_read_value ?? '');
        $sheet->setCellValue('D19', $boronAnalysis->standard_b_error_percentage ?? '');
        $sheet->setCellValue('E19', $boronAnalysis->standard_b_error_acceptability ?? '');
        $sheet->setCellValue('F19', $boronAnalysis->standard_b_recovery_percentage ?? '');
        $sheet->setCellValue('G19', $boronAnalysis->standard_b_recovery_acceptability ?? '');
        $sheet->setCellValue('H19', $boronAnalysis->standard_b_dpr_percentage ?? '');
        $sheet->setCellValue('I19', $boronAnalysis->standard_b_dpr_acceptability ?? '');

        // Curva de calibración
        $sheet->setCellValue('A21', 'Curva de calibración');
        $sheet->setCellValue('A22', 'Valor esperado');
        $sheet->setCellValue('B22', $boronAnalysis->calibration_curve_value ?? '0.995');
        $sheet->setCellValue('A23', 'Valor leído');
        $sheet->setCellValue('B23', $boronAnalysis->calibration_curve_read_value ?? '');
        $sheet->setCellValue('A24', '% Error');
        $sheet->setCellValue('B24', $boronAnalysis->calibration_curve_error_percentage ?? '');
        $sheet->setCellValue('A25', 'Aceptabilidad');
        $sheet->setCellValue('B25', $boronAnalysis->calibration_curve_acceptability ?? '');

        // Duplicados
        $sheet->setCellValue('A27', 'Duplicados');
        $sheet->setCellValue('A28', 'Duplicado A');
        $sheet->setCellValue('B28', $boronAnalysis->duplicate_a_value ?? '');
        $sheet->setCellValue('A29', 'Duplicado B');
        $sheet->setCellValue('B29', $boronAnalysis->duplicate_b_value ?? '');
        $sheet->setCellValue('A30', '% DPR');
        $sheet->setCellValue('B30', $boronAnalysis->duplicate_dpr_percentage ?? '');
        $sheet->setCellValue('A31', 'Aceptabilidad DPR');
        $sheet->setCellValue('B31', $boronAnalysis->duplicate_dpr_acceptability ?? '');

        // Ítems de ensayo (muestras)
        $sheet->setCellValue('A33', 'Ítems de ensayo');
        $sheet->setCellValue('A34', 'Código interno');
        $sheet->setCellValue('B34', 'Peso muestra (g)');
        $sheet->setCellValue('C34', 'pW');
        $sheet->setCellValue('D34', 'V. Extractante (mL)');
        $sheet->setCellValue('E34', 'Lectura blanco');
        $sheet->setCellValue('F34', 'Factor de dilución (fd)');
        $sheet->setCellValue('G34', 'Boro disponible (mg/L)');
        $sheet->setCellValue('H34', 'Boro disponible (mg/kg)');
        $sheet->setCellValue('I34', 'Observaciones');

        $row = 35;
        $testItems = is_string($boronAnalysis->test_items) ? 
            json_decode($boronAnalysis->test_items, true) : 
            $boronAnalysis->test_items;

        if (is_array($testItems)) {
            foreach ($testItems as $testItem) {
                if (is_array($testItem)) {
                    $sheet->setCellValue('A' . $row, $testItem['internal_code'] ?? '');
                    $sheet->setCellValue('B' . $row, $testItem['sample_weight'] ?? '');
                    $sheet->setCellValue('C' . $row, $testItem['pw'] ?? '');
                    $sheet->setCellValue('D' . $row, $testItem['extractant_volume'] ?? '');
                    $sheet->setCellValue('E' . $row, $testItem['blank_reading'] ?? '');
                    $sheet->setCellValue('F' . $row, $testItem['dilution_factor'] ?? '');
                    $sheet->setCellValue('G' . $row, $testItem['available_boron_mg_l'] ?? '');
                    $sheet->setCellValue('H' . $row, $testItem['available_boron_mg_kg'] ?? '');
                    $sheet->setCellValue('I' . $row, $testItem['observations'] ?? '');
                    $row++;
                }
            }
        }

        // Observaciones generales
        $row += 2;
        $sheet->setCellValue('A' . $row, 'Observaciones generales:');
        $sheet->setCellValue('A' . ($row + 1), $boronAnalysis->general_observations ?? '');

        // Auto-size columns
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Crear el archivo Excel
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'reporte_boro_' . $boronAnalysis->consecutive_no . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
}
