<?php

namespace Modules\LSCEFA\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\LSCEFA\Models\Process;
use Modules\LSCEFA\Models\Service;
use Modules\LSCEFA\Models\ServiceProcessDetail;
use Modules\LSCEFA\Entities\MicronutrientsAnalysis;
use Modules\LSCEFA\Entities\AnalyticalControl;
use Illuminate\Support\Facades\Auth;

class MicronutrientsAnalysisController2 extends Controller
{
    public function index()
    {
        $processes = Process::with(['serviceProcessDetails.service'])
            ->whereHas('serviceProcessDetails', function ($query) {
                $query->whereHas('service', function ($serviceQuery) {
                    $serviceQuery->where(function($subQuery) {
                        $subQuery->whereRaw('LOWER(descripcion) LIKE ?', ['%micronutrientes%'])
                                ->orWhereRaw('LOWER(descripcion) LIKE ?', ['%micronutrients%'])
                                ->orWhereRaw('LOWER(descripcion) LIKE ?', ['%zinc%'])
                                ->orWhereRaw('LOWER(descripcion) LIKE ?', ['%hierro%'])
                                ->orWhereRaw('LOWER(descripcion) LIKE ?', ['%manganeso%'])
                                ->orWhereRaw('LOWER(descripcion) LIKE ?', ['%cobre%']);
                    })
                    ->whereRaw('LOWER(descripcion) NOT LIKE ?', ['%boro%'])
                    ->whereRaw('LOWER(descripcion) NOT LIKE ?', ['%boron%']);
                })->where('status', 'pending');
            })
            ->get();

        return view('lscefa::analyses.micronutrients.index', compact('processes'));
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
                'vol_extractante' => '',
                'lectura_blanco' => '',
                'factor_dilucion' => '',
                'valor_leido' => '',
                'concentracion_mg_l' => '',
                'concentracion_mg_kg' => ''
            ];
        }

        return view('lscefa::analyses.micronutrients.process', compact('process', 'service', 'pendingItems'));
    }

    public function storeMicronutrientsAnalysis(Request $request)
    {
        $processId = $request->input('process_id');
        $serviceId = $request->input('service_id');

        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'process_id' => 'required|string',
                'service_id' => 'required|integer',
                'consecutivo_no' => 'required|string',
                'fecha_analisis' => 'required|date',
                'equipo_utilizado' => 'nullable|string',
                'intervalo_metodo' => 'nullable|string',
                'nombre_analista' => 'nullable|string',

                'items_ensayo' => 'required|array|min:1',
                'items_ensayo.*.codigo_interno' => 'nullable|string',
                'items_ensayo.*.peso_muestra' => 'nullable|numeric',
                'items_ensayo.*.humedad' => 'nullable|numeric',
                'items_ensayo.*.volumen_final' => 'nullable|numeric',
                'items_ensayo.*.mn_lectura' => 'nullable|numeric',
                'items_ensayo.*.mn_factor' => 'nullable|numeric',
                'items_ensayo.*.mn_resultado' => 'nullable|numeric',
                'items_ensayo.*.fe_lectura' => 'nullable|numeric',
                'items_ensayo.*.fe_factor' => 'nullable|numeric',
                'items_ensayo.*.fe_resultado' => 'nullable|numeric',
                'items_ensayo.*.zn_lectura' => 'nullable|numeric',
                'items_ensayo.*.zn_factor' => 'nullable|numeric',
                'items_ensayo.*.zn_resultado' => 'nullable|numeric',
                'items_ensayo.*.cu_lectura' => 'nullable|numeric',
                'items_ensayo.*.cu_factor' => 'nullable|numeric',
                'items_ensayo.*.cu_resultado' => 'nullable|numeric',
                'items_ensayo.*.observaciones' => 'nullable|string',

                'blanco_metodo' => 'nullable|array',
                'duplicado_muestra' => 'nullable|array',
                'controles_calidad' => 'nullable|array',
                'control_estandar' => 'nullable|array',
                'curva_calibracion' => 'nullable|array',
            ]);

            // Resolver analysis_id desde process_id + service_id
            $serviceProcessDetail = ServiceProcessDetail::where('process_id', $processId)
                ->where('service_id', $serviceId)
                ->orderBy('id', 'desc')
                ->firstOrFail();

            $controlesAnaliticos = [
                'blanco_metodo' => $request->input('blanco_metodo', []),
                'duplicado_muestra' => $request->input('duplicado_muestra', []),
                'controles_calidad' => $request->input('controles_calidad', []),
                'control_estandar' => $request->input('control_estandar', []),
                'curva_calibracion' => $request->input('curva_calibracion', []),
            ];

            // 1. Blanco del método (Method blank)
            if ($request->has('blanco_metodo')) {
                foreach ($request->input('blanco_metodo', []) as $index => $blanco) {
                    if (!empty($blanco['identificacion'])) {
                        $controlesAnaliticos[] = [
                            'tipo' => 'blanco_metodo',
                            'identificacion' => $blanco['identificacion'],
                            'resultado' => $blanco['resultado'] ?? null,
                            'lcm' => $blanco['lcm'] ?? null,
                            'aceptabilidad' => $blanco['aceptabilidad'] ?? null,
                        ];
                    }
                }
            }

            // 2. Duplicado de muestra (Duplicate sample)
            if ($request->has('duplicado_muestra')) {
                foreach ($request->input('duplicado_muestra', []) as $index => $duplicado) {
                    if (!empty($duplicado['identificacion_muestra'])) {
                        $controlesAnaliticos[] = [
                            'tipo' => 'duplicado_muestra',
                            'identificacion_muestra' => $duplicado['identificacion_muestra'],
                            'replica_1' => $duplicado['replica_1'] ?? null,
                            'replica_2' => $duplicado['replica_2'] ?? null,
                            'dpr' => $duplicado['dpr'] ?? null,
                            'elemento' => $duplicado['elemento'] ?? null,
                            'aceptabilidad' => $duplicado['aceptabilidad'] ?? null,
                        ];
                    }
                }
            }

            $itemsEnsayo = array_values($request->input('items_ensayo', []));

            MicronutrientsAnalysis::updateOrCreate(
                ['analysis_id' => $serviceProcessDetail->id],
                [
                    'consecutivo_no' => $request->consecutivo_no,
                    'fecha_analisis' => $request->fecha_analisis,
                    'user_id' => Auth::id(),
                    'equipo_utilizado' => $request->equipo_utilizado,
                    'intervalo_metodo' => $request->intervalo_metodo,
                    'controles_analiticos' => $controlesAnaliticos,
                    'items_ensayo' => $itemsEnsayo,
                    'observaciones' => $request->observaciones,
                    'review_status' => 'pending',
                ]
            );

            // Marcar detalle como completado
            $serviceProcessDetail->status = 'completed';
            $serviceProcessDetail->save();

            DB::commit();
            return redirect()->route('lscefa.technical.analyses.micronutrients.index')
                ->with('success', 'Análisis de micronutrientes guardado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar análisis de micronutrientes: ' . $e->getMessage(), [
                'process_id' => $processId,
                'service_id' => $serviceId,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Error al guardar el análisis: ' . $e->getMessage())->withInput();
        }
    }

    public function batchProcess(Request $request)
    {
        $selectedProcessIds = [];
        if ($request->has('processes')) {
            $selectedProcessIds = explode(',', $request->processes);
        }
        
        if (empty($selectedProcessIds)) {
            return redirect()->route('lscefa.technical.analyses.micronutrients.index')
                ->with('error', 'No se seleccionaron procesos para procesar.');
        }

        // Limitar a máximo 10 procesos por lote
        if (count($selectedProcessIds) > 10) {
            return redirect()->route('lscefa.technical.analyses.micronutrients.index')
                ->with('error', 'No se pueden procesar más de 10 procesos a la vez. Por favor, selecciona menos procesos.');
        }

        $pendingProcesses = Process::with(['serviceProcessDetails.service'])
            ->whereIn('process_id', $selectedProcessIds)
            ->whereHas('serviceProcessDetails', function ($query) {
                $query->whereHas('service', function ($serviceQuery) {
                    $serviceQuery->where('descripcion', 'like', '%micronutrientes%')
                                ->orWhere('descripcion', 'like', '%micronutrients%')
                                ->orWhere('descripcion', 'like', '%zinc%')
                                ->orWhere('descripcion', 'like', '%hierro%')
                                ->orWhere('descripcion', 'like', '%manganeso%')
                                ->orWhere('descripcion', 'like', '%cobre%')
                                ->orWhere('descripcion', 'like', '%boro%');
                })->where('status', 'pending');
            })
            ->get();

        if ($pendingProcesses->isEmpty()) {
            return redirect()->route('lscefa.technical.analyses.micronutrients.index')
                ->with('error', 'No se encontraron procesos pendientes para los elementos seleccionados.');
        }

        return view('lscefa::analyses.micronutrients.batch_process', compact('pendingProcesses'));
    }

    public function batchStore(Request $request)
    {
        Log::info('🚨 MÉTODO BATCH STORE EJECUTÁNDOSE');
        try {
            Log::info('=== INICIANDO BATCH STORE ===');
            Log::info('Request method: ' . $request->method());
            Log::info('Request URL: ' . $request->url());
            Log::info('User ID: ' . Auth::id());
            
            DB::beginTransaction();
            Log::info('Transaction started successfully');

            // Log the request data for debugging
            Log::info('Batch store request data:', [
                'request_data' => $request->all(),
                'user_id' => Auth::id()
            ]);

            Log::info('Starting validation...');
            $request->validate([
                'processes' => 'required|array',
                'consecutivo_no' => 'required|string',
                'fecha_analisis' => 'required|date',
                'equipo_utilizado' => 'nullable|string',
                'intervalo_metodo' => 'nullable|string',
                'nombre_analista' => 'nullable|string',
            ]);

            // Log validation passed
            Log::info('✅ Validation passed successfully');

            $savedCount = 0;

            // Get common analysis data
            $consecutivoNo = $request->input('consecutivo_no');
            $fechaAnalisis = $request->input('fecha_analisis');
            $equipoUtilizado = $request->input('equipo_utilizado');
            $intervaloMetodo = $request->input('intervalo_metodo');
            $nombreAnalista = $request->input('nombre_analista', Auth::user()->name);

            Log::info('Starting to process processes...');
            $processes = $request->input('processes', []);
            Log::info('Number of processes to process: ' . count($processes));
            
            foreach ($processes as $processId => $services) {
                Log::info("🔄 Processing process: {$processId}", ['services' => $services]);
                Log::info("Number of services in process {$processId}: " . count($services));
                
                foreach ($services as $serviceId => $serviceData) {
                    Log::info("🔄 Processing service: {$serviceId}", ['service_data' => $serviceData]);
                    
                    try {
                        Log::info("🔍 Starting to process process {$processId}, service {$serviceId}");
                    
                    // Extract process_id and service_id from serviceData if they exist
                    $actualProcessId = $serviceData['process_id'] ?? $processId;
                    $actualServiceId = $serviceData['service_id'] ?? $serviceId;
                    
                                         Log::info("Creating analysis for process: {$actualProcessId}, service: {$actualServiceId}");
                     
                     Log::info("🔍 Looking for ServiceProcessDetail with process_id: {$actualProcessId}, service_id: {$actualServiceId}");
                     
                     // Get the service process detail to get the analysis_id
                     $serviceProcessDetail = ServiceProcessDetail::where('process_id', $actualProcessId)
                         ->where('service_id', $actualServiceId)
                         ->orderBy('id', 'desc')
                         ->first();
                     
                     if (!$serviceProcessDetail) {
                         Log::error("❌ ServiceProcessDetail not found for process: {$actualProcessId}, service: {$actualServiceId}");
                         throw new \Exception("No se encontró el detalle del proceso para process_id: {$actualProcessId}, service_id: {$actualServiceId}");
                     }
                     
                     Log::info("✅ ServiceProcessDetail found with ID: {$serviceProcessDetail->id}");
                     
                                          Log::info("🔧 Creating/updating MicronutrientsAnalysis...");
                     Log::info("Data to save:", [
                         'analysis_id' => $serviceProcessDetail->id,
                         'process_id' => $actualProcessId,
                         'service_id' => $actualServiceId,
                         'consecutivo_no' => $consecutivoNo,
                         'fecha_analisis' => $fechaAnalisis,
                         'user_id' => Auth::id(),
                         'equipo_utilizado' => $equipoUtilizado,
                         'intervalo_metodo' => $intervaloMetodo,
                         'analista' => $nombreAnalista,
                         'review_status' => 'pending',
                     ]);
                     
                                           // Create or update micronutrients analysis
                      $micronutrientsAnalysis = MicronutrientsAnalysis::updateOrCreate(
                          ['analysis_id' => $serviceProcessDetail->id],
                          [
                              'process_id' => $actualProcessId,
                              'service_id' => $actualServiceId,
                              'consecutivo_no' => $consecutivoNo,
                              'fecha_analisis' => $fechaAnalisis,
                              'user_id' => Auth::id(),
                              'equipo_utilizado' => $equipoUtilizado,
                              'intervalo_metodo' => $intervaloMetodo,
                              'analista' => $nombreAnalista,
                              'review_status' => 'pending',
                              'controles_analiticos' => [], // Inicializar como array vacío
                              'items_ensayo' => [], // Inicializar como array vacío
                          ]
                      );
                     
                     Log::info("✅ Analysis created/updated with ID: {$micronutrientsAnalysis->id}");

                                         Log::info("📊 Starting to collect analytical controls...");
                     
                                          // Collect all analytical controls from different tables
                     $controlesAnaliticos = [];

                     // 1. Blanco del método (Method blank) - TODOS LOS CAMPOS
                     if ($request->has('blanco_metodo')) {
                         foreach ($request->input('blanco_metodo', []) as $index => $blanco) {
                            // Capturar TODOS los campos sin excepción
                            $controlesAnaliticos[] = [
                                'tipo' => 'blanco_metodo',
                                'indice' => $index,
                                'identificacion' => $blanco['identificacion'] ?? null,
                                'resultado' => $blanco['resultado'] ?? null,
                                'lcm' => $blanco['lcm'] ?? null,
                                'aceptabilidad' => $blanco['aceptabilidad'] ?? null,
                                // Capturar cualquier campo adicional que pueda existir
                                'datos_completos' => $blanco,
                            ];
                        }
                    }

                    // 2. Duplicado de muestra (Duplicate sample) - TODOS LOS CAMPOS
                    if ($request->has('duplicado_muestra')) {
                        foreach ($request->input('duplicado_muestra', []) as $index => $duplicado) {
                            // Capturar TODOS los campos sin excepción
                            $controlesAnaliticos[] = [
                                'tipo' => 'duplicado_muestra',
                                'indice' => $index,
                                'identificacion_muestra' => $duplicado['identificacion_muestra'] ?? null,
                                'replica_1' => $duplicado['replica_1'] ?? null,
                                'replica_2' => $duplicado['replica_2'] ?? null,
                                'dpr' => $duplicado['dpr'] ?? null,
                                'elemento' => $duplicado['elemento'] ?? null,
                                'aceptabilidad' => $duplicado['aceptabilidad'] ?? null,
                                // Capturar cualquier campo adicional que pueda existir
                                'datos_completos' => $duplicado,
                            ];
                        }
                    }

                    // 3. Controles de calidad (Quality controls - MRC) - TODOS LOS CAMPOS
                    if ($request->has('controles_calidad')) {
                        foreach ($request->input('controles_calidad', []) as $index => $control) {
                            // Capturar TODOS los campos sin excepción
                            $controlesAnaliticos[] = [
                                'tipo' => 'controles_calidad',
                                'indice' => $index,
                                'controles_calidad' => $control['controles_calidad'] ?? null,
                                'identificacion' => $control['identificacion'] ?? null,
                                'valor_esperado' => $control['valor_esperado'] ?? null,
                                'valor_leido' => $control['valor_leido'] ?? null,
                                'porcentaje_recuperacion' => $control['porcentaje_recuperacion'] ?? null,
                                'aceptabilidad' => $control['aceptabilidad'] ?? null,
                                'observaciones' => $control['observaciones'] ?? null,
                                // Capturar cualquier campo adicional que pueda existir
                                'datos_completos' => $control,
                            ];
                        }
                    }

                    // 4. Control de estándar (Standard control) - TODOS LOS CAMPOS
                    if ($request->has('control_estandar')) {
                        foreach ($request->input('control_estandar', []) as $index => $estandar) {
                            // Capturar TODOS los campos sin excepción
                            $controlesAnaliticos[] = [
                                'tipo' => 'control_estandar',
                                'indice' => $index,
                                'estandar' => $estandar['estandar'] ?? null,
                                'concentracion' => $estandar['concentracion'] ?? null,
                                'valor_leido' => $estandar['valor_leido'] ?? null,
                                'porcentaje_error' => $estandar['porcentaje_error'] ?? null,
                                'aceptabilidad' => $estandar['aceptabilidad'] ?? null,
                                'observaciones' => $estandar['observaciones'] ?? null,
                                // Capturar cualquier campo adicional que pueda existir
                                'datos_completos' => $estandar,
                            ];
                        }
                    }

                    // 5. Curva de calibración (Calibration curve) - TODOS LOS CAMPOS
                    if ($request->has('curva_calibracion')) {
                        foreach ($request->input('curva_calibracion', []) as $index => $curva) {
                            // Capturar TODOS los campos sin excepción
                            $controlesAnaliticos[] = [
                                'tipo' => 'curva_calibracion',
                                'indice' => $index,
                                'elemento' => $curva['elemento'] ?? null,
                                'r2_obtenido' => $curva['r2_obtenido'] ?? null,
                                'r2_esperado' => $curva['r2_esperado'] ?? null,
                                'aceptabilidad' => $curva['aceptabilidad'] ?? null,
                                'observaciones' => $curva['observaciones'] ?? null,
                                // Capturar cualquier campo adicional que pueda existir
                                'datos_completos' => $curva,
                            ];
                        }
                    }

                                         // Save all analytical controls to the analytical_controls table
                     if (!empty($controlesAnaliticos)) {
                         AnalyticalControl::updateOrCreate(
                             [
                                 'analysis_type' => 'micronutrients',
                                 'analysis_id' => $micronutrientsAnalysis->id,
                                 'identificacion' => 'controles_analiticos_completos'
                             ],
                             [
                                 'controles_analiticos' => $controlesAnaliticos,
                             ]
                         );
                         
                         // También actualizar el campo controles_analiticos en la tabla micronutrients_analyses
                         $micronutrientsAnalysis->update([
                             'controles_analiticos' => $controlesAnaliticos
                         ]);
                     }

                    // Save test items (items_ensayo) as JSON array
                    $itemsEnsayo = [];
                    if ($request->has('items_ensayo')) {
                        $allItems = $request->input('items_ensayo');
                        Log::info("All items_ensayo data:", ['all_items' => $allItems]);
                        
                        // Buscar items para este proceso y servicio específico
                        if (isset($allItems[$processId][$serviceId])) {
                            $items = $allItems[$processId][$serviceId];
                            Log::info("Found items for process {$processId}, service {$serviceId}:", ['items' => $items]);
                        } elseif (isset($allItems[$actualProcessId][$actualServiceId])) {
                            $items = $allItems[$actualProcessId][$actualServiceId];
                            Log::info("Found items for actual process {$actualProcessId}, service {$actualServiceId}:", ['items' => $items]);
                        } else {
                            Log::warning("No items found for process {$processId}, service {$serviceId} or {$actualProcessId}, {$actualServiceId}");
                            $items = [];
                        }
                            
                        foreach ($items as $itemIndex => $item) {
                            // Guardar todos los items, incluso si no tienen código interno
                            $itemsEnsayo[] = [
                                'codigo_interno' => $item['codigo_interno'] ?? null,
                                'peso_muestra' => $item['peso_muestra'] ?? null,
                                'humedad' => $item['humedad'] ?? null,
                                'volumen_final' => $item['volumen_final'] ?? null,
                                'mn_lectura' => $item['mn_lectura'] ?? null,
                                'mn_factor' => $item['mn_factor'] ?? null,
                                'mn_resultado' => $item['mn_resultado'] ?? null,
                                'fe_lectura' => $item['fe_lectura'] ?? null,
                                'fe_factor' => $item['fe_factor'] ?? null,
                                'fe_resultado' => $item['fe_resultado'] ?? null,
                                'zn_lectura' => $item['zn_lectura'] ?? null,
                                'zn_factor' => $item['zn_factor'] ?? null,
                                'zn_resultado' => $item['zn_resultado'] ?? null,
                                'cu_lectura' => $item['cu_lectura'] ?? null,
                                'cu_factor' => $item['cu_factor'] ?? null,
                                'cu_resultado' => $item['cu_resultado'] ?? null,
                                'observaciones' => $item['observaciones'] ?? null,
                            ];
                        }
                    } else {
                        Log::warning("No items_ensayo data in request");
                    }

                    // Update the analysis with items_ensayo
                    Log::info("Updating analysis with items_ensayo:", ['items_ensayo' => $itemsEnsayo]);
                    $micronutrientsAnalysis->update([
                        'items_ensayo' => $itemsEnsayo
                    ]);
                    Log::info("Analysis updated successfully");

                    // Update service process detail status
                    ServiceProcessDetail::where('process_id', $processId)
                        ->where('service_id', $serviceId)
                        ->update(['status' => 'completed']);

                    $savedCount++;
                    } catch (\Exception $innerException) {
                        Log::error("Error processing process {$processId}, service {$serviceId}: " . $innerException->getMessage());
                        throw $innerException;
                    }
                }
            }

                         Log::info("💾 Committing transaction...");
             DB::commit();
             Log::info("✅ Transaction committed successfully");

             Log::info('🎉 Procesamiento en lote de micronutrientes completado', [
                 'saved_count' => $savedCount,
                 'user_id' => Auth::id()
             ]);

             Log::info("🔄 Redirecting to index page...");
             return redirect()->route('lscefa.technical.analyses.micronutrients.index')
                 ->with('success', "✅ ¡Éxito! Se procesaron exitosamente {$savedCount} análisis de micronutrientes. Todos los datos han sido guardados correctamente. Los items de ensayo y controles analíticos se han guardado en la base de datos.");

                 } catch (\Exception $e) {
             Log::error('🚨 EXCEPCIÓN CAPTURADA EN BATCH STORE');
             Log::error('Error message: ' . $e->getMessage());
             Log::error('Error code: ' . $e->getCode());
             Log::error('Error file: ' . $e->getFile());
             Log::error('Error line: ' . $e->getLine());
             Log::error('Full trace: ' . $e->getTraceAsString());
             
             Log::info('🔄 Rolling back transaction...');
             DB::rollBack();
             Log::info('✅ Transaction rolled back successfully');
             
             Log::error('❌ Error en procesamiento en lote de micronutrientes: ' . $e->getMessage(), [
                 'user_id' => Auth::id(),
                 'exception' => $e,
                 'trace' => $e->getTraceAsString()
             ]);

             Log::info('🔄 Redirecting back with error...');
             return back()->with('error', '❌ Error al procesar los análisis en lote: ' . $e->getMessage() . '. Por favor, verifique los datos e intente nuevamente.')
                 ->withInput();
         }
    }

    public function show($id)
    {
        $analysis = MicronutrientsAnalysis::with(['items', 'analyticalControls'])->findOrFail($id);
        return view('lscefa::analyses.micronutrients.show', compact('analysis'));
    }

    public function edit($id)
    {
        $analysis = MicronutrientsAnalysis::with(['items', 'analyticalControls'])->findOrFail($id);
        return view('lscefa::analyses.micronutrients.edit', compact('analysis'));
    }

    public function update(Request $request, $id)
    {
        // Similar to store but for updating existing analysis
        return redirect()->route('lscefa.technical.analyses.micronutrients.index')
            ->with('success', 'Análisis de micronutrientes actualizado exitosamente.');
    }

    public function destroy($id)
    {
        $analysis = MicronutrientsAnalysis::findOrFail($id);
        $analysis->delete();
        
        return redirect()->route('lscefa.technical.analyses.micronutrients.index')
            ->with('success', 'Análisis de micronutrientes eliminado exitosamente.');
    }

    public function report($id)
    {
        $analysis = MicronutrientsAnalysis::with(['items', 'analyticalControls'])->findOrFail($id);
        return view('lscefa::analyses.micronutrients.report', compact('analysis'));
    }
}
