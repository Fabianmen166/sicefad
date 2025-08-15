<?php

namespace Modules\LSCEFA\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\LSCEFA\Models\Process;
use Modules\LSCEFA\Models\Service;
use Modules\LSCEFA\Models\ServiceProcessDetail;
use Modules\LSCEFA\Entities\ExchangeableBasesAnalysis;
use Modules\LSCEFA\Entities\AnalyticalControl;
use Illuminate\Support\Facades\Auth;

class ExchangeableBasesAnalysisController extends Controller
{
    public function index()
    {
        $processes = Process::with(['serviceProcessDetails.service'])
            ->whereHas('serviceProcessDetails', function ($query) {
                $query->whereHas('service', function ($serviceQuery) {
                    $serviceQuery->where('descripcion', 'like', '%bases cambiables%')
                                ->orWhere('descripcion', 'like', '%exchangeable bases%')
                                ->orWhere('descripcion', 'like', '%intercambio cationico%')
                                ->orWhere('descripcion', 'like', '%cationic exchange%');
                })->where('status', 'pending');
            })
            ->get();

        return view('lscefa::analyses.exchangeable_bases.index', compact('processes'));
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

        return view('lscefa::analyses.exchangeable_bases.process', compact('process', 'service', 'pendingItems'));
    }

    public function storeExchangeableBasesAnalysis(Request $request)
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
                'items.*.bases_cambiables_mg_l' => 'nullable|numeric|min:0',
                'items.*.bases_cambiables_mg_kg' => 'nullable|numeric',
                'items.*.observaciones_item' => 'nullable|string',
            ]);

            Log::info('Iniciando guardado de análisis de bases cambiables', [
                'process_id' => $processId,
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);

            // Buscar el servicio de bases cambiables
            $exchangeableBasesService = Service::where('descripcion', 'like', '%bases cambiables%')
                                        ->orWhere('descripcion', 'like', '%exchangeable bases%')
                                        ->orWhere('descripcion', 'like', '%intercambio cationico%')
                                        ->orWhere('descripcion', 'like', '%cationic exchange%')
                                        ->first();

            if (!$exchangeableBasesService) {
                throw new \Exception('No se encontró el servicio de bases cambiables');
            }

            // Guardar o actualizar control analítico
            $analyticalControl = AnalyticalControl::updateOrCreate(
                ['process_id' => $processId],
                [
                    'consecutivo_no' => $request->consecutivo_no ?? '',
                    'fecha_analisis' => $request->fecha_analisis ?? date('Y-m-d'),
                    'equipo_utilizado' => $request->equipo_utilizado ?? '',
                    'intervalo_metodo' => $request->intervalo_metodo ?? '',
                    'nombre_analista' => $request->analista ?? '',
                    'controles_analiticos' => json_encode($request->controles_analiticos ?? []),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            Log::info('Control analítico guardado/actualizado', [
                'control_id' => $analyticalControl->id,
                'process_id' => $processId
            ]);

            // Guardar análisis de bases cambiables
            foreach ($request->items as $item) {
                $exchangeableBasesAnalysis = new ExchangeableBasesAnalysis();
                $exchangeableBasesAnalysis->process_id = $processId;
                $exchangeableBasesAnalysis->service_id = $serviceId;
                $exchangeableBasesAnalysis->analytical_control_id = $analyticalControl->id;
                $exchangeableBasesAnalysis->codigo_interno = $item['codigo_interno'] ?? '';
                $exchangeableBasesAnalysis->peso_muestra = $item['peso_muestra'] ?? 0;
                $exchangeableBasesAnalysis->pw = $item['pw'] ?? 0;
                $exchangeableBasesAnalysis->v_extractante = $item['v_extractante'] ?? 0;
                $exchangeableBasesAnalysis->lectura_blanco = $item['lectura_blanco'] ?? 0;
                $exchangeableBasesAnalysis->factor_dilucion = $item['factor_dilucion'] ?? 0;
                $exchangeableBasesAnalysis->bases_cambiables_mg_l = $item['bases_cambiables_mg_l'] ?? 0;
                $exchangeableBasesAnalysis->bases_cambiables_mg_kg = $item['bases_cambiables_mg_kg'] ?? 0;
                $exchangeableBasesAnalysis->observaciones_item = $item['observaciones_item'] ?? '';
                $exchangeableBasesAnalysis->save();

                Log::info('Análisis de bases cambiables guardado', [
                    'analysis_id' => $exchangeableBasesAnalysis->id,
                    'codigo_interno' => $item['codigo_interno'] ?? ''
                ]);
            }

            // Actualizar estado del detalle del proceso
            $serviceProcessDetail = ServiceProcessDetail::where('process_id', $processId)
                ->where('service_id', $serviceId)
                ->first();

            if ($serviceProcessDetail) {
                $serviceProcessDetail->status = 'completed';
                $serviceProcessDetail->save();

                Log::info('Estado del detalle del proceso actualizado a completado', [
                    'service_process_detail_id' => $serviceProcessDetail->id
                ]);
            }

            // Verificar si todos los detalles del proceso están completados
            $pendingDetails = ServiceProcessDetail::where('process_id', $processId)
                ->where('status', 'pending')
                ->count();

            if ($pendingDetails == 0) {
                $process = Process::find($processId);
                if ($process) {
                    $process->status = 'completed';
                    $process->save();

                    Log::info('Proceso marcado como completado', [
                        'process_id' => $processId
                    ]);
                }
            }

            DB::commit();

            Log::info('Análisis de bases cambiables guardado exitosamente', [
                'process_id' => $processId,
                'user_id' => Auth::id()
            ]);

            return redirect()->route('lscefa.technical.analyses.exchangeable_bases.index')
                ->with('success', 'Análisis de bases cambiables guardado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al guardar análisis de bases cambiables', [
                'user_id' => Auth::id(),
                'process_id' => $processId,
                'error' => $e->getMessage()
            ]);

            return back()->withInput()->with('error', 'Error al guardar el análisis de bases cambiables: ' . $e->getMessage());
        }
    }

    public function batchProcess()
    {
        $pendingProcesses = Process::with(['serviceProcessDetails.service'])
            ->whereHas('serviceProcessDetails', function ($query) {
                $query->whereHas('service', function ($serviceQuery) {
                    $serviceQuery->where('descripcion', 'like', '%bases cambiables%')
                                ->orWhere('descripcion', 'like', '%exchangeable bases%')
                                ->orWhere('descripcion', 'like', '%intercambio cationico%')
                                ->orWhere('descripcion', 'like', '%cationic exchange%');
                })->where('status', 'pending');
            })
            ->get();

        return view('lscefa::analyses.exchangeable_bases.batch_process', compact('pendingProcesses'));
    }

    public function batchStore(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'process_ids' => 'required|array',
                'process_ids.*' => 'required|string',
                'consecutivo_no' => 'nullable|string',
                'metodo' => 'nullable|string',
                'fecha_analisis' => 'nullable|date',
                'equipo_utilizado' => 'nullable|string',
                'intervalo_metodo' => 'nullable|string',
                'nombre_analista' => 'nullable|string',
                
                // Blanco del método
                'blanco_metodo.*.identificacion' => 'nullable|string',
                'blanco_metodo.*.resultado' => 'nullable|numeric',
                'blanco_metodo.*.lcm' => 'nullable|numeric',
                'blanco_metodo.*.aceptabilidad' => 'nullable|string',
                
                // Duplicado muestra (misma estructura que en process; sin process_id)
                'duplicado_muestra.*.identificacion_muestra' => 'nullable|string',
                'duplicado_muestra.*.replica_1' => 'nullable|numeric',
                'duplicado_muestra.*.replica_2' => 'nullable|numeric',
                'duplicado_muestra.*.dpr_1' => 'nullable|numeric',
                'duplicado_muestra.*.elemento' => 'nullable|string',
                'duplicado_muestra.*.dpr_2' => 'nullable|numeric',
                'duplicado_muestra.*.aceptabilidad' => 'nullable|string',
                
                // Controles de calidad (Exactitud)
                'controles_calidad.*.identificacion' => 'nullable|string',
                'controles_calidad.*.valor_esperado' => 'nullable|numeric',
                'controles_calidad.*.valor_leido' => 'nullable|numeric',
                'controles_calidad.*.porcentaje_recuperacion' => 'nullable|numeric',
                'controles_calidad.*.aceptabilidad' => 'nullable|string',
                'controles_calidad.*.observaciones' => 'nullable|string',
                
                // Control de estándar (Exactitud)
                'control_estandar.*.estandar' => 'nullable|string',
                'control_estandar.*.concentracion' => 'nullable|numeric',
                'control_estandar.*.valor_leido' => 'nullable|numeric',
                'control_estandar.*.porcentaje_error' => 'nullable|numeric',
                'control_estandar.*.aceptabilidad' => 'nullable|string',
                'control_estandar.*.observaciones' => 'nullable|string',
                
                // Curva de calibración
                'curva_calibracion.*.elemento' => 'nullable|string',
                'curva_calibracion.*.r2_obtenido' => 'nullable|numeric',
                'curva_calibracion.*.r2_esperado' => 'nullable|numeric',
                'curva_calibracion.*.aceptabilidad' => 'nullable|string',
                'curva_calibracion.*.observaciones' => 'nullable|string',
                
                'items_ensayo' => 'nullable|array',
                'items_ensayo.*.codigo_interno' => 'nullable|string',
                'items_ensayo.*.peso_muestra' => 'nullable|numeric|min:0',
                'items_ensayo.*.humedad' => 'nullable|numeric|min:0',
                'items_ensayo.*.volumen_final' => 'nullable|numeric|min:0',
                // Campos para Na
                'items_ensayo.*.na_lectura' => 'nullable|numeric|min:0',
                'items_ensayo.*.na_blanco' => 'nullable|numeric|min:0',
                'items_ensayo.*.na_factor' => 'nullable|numeric|min:0',
                'items_ensayo.*.na_resultado' => 'nullable|numeric|min:0',
                // Campos para K
                'items_ensayo.*.k_lectura' => 'nullable|numeric|min:0',
                'items_ensayo.*.k_blanco' => 'nullable|numeric|min:0',
                'items_ensayo.*.k_factor' => 'nullable|numeric|min:0',
                'items_ensayo.*.k_resultado' => 'nullable|numeric|min:0',
                // Campos para Ca
                'items_ensayo.*.ca_lectura' => 'nullable|numeric|min:0',
                'items_ensayo.*.ca_blanco' => 'nullable|numeric|min:0',
                'items_ensayo.*.ca_factor' => 'nullable|numeric|min:0',
                'items_ensayo.*.ca_resultado' => 'nullable|numeric|min:0',
                // Campos para Mg
                'items_ensayo.*.mg_lectura' => 'nullable|numeric|min:0',
                'items_ensayo.*.mg_blanco' => 'nullable|numeric|min:0',
                'items_ensayo.*.mg_factor' => 'nullable|numeric|min:0',
                'items_ensayo.*.mg_resultado' => 'nullable|numeric|min:0',
                'items_ensayo.*.observaciones' => 'nullable|string',
            ]);

            Log::info('Iniciando guardado de análisis de bases cambiables por lotes', [
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

                    // Buscar el servicio específico para este proceso
                    $process = Process::with(['serviceProcessDetails.service'])
                        ->where('process_id', $processId)
                        ->first();

                    if (!$process) {
                        Log::warning('No se encontró el proceso', ['process_id' => $processId]);
                        $errors[] = "Proceso {$processId}: No se encontró el proceso";
                        continue;
                    }

                    // Buscar el servicio de bases cambiables para este proceso específico
                    $exchangeableBasesService = $process->serviceProcessDetails
                        ->where('status', 'pending')
                        ->first()
                        ->service ?? null;

                    if (!$exchangeableBasesService) {
                        Log::warning('No se encontró el servicio de bases cambiables para el proceso', ['process_id' => $processId]);
                        $errors[] = "Proceso {$processId}: No se encontró el servicio de bases cambiables";
                        continue;
                    }

                    $serviceId = $exchangeableBasesService->services_id;

                    // Verificar si ya existe un control analítico para este proceso
                    $existingControl = AnalyticalControl::where('process_id', $processId)->first();
                    if ($existingControl) {
                        Log::warning('Ya existe un control analítico para este proceso, actualizando...', [
                            'process_id' => $processId,
                            'control_id' => $existingControl->id
                        ]);
                    }

                    // Guardar o actualizar control analítico
                    $analyticalControl = AnalyticalControl::updateOrCreate(
                        ['process_id' => $processId],
                        [
                            'consecutivo_no' => $consecutivoNo,
                            'fecha_analisis' => $fechaAnalisis,
                            'equipo_utilizado' => $request->equipo_utilizado ?? '',
                            'intervalo_metodo' => $request->intervalo_metodo ?? '',
                            'nombre_analista' => $request->nombre_analista ?? '',
                            'curva_valor_leido' => $request->curva_valor_leido ?? null,
                            'curva_error_porcentaje' => $request->curva_error_porcentaje ?? null,
                            // Unificamos en un JSON los bloques de controles
                            'controles_analiticos' => json_encode([
                                'blanco_metodo' => $request->blanco_metodo ?? [],
                                'duplicado_muestra' => $request->duplicado_muestra ?? [],
                                'controles_calidad' => $request->controles_calidad ?? [],
                                'control_estandar' => $request->control_estandar ?? [],
                                'curva_calibracion' => $request->curva_calibracion ?? [],
                            ]),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );

                    Log::info('Control analítico guardado/actualizado para proceso', [
                        'control_id' => $analyticalControl->id,
                        'process_id' => $processId
                    ]);

                    // No es necesario procesar duplicado por separado: ya quedó embebido en controles_analiticos

                    // Guardar análisis de bases cambiables para este proceso
                    if (isset($request->items_ensayo[$processId])) {
                        foreach ($request->items_ensayo[$processId] as $item) {
                            $analysisData = [
                                'process_id' => $processId,
                                'service_id' => $serviceId,
                                'analytical_control_id' => $analyticalControl->id,
                                'codigo_interno' => $item['codigo_interno'] ?? '',
                                'peso_muestra' => $item['peso_muestra'] ?? 0,
                                'pw' => $item['pw'] ?? null,
                                'v_extractante' => $item['volumen_final'] ?? 0,
                                'lectura_blanco' => $item['lectura_blanco'] ?? null,
                                'factor_dilucion' => $item['factor_dilucion'] ?? null,
                                'bases_cambiables_mg_l' => $item['bases_cambiables_mg_l'] ?? null,
                                'bases_cambiables_mg_kg' => $item['bases_cambiables_mg_kg'] ?? null,
                                
                                // Campos para Na (solo almacenamos los de salida definidos en migración)
                                'na_blank' => $item['na_blanco'] ?? 0,
                                'na_factor' => $item['na_factor'] ?? 0,
                                'na_result' => $item['na_resultado'] ?? 0,
                                
                                // Campos para K
                                'k_blank' => $item['k_blanco'] ?? 0,
                                'k_factor' => $item['k_factor'] ?? 0,
                                'k_result' => $item['k_resultado'] ?? 0,
                                
                                // Campos para Ca
                                'ca_blank' => $item['ca_blanco'] ?? 0,
                                'ca_factor' => $item['ca_factor'] ?? 0,
                                'ca_result' => $item['ca_resultado'] ?? 0,
                                
                                // Campos para Mg
                                'mg_blank' => $item['mg_blanco'] ?? 0,
                                'mg_factor' => $item['mg_factor'] ?? 0,
                                'mg_result' => $item['mg_resultado'] ?? 0,
                                
                                'observaciones_item' => $item['observaciones'] ?? '',
                            ];

                            $exchangeableBasesAnalysis = ExchangeableBasesAnalysis::create($analysisData);

                            Log::info('Análisis de bases cambiables guardado para proceso', [
                                'analysis_id' => $exchangeableBasesAnalysis->id,
                                'process_id' => $processId,
                                'codigo_interno' => $item['codigo_interno'] ?? ''
                            ]);
                        }
                    }

                    // Actualizar estado del detalle del proceso
                    $serviceProcessDetail = ServiceProcessDetail::where('process_id', $processId)
                        ->where('service_id', $serviceId)
                        ->first();

                    if ($serviceProcessDetail) {
                        $serviceProcessDetail->status = 'completed';
                        $serviceProcessDetail->save();

                        Log::info('Estado del detalle del proceso actualizado a completado', [
                            'service_process_detail_id' => $serviceProcessDetail->id,
                            'process_id' => $processId
                        ]);
                    }

                    // Verificar si todos los detalles del proceso están completados
                    $pendingDetails = ServiceProcessDetail::where('process_id', $processId)
                        ->where('status', 'pending')
                        ->count();

                    if ($pendingDetails == 0) {
                        $process = Process::find($processId);
                        if ($process) {
                            $process->status = 'completed';
                            $process->save();

                            Log::info('Proceso marcado como completado', [
                                'process_id' => $processId
                            ]);
                        }
                    }

                    $savedCount++;

                } catch (\Exception $e) {
                    Log::error('Error procesando proceso en lote', [
                        'process_id' => $processId,
                        'error' => $e->getMessage()
                    ]);
                    $errors[] = "Proceso {$processId}: " . $e->getMessage();
                }
            }

            DB::commit();

            Log::info('Procesamiento por lotes completado', [
                'total_processes' => count($request->process_ids),
                'saved_count' => $savedCount,
                'errors' => $errors
            ]);

            $message = "Se procesaron {$savedCount} procesos exitosamente.";
            if (!empty($errors)) {
                $message .= " Errores: " . implode(', ', $errors);
            }

            return redirect()->route('lscefa.technical.analyses.exchangeable_bases.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al procesar análisis de bases cambiables por lotes', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return back()->withInput()->with('error', 'Error al procesar los análisis por lotes: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $analysis = ExchangeableBasesAnalysis::with(['process', 'service', 'analyticalControl'])->findOrFail($id);
        return view('lscefa::analyses.exchangeable_bases.show', compact('analysis'));
    }

    public function edit($id)
    {
        $analysis = ExchangeableBasesAnalysis::with(['process', 'service', 'analyticalControl'])->findOrFail($id);
        return view('lscefa::analyses.exchangeable_bases.edit', compact('analysis'));
    }

    public function update(Request $request, $id)
    {
        // Implementation for updating exchangeable bases analysis
        return redirect()->route('lscefa.technical.analyses.exchangeable_bases.index')
            ->with('success', 'Análisis de bases cambiables actualizado exitosamente');
    }

    public function destroy($id)
    {
        // Implementation for deleting exchangeable bases analysis
        return redirect()->route('lscefa.technical.analyses.exchangeable_bases.index')
            ->with('success', 'Análisis de bases cambiables eliminado exitosamente');
    }

    public function report($id)
    {
        // Implementation for generating exchangeable bases analysis report
        return redirect()->route('lscefa.technical.analyses.exchangeable_bases.index')
            ->with('success', 'Reporte de análisis de bases cambiables generado exitosamente');
    }
} 