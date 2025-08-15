<?php

namespace Modules\LSCEFA\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

            Log::info('Guardando análisis de fósforo', [
                'total_rows' => count($items),
                'items' => $items
            ]);

            foreach ($items as $index => $item) {
                $analysisData = [
                    'process_id' => (string)$processId,
                    'service_id' => $serviceId,
                    'consecutivo_no' => $request->consecutivo_no,
                    'fecha_analisis' => $request->fecha_analisis,
                    'equipo_utilizado' => $request->equipo_utilizado,
                    'intervalo_metodo' => $request->intervalo_metodo,
                    'nombre_analista' => $request->analista,
                    'observaciones' => $request->observaciones ?? '',
                    'codigo_interno' => $item['codigo_interno'] ?? '',
                    'peso_muestra' => $item['peso_muestra'] ?? 0,
                    'pw' => $item['pw'] ?? 0,
                    'v_extractante' => $item['v_extractante'] ?? 0,
                    'lectura_blanco' => $item['lectura_blanco'] ?? 0,
                    'factor_dilucion' => $item['factor_dilucion'] ?? 0,
                    'fosforo_disponible_mg_l' => $item['fosforo_disponible_mg_l'] ?? 0,
                    'fosforo_disponible_mg_kg' => $item['fosforo_disponible_mg_kg'] ?? 0,
                    'observaciones_item' => $item['observaciones_item'] ?? '',
                ];

                Log::info("Creando análisis {$index}", $analysisData);

                $phosphorusAnalysis = PhosphorusAnalysis::create($analysisData);
                
                $phosphorusAnalyses[] = $phosphorusAnalysis;
                
                Log::info("Análisis {$index} creado con ID: {$phosphorusAnalysis->id}");
            }

            // Actualizar el estado del servicio a 'completed'
            $phosphorusService = Service::whereRaw('LOWER(descripcion) LIKE ?', ['%fósforo%'])
                                    ->orWhereRaw('LOWER(descripcion) LIKE ?', ['%fosforo%'])
                                    ->orWhereRaw('LOWER(descripcion) LIKE ?', ['%phosphorus%'])
                                    ->first();

            Log::info('Buscando servicio de fósforo', [
                'process_id' => $processId,
                'phosphorus_service_found' => $phosphorusService ? true : false,
                'service_id' => $phosphorusService ? $phosphorusService->services_id : null,
                'service_descripcion' => $phosphorusService ? $phosphorusService->descripcion : null
            ]);

            if ($phosphorusService) {
                // Verificar que el ServiceProcessDetail existe antes de actualizar
                $serviceProcessDetail = ServiceProcessDetail::where('process_id', $processId)
                    ->where('service_id', $phosphorusService->services_id)
                    ->first();

                if ($serviceProcessDetail) {
                    $updatedRows = ServiceProcessDetail::where('process_id', $processId)
                        ->where('service_id', $phosphorusService->services_id)
                        ->update([
                            'status' => 'completed',
                            'result' => 'Análisis de fósforo completado',
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
            } else {
                Log::warning('No se encontró el servicio de fósforo para actualizar estado');
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

                    // Guardar múltiples análisis de fósforo (uno por cada fila de resultados)
                    $phosphorusAnalyses = [];
                    $items = $request->input("items_ensayo.{$index}", []);

                    Log::info('Guardando análisis de fósforo para proceso', [
                        'process_id' => $processId,
                        'total_rows' => count($items),
                        'items' => $items
                    ]);

                    foreach ($items as $itemIndex => $item) {
                        $analysisData = [
                            'process_id' => (string)$processId,
                            'service_id' => $serviceId,
                            'consecutivo_no' => $consecutivoNo,
                            'fecha_analisis' => $fechaAnalisis,
                            'equipo_utilizado' => $request->equipo_utilizado ?? '',
                            'intervalo_metodo' => $request->intervalo_metodo ?? '',
                            'nombre_analista' => $request->nombre_analista ?? '',
                            'observaciones' => $request->observaciones ?? '',
                            'codigo_interno' => $item['codigo_interno'] ?? '',
                            'peso_muestra' => $item['peso_muestra'] ?? 0,
                            'pw' => $item['pw'] ?? 0,
                            'v_extractante' => $item['v_extractante'] ?? 0,
                            'lectura_blanco' => $item['lectura_blanco'] ?? 0,
                            'factor_dilucion' => $item['factor_dilucion'] ?? 0,
                            'fosforo_disponible_mg_l' => $item['fosforo_disponible_mg_l'] ?? 0,
                            'fosforo_disponible_mg_kg' => $item['fosforo_disponible_mg_kg'] ?? 0,
                            'observaciones_item' => $item['observaciones_item'] ?? '',
                        ];

                        Log::info("Creando análisis {$itemIndex} para proceso {$processId}", $analysisData);

                        $phosphorusAnalysis = PhosphorusAnalysis::create($analysisData);
                        $phosphorusAnalyses[] = $phosphorusAnalysis;
                        
                        Log::info("Análisis {$itemIndex} creado con ID: {$phosphorusAnalysis->id}");
                    }

                    // Actualizar el estado del servicio a 'completed'
                    $serviceProcessDetail = ServiceProcessDetail::where('process_id', $processId)
                        ->where('service_id', $phosphorusService->services_id)
                        ->first();

                    if ($serviceProcessDetail) {
                        $updatedRows = ServiceProcessDetail::where('process_id', $processId)
                            ->where('service_id', $phosphorusService->services_id)
                            ->update([
                                'status' => 'completed',
                                'result' => 'Análisis de fósforo completado',
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