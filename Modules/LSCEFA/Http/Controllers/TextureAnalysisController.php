<?php

namespace Modules\LSCEFA\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\LSCEFA\Models\Process;
use Modules\LSCEFA\Models\Service;
use Modules\LSCEFA\Models\ServiceProcessDetail;
use Modules\LSCEFA\Entities\TextureAnalysis;
use Modules\LSCEFA\Entities\AnalyticalControl;
use Illuminate\Support\Facades\Auth;
use Modules\LSCEFA\Entities\BatchTextureAnalysis;

class TextureAnalysisController extends Controller
{
    public function index()
    {
        $processes = Process::with(['serviceProcessDetails.service'])
            ->whereHas('serviceProcessDetails', function ($query) {
                $query->whereHas('service', function ($serviceQuery) {
                    $serviceQuery->where('descripcion', 'like', '%textura%')
                                ->orWhere('descripcion', 'like', '%texture%');
                })->where('status', 'pending');
            })
            ->get();

        return view('lscefa::analyses.texture.index', compact('processes'));
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
                'peso_arena' => '',
                'peso_limo' => '',
                'peso_arcilla' => '',
                'peso_total' => '',
                'porcentaje_arena' => '',
                'porcentaje_limo' => '',
                'porcentaje_arcilla' => '',
                'clase_textural' => '',
                'observaciones' => ''
            ];
        }

        return view('lscefa::analyses.texture.process', compact('process', 'service', 'pendingItems'));
    }

    public function storeTextureAnalysis(Request $request)
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
                'items.*.peso_arena' => 'nullable|numeric|min:0',
                'items.*.peso_limo' => 'nullable|numeric|min:0',
                'items.*.peso_arcilla' => 'nullable|numeric|min:0',
                'items.*.peso_total' => 'nullable|numeric|min:0',
                'items.*.porcentaje_arena' => 'nullable|numeric|min:0',
                'items.*.porcentaje_limo' => 'nullable|numeric|min:0',
                'items.*.porcentaje_arcilla' => 'nullable|numeric|min:0',
                'items.*.clase_textural' => 'nullable|string',
                'items.*.observaciones' => 'nullable|string',
            ]);

            Log::info('Iniciando guardado de análisis de textura', [
                'process_id' => $processId,
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);

            // Create the main analysis record
            $textureAnalysis = TextureAnalysis::create([
                'process_id' => $processId,
                'service_id' => $serviceId,
                'consecutivo_no' => $request->input('consecutivo_no'),
                'fecha_analisis' => $request->input('fecha_analisis'),
                'equipo_utilizado' => $request->input('equipo_utilizado'),
                'intervalo_metodo' => $request->input('intervalo_metodo'),
                'analista' => $request->input('analista'),
                'user_id' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Save analytical controls
            foreach ($request->input('controles_analiticos') as $control) {
                if (!empty($control['identificacion'])) {
                    AnalyticalControl::create([
                        'analysis_id' => $textureAnalysis->id,
                        'analysis_type' => 'texture',
                        'identificacion' => $control['identificacion'],
                        'valor_esperado' => $control['valor_esperado'] ?? null,
                        'valor_leido' => $control['valor_leido'] ?? null,
                        'porcentaje_error' => $control['porcentaje_error'] ?? null,
                        'aceptabilidad_error' => $control['aceptabilidad_error'] ?? null,
                        'porcentaje_recuperacion' => $control['porcentaje_recuperacion'] ?? null,
                        'aceptabilidad_recuperacion' => $control['aceptabilidad_recuperacion'] ?? null,
                        'porcentaje_dpr' => $control['porcentaje_dpr'] ?? null,
                        'aceptabilidad_dpr' => $control['aceptabilidad_dpr'] ?? null,
                    ]);
                }
            }

            // Save analysis items
            foreach ($request->input('items') as $item) {
                if (!empty($item['codigo_interno'])) {
                    $textureAnalysis->items()->create([
                        'codigo_interno' => $item['codigo_interno'],
                        'peso_arena' => $item['peso_arena'] ?? null,
                        'peso_limo' => $item['peso_limo'] ?? null,
                        'peso_arcilla' => $item['peso_arcilla'] ?? null,
                        'peso_total' => $item['peso_total'] ?? null,
                        'porcentaje_arena' => $item['porcentaje_arena'] ?? null,
                        'porcentaje_limo' => $item['porcentaje_limo'] ?? null,
                        'porcentaje_arcilla' => $item['porcentaje_arcilla'] ?? null,
                        'clase_textural' => $item['clase_textural'] ?? null,
                        'observaciones' => $item['observaciones'] ?? null,
                    ]);
                }
            }

            // Update service process detail status
            $serviceProcessDetail = ServiceProcessDetail::where('process_id', $processId)
                ->where('service_id', $serviceId)
                ->first();

            if ($serviceProcessDetail) {
                $serviceProcessDetail->update(['status' => 'completed']);
            }

            DB::commit();

            Log::info('Análisis de textura guardado exitosamente', [
                'analysis_id' => $textureAnalysis->id,
                'process_id' => $processId
            ]);

            return redirect()->route('lscefa.technical.analyses.texture.index')
                ->with('success', 'Análisis de textura guardado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar análisis de textura', [
                'process_id' => $processId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withInput()->with('error', 'Error al guardar el análisis: ' . $e->getMessage());
        }
    }

    public function batchProcess(Request $request)
    {
        $processIds = $request->query('processes');
        
        if (!$processIds) {
            return redirect()->route('lscefa.technical.analyses.texture.index')
                ->with('error', 'No se especificaron procesos para procesar');
        }

        $processIdsArray = explode(',', $processIds);
        
        $processes = Process::with(['serviceProcessDetails.service'])
            ->whereIn('process_id', $processIdsArray)
            ->whereHas('serviceProcessDetails', function ($query) {
                $query->whereHas('service', function ($serviceQuery) {
                    $serviceQuery->where('descripcion', 'like', '%textura%')
                                ->orWhere('descripcion', 'like', '%texture%');
                });
            })
            ->get();

        return view('lscefa::analyses.texture.batch_process', ['processes' => $processes->values()]);
    }

    public function batchStore(Request $request)
    {
        // Log para debug - ver todos los datos que llegan
        Log::info('Datos completos recibidos en batchStore:', $request->all());
        Log::info('Controles analíticos recibidos:', $request->analytical_controls ?? []);
        
        try {
            DB::beginTransaction();
            $analysesInput = $request->input('analyses');
            Log::info('DEBUG analysesInput type', ['type' => gettype($analysesInput)]);
            Log::info('DEBUG analysesInput content', ['analysesInput' => $analysesInput]);
            Log::info('DEBUG analyses count', ['count' => is_array($analysesInput) ? count($analysesInput) : 'not array']);
            Log::info('DEBUG analyses content', ['analyses' => $analysesInput]);
            $completedProcesses = [];
            foreach ($analysesInput as $analysisData) {
                try {
                    Log::info('Processing analysisData', $analysisData);
                    // Serializar muestras y controles como JSON
                    $samples = isset($analysisData['items']) ? json_encode($analysisData['items']) : null;
                    $controls = isset($analysisData['analytical_controls']) ? json_encode($analysisData['analytical_controls']) : null;
                    $extra = isset($analysisData['extra_data']) ? json_encode($analysisData['extra_data']) : null;

                    Log::info('Antes de BatchTextureAnalysis::create', ['data' => [
                        'consecutive_no' => $request->consecutivo_no ?? null,
                        'analysis_date' => $request->fecha_analisis ?? null,
                        'analyst_name' => $request->nombre_analista ?? null,
                        'methodology_used' => $request->metodologia_utilizada ?? null,
                        'thermometer_code' => $request->codigo_termometro ?? null,
                        'hydrometer_code' => $request->codigo_hidrometro ?? null,
                        'equipment_used' => $analysisData['equipment_used'] ?? null,
                        'method_interval' => $analysisData['method_interval'] ?? null,
                        'user_id' => Auth::id(),
                        'process_id' => $analysisData['process_id'],
                        'service_id' => $analysisData['service_id'],
                        'samples' => $samples,
                        'analytical_controls' => $controls,
                        'duplicate_a_code' => $analysisData['duplicate_a_code'] ?? null,
                        'duplicate_a_avg_sand' => $analysisData['duplicate_a_avg_sand'] ?? null,
                        'duplicate_a_avg_clay' => $analysisData['duplicate_a_avg_clay'] ?? null,
                        'duplicate_a_avg_silt' => $analysisData['duplicate_a_avg_silt'] ?? null,
                        'duplicate_a_dpr_sand' => $analysisData['duplicate_a_dpr_sand'] ?? null,
                        'duplicate_a_dpr_clay' => $analysisData['duplicate_a_dpr_clay'] ?? null,
                        'duplicate_a_dpr_silt' => $analysisData['duplicate_a_dpr_silt'] ?? null,
                        'duplicate_a_acceptability' => $analysisData['duplicate_a_acceptability'] ?? null,
                        'duplicate_a_observations' => $analysisData['duplicate_a_observations'] ?? null,
                        'duplicate_b_code' => $analysisData['duplicate_b_code'] ?? null,
                        'duplicate_b_avg_sand' => $analysisData['duplicate_b_avg_sand'] ?? null,
                        'duplicate_b_avg_clay' => $analysisData['duplicate_b_avg_clay'] ?? null,
                        'duplicate_b_avg_silt' => $analysisData['duplicate_b_avg_silt'] ?? null,
                        'duplicate_b_dpr_sand' => $analysisData['duplicate_b_dpr_sand'] ?? null,
                        'duplicate_b_dpr_clay' => $analysisData['duplicate_b_dpr_clay'] ?? null,
                        'duplicate_b_dpr_silt' => $analysisData['duplicate_b_dpr_silt'] ?? null,
                        'duplicate_b_acceptability' => $analysisData['duplicate_b_acceptability'] ?? null,
                        'duplicate_b_observations' => $analysisData['duplicate_b_observations'] ?? null,
                        'reference_material_expected_sand' => $analysisData['reference_material_expected_sand'] ?? null,
                        'reference_material_expected_clay' => $analysisData['reference_material_expected_clay'] ?? null,
                        'reference_material_expected_silt' => $analysisData['reference_material_expected_silt'] ?? null,
                        'reference_material_obtained_sand' => $analysisData['reference_material_obtained_sand'] ?? null,
                        'reference_material_obtained_clay' => $analysisData['reference_material_obtained_clay'] ?? null,
                        'reference_material_obtained_silt' => $analysisData['reference_material_obtained_silt'] ?? null,
                        'reference_material_error_percent' => $analysisData['reference_material_error_percent'] ?? null,
                        'reference_material_acceptability' => $analysisData['reference_material_acceptability'] ?? null,
                        'reference_material_observations' => $analysisData['reference_material_observations'] ?? null,
                        'general_observations' => $analysisData['general_observations'] ?? null,
                        'extra_data' => $extra,
                    ]]);
                    $batch = \Modules\LSCEFA\Entities\BatchTextureAnalysis::create([
                        'consecutive_no' => $request->consecutivo_no ?? null,
                        'analysis_date' => $request->fecha_analisis ?? null,
                        'analyst_name' => $request->nombre_analista ?? null,
                        'methodology_used' => $request->metodologia_utilizada ?? null,
                        'thermometer_code' => $request->codigo_termometro ?? null,
                        'hydrometer_code' => $request->codigo_hidrometro ?? null,
                        'equipment_used' => $analysisData['equipment_used'] ?? null,
                        'method_interval' => $analysisData['method_interval'] ?? null,
                        'user_id' => Auth::id(),
                        'process_id' => $analysisData['process_id'],
                        'service_id' => $analysisData['service_id'],
                        'review_status' => 'pending',
                        'samples' => $samples,
                        'analytical_controls' => $controls,
                        'duplicate_a_code' => $analysisData['duplicate_a_code'] ?? null,
                        'duplicate_a_avg_sand' => $analysisData['duplicate_a_avg_sand'] ?? null,
                        'duplicate_a_avg_clay' => $analysisData['duplicate_a_avg_clay'] ?? null,
                        'duplicate_a_avg_silt' => $analysisData['duplicate_a_avg_silt'] ?? null,
                        'duplicate_a_dpr_sand' => $analysisData['duplicate_a_dpr_sand'] ?? null,
                        'duplicate_a_dpr_clay' => $analysisData['duplicate_a_dpr_clay'] ?? null,
                        'duplicate_a_dpr_silt' => $analysisData['duplicate_a_dpr_silt'] ?? null,
                        'duplicate_a_acceptability' => $analysisData['duplicate_a_acceptability'] ?? null,
                        'duplicate_a_observations' => $analysisData['duplicate_a_observations'] ?? null,
                        'duplicate_b_code' => $analysisData['duplicate_b_code'] ?? null,
                        'duplicate_b_avg_sand' => $analysisData['duplicate_b_avg_sand'] ?? null,
                        'duplicate_b_avg_clay' => $analysisData['duplicate_b_avg_clay'] ?? null,
                        'duplicate_b_avg_silt' => $analysisData['duplicate_b_avg_silt'] ?? null,
                        'duplicate_b_dpr_sand' => $analysisData['duplicate_b_dpr_sand'] ?? null,
                        'duplicate_b_dpr_clay' => $analysisData['duplicate_b_dpr_clay'] ?? null,
                        'duplicate_b_dpr_silt' => $analysisData['duplicate_b_dpr_silt'] ?? null,
                        'duplicate_b_acceptability' => $analysisData['duplicate_b_acceptability'] ?? null,
                        'duplicate_b_observations' => $analysisData['duplicate_b_observations'] ?? null,
                        'reference_material_expected_sand' => $analysisData['reference_material_expected_sand'] ?? null,
                        'reference_material_expected_clay' => $analysisData['reference_material_expected_clay'] ?? null,
                        'reference_material_expected_silt' => $analysisData['reference_material_expected_silt'] ?? null,
                        'reference_material_obtained_sand' => $analysisData['reference_material_obtained_sand'] ?? null,
                        'reference_material_obtained_clay' => $analysisData['reference_material_obtained_clay'] ?? null,
                        'reference_material_obtained_silt' => $analysisData['reference_material_obtained_silt'] ?? null,
                        'reference_material_error_percent' => $analysisData['reference_material_error_percent'] ?? null,
                        'reference_material_acceptability' => $analysisData['reference_material_acceptability'] ?? null,
                        'reference_material_observations' => $analysisData['reference_material_observations'] ?? null,
                        'general_observations' => $analysisData['general_observations'] ?? null,
                        'extra_data' => $extra,
                    ]);
                    Log::info('Despues de BatchTextureAnalysis::create', ['id' => $batch->id, 'process_id' => $batch->process_id]);
                    Log::info('BatchTextureAnalysis created', ['id' => $batch->id, 'process_id' => $batch->process_id]);

                    // Guardar controles analíticos individuales
                    $analyticalControls = [];
                    
                    // Extraer datos de Duplicado A
                    if (!empty($analysisData['duplicado_a_codigo'])) {
                        $analyticalControls[] = [
                            'identificacion' => $analysisData['duplicado_a_codigo'],
                            'codigo_interno' => '',
                            'arena_1' => $analysisData['duplicado_a_promedio_arena'] ?? '',
                            'arcilla_1' => $analysisData['duplicado_a_promedio_arcilla'] ?? '',
                            'limo_1' => $analysisData['duplicado_a_promedio_limo'] ?? '',
                            'dpr_arena' => $analysisData['duplicado_a_dpr_arena'] ?? '',
                            'dpr_arcilla' => $analysisData['duplicado_a_dpr_arcilla'] ?? '',
                            'dpr_limo' => $analysisData['duplicado_a_dpr_limo'] ?? '',
                            'aceptabilidad_control' => $analysisData['duplicado_a_aceptabilidad'] ?? '',
                            'observaciones' => $analysisData['duplicado_a_observaciones'] ?? '',
                        ];
                    }
                    
                    // Extraer datos de Duplicado B
                    if (!empty($analysisData['duplicado_b_codigo'])) {
                        $analyticalControls[] = [
                            'identificacion' => $analysisData['duplicado_b_codigo'],
                            'codigo_interno' => '',
                            'arena_1' => $analysisData['duplicado_b_promedio_arena'] ?? '',
                            'arcilla_1' => $analysisData['duplicado_b_promedio_arcilla'] ?? '',
                            'limo_1' => $analysisData['duplicado_b_promedio_limo'] ?? '',
                            'dpr_arena' => $analysisData['duplicado_b_dpr_arena'] ?? '',
                            'dpr_arcilla' => $analysisData['duplicado_b_dpr_arcilla'] ?? '',
                            'dpr_limo' => $analysisData['duplicado_b_dpr_limo'] ?? '',
                            'aceptabilidad_control' => $analysisData['duplicado_b_aceptabilidad'] ?? '',
                            'observaciones' => $analysisData['duplicado_b_observaciones'] ?? '',
                        ];
                    }
                    
                    // Extraer datos de Material de Referencia (Exactitud)
                    if (!empty($analysisData['material_referencia_lote'])) {
                        $analyticalControls[] = [
                            'identificacion' => 'Material de Referencia',
                            'codigo_interno' => $analysisData['material_referencia_lote'] ?? '',
                            'arena_1' => $analysisData['material_referencia_esperado_arena'] ?? '',
                            'arcilla_1' => $analysisData['material_referencia_esperado_arcilla'] ?? '',
                            'limo_1' => $analysisData['material_referencia_esperado_limo'] ?? '',
                            'dpr_arena' => $analysisData['material_referencia_obtenido_arena'] ?? '',
                            'dpr_arcilla' => $analysisData['material_referencia_obtenido_arcilla'] ?? '',
                            'dpr_limo' => $analysisData['material_referencia_obtenido_limo'] ?? '',
                            'aceptabilidad_control' => $analysisData['material_referencia_aceptabilidad'] ?? '',
                            'observaciones' => $analysisData['material_referencia_observaciones'] ?? '',
                        ];
                    }
                    
                    // Guardar cada control en la tabla analytical_controls
                    foreach ($analyticalControls as $control) {
                        // Log para debug - ver qué datos llegan
                        Log::info('Datos de control a guardar:', $control);
                        
                        // Obtener los nombres de las columnas de la tabla analytical_controls
                        $columns = \Schema::getColumnListing('analytical_controls');
                        
                        $data = [
                            'analysis_id' => $batch->id,
                            'process_id' => $analysisData['process_id'],
                            'analysis_type' => 'texture',
                        ];
                        
                        // Mapeo flexible: acepta nombres antiguos y los mapea a los nuevos
                        $jsonData = [
                            'identificacion' => $control['identificacion'] ?? '',
                            'codigo_interno' => $control['codigo_interno'] ?? '',
                            'arena_1' => $control['arena_1'] ?? $control['valor_esperado'] ?? $control['arena'] ?? '',
                            'arcilla_1' => $control['arcilla_1'] ?? $control['valor_leido'] ?? $control['arcilla'] ?? '',
                            'limo_1' => $control['limo_1'] ?? $control['porcentaje_error'] ?? $control['limo'] ?? '',
                            'dpr_arena' => $control['dpr_arena'] ?? $control['dpr_arena_1'] ?? $control['dpr_arena_2'] ?? '',
                            'dpr_arcilla' => $control['dpr_arcilla'] ?? $control['dpr_arcilla_1'] ?? $control['dpr_arcilla_2'] ?? '',
                            'dpr_limo' => $control['dpr_limo'] ?? $control['dpr_limo_1'] ?? $control['dpr_limo_2'] ?? '',
                            'aceptabilidad_control' => $control['aceptabilidad_control'] ?? $control['aceptabilidad'] ?? $control['aceptabilidad_dpr'] ?? '',
                            'observaciones' => $control['observaciones'] ?? $control['obs'] ?? $control['comentarios'] ?? '',
                        ];
                        
                        // Guardar todos los campos del control que coincidan con alguna columna
                        foreach ($control as $key => $value) {
                            if (in_array($key, $columns)) {
                                $data[$key] = $value;
                            }
                        }
                        
                        // Guardar todo el control como JSON en la columna controles_analiticos usando los nombres de la tabla
                        if (in_array('controles_analiticos', $columns)) {
                            $data['controles_analiticos'] = json_encode($jsonData);
                        }
                        
                        \Modules\LSCEFA\Entities\AnalyticalControl::create($data);
                    }
                    // Guardar datos de la tabla de Exactitud (material de referencia)
                    if (isset($analysisData['material_referencia'])) {
                        $ref = $analysisData['material_referencia'];
                        $data = [
                            'analysis_id' => $batch->id,
                            'process_id' => $analysisData['process_id'],
                            'analysis_type' => 'texture',
                        ];
                        // Mapeo para el JSON de exactitud igual que arriba
                        $jsonMap = [
                                'identificacion' => 'identificacion',
                                'codigo_interno' => 'codigo_interno',
                                'arena_1' => 'arena_1',
                                'arcilla_1' => 'arcilla_1',
                                'limo_1' => 'limo_1',
                                'dpr_arena' => 'dpr_arena',
                                'dpr_arcilla' => 'dpr_arcilla',
                                'dpr_limo' => 'dpr_limo',
                                'aceptabilidad_control' => 'aceptabilidad_control',
                                'observaciones' => 'observaciones',
                            ];
                        $jsonData = [];
                        foreach ($jsonMap as $from => $to) {
                            if (isset($ref[$from])) {
                                $jsonData[$to] = $ref[$from];
                            }
                        }
                        foreach ($ref as $key => $value) {
                            if (in_array($key, $columns)) {
                                $data[$key] = $value;
                            }
                        }
                        if (in_array('controles_analiticos', $columns)) {
                            $data['controles_analiticos'] = json_encode($jsonData);
                        }
                        \Modules\LSCEFA\Entities\AnalyticalControl::create($data);
                    }
                    $batch->save();
                } catch (\Exception $e) {
                    Log::error('Error creating BatchTextureAnalysis', [
                        'error' => $e->getMessage(),
                        'data' => $analysisData
                    ]);
                }
                // Update service process detail status
                $spd = ServiceProcessDetail::where('process_id', $analysisData['process_id'])
                    ->where('service_id', $analysisData['service_id'])
                    ->first();
                if ($spd) {
                    $spd->update(['status' => 'completed']);
                    $completedProcesses[] = $analysisData['process_id'];
                }
            }

            // Marcar proceso como completed si todos los detalles están completed
            foreach (array_unique($completedProcesses) as $processId) {
                $pendingDetails = ServiceProcessDetail::where('process_id', $processId)
                    ->where('status', 'pending')
                    ->count();
                if ($pendingDetails == 0) {
                    $process = \Modules\LSCEFA\Models\Process::where('process_id', $processId)->first();
                    if ($process) {
                        $process->status = 'completed';
                        $process->save();
                        Log::info('Process marked as completed', ['process_id' => $processId]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('lscefa.technical.analyses.texture.index')
                ->with('success', 'Batch texture analyses saved successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in batchStore', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->withInput()->with('error', 'Error saving batch texture analyses: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $analysis = TextureAnalysis::with(['items', 'analyticalControls'])->findOrFail($id);
        return view('lscefa::analyses.texture.show', compact('analysis'));
    }

    public function edit($id)
    {
        $analysis = TextureAnalysis::with(['items', 'analyticalControls'])->findOrFail($id);
        return view('lscefa::analyses.texture.edit', compact('analysis'));
    }

    public function update(Request $request, $id)
    {
        $analysis = TextureAnalysis::findOrFail($id);
        
        $request->validate([
            'consecutivo_no' => 'required|string',
            'fecha_analisis' => 'required|date',
            'equipo_utilizado' => 'nullable|string',
            'intervalo_metodo' => 'nullable|string',
            'analista' => 'nullable|string',
        ]);

        $analysis->update($request->only([
            'consecutivo_no', 'fecha_analisis', 'equipo_utilizado', 
            'intervalo_metodo', 'analista'
        ]));

        return redirect()->route('lscefa.technical.analyses.texture.index')
            ->with('success', 'Análisis de textura actualizado exitosamente');
    }

    public function destroy($id)
    {
        $analysis = TextureAnalysis::findOrFail($id);
        $analysis->delete();

        return redirect()->route('lscefa.technical.analyses.texture.index')
            ->with('success', 'Análisis de textura eliminado exitosamente');
    }

    public function report($id)
    {
        $analysis = TextureAnalysis::with(['items', 'analyticalControls'])->findOrFail($id);
        return view('lscefa::analyses.texture.report', compact('analysis'));
    }

    /**
     * Editar un análisis de textura rechazado
     */
    public function editRejected($id)
    {
        // Cargar el análisis con todas las relaciones necesarias
        $analysis = \Modules\LSCEFA\Entities\BatchTextureAnalysis::with([
            'analyticalControls',
            'process',
            'process.quote'
        ])->findOrFail($id);
        
        // Verificar que el análisis esté rechazado
        if ($analysis->review_status !== 'rejected') {
            return redirect()->route('lscefa.technical.analyses.texture.index')
                ->with('error', 'Este análisis no está rechazado o ya fue corregido.');
        }

        // Obtener el proceso y servicio asociados
        $process = \Modules\LSCEFA\Models\Process::find($analysis->process_id);
        $service = \Modules\LSCEFA\Models\Service::find($analysis->service_id);

        // Crear un array de procesos con el proceso del análisis rechazado
        $processes = collect([$process]);

        // Cargar los controles analíticos desde la tabla analytical_controls
        $analyticalControls = \Modules\LSCEFA\Entities\AnalyticalControl::where('analysis_id', $id)
            ->where('analysis_type', 'texture')
            ->get();

        // Debug: Log detallado de la estructura de analytical_controls
        \Log::info('Estructura completa de analytical_controls:', [
            'total_controls' => $analyticalControls->count(),
            'controls_data' => $analyticalControls->toArray()
        ]);

        // Extraer datos de Precisión Analítica y Exactitud desde los controles
        $precisionData = [];
        $accuracyData = [];
        
        foreach ($analyticalControls as $control) {
            \Log::info('Procesando control individual:', [
                'control_id' => $control->id,
                'has_controles_analiticos' => isset($control->controles_analiticos),
                'controles_analiticos_raw' => $control->controles_analiticos
            ]);
            
            if (isset($control->controles_analiticos)) {
                $controlData = json_decode($control->controles_analiticos, true);
                
                \Log::info('Control decodificado:', [
                    'control_data' => $controlData,
                    'identificacion' => $controlData['identificacion'] ?? 'NO_IDENTIFICACION'
                ]);
                
                if ($controlData) {
                    // Lógica mejorada para identificar el tipo de control
                    $identificacion = strtolower($controlData['identificacion'] ?? '');
                    
                    if (str_contains($identificacion, 'duplicado a') || 
                        (str_contains($identificacion, 'duplicado') && !str_contains($identificacion, 'b'))) {
                        // Es Duplicado A
                        $controlData['tipo'] = 'duplicado_a';
                        $precisionData[] = $controlData;
                        \Log::info('Agregado a precisionData (Duplicado A):', $controlData);
                    } elseif (str_contains($identificacion, 'duplicado b') || 
                               (str_contains($identificacion, 'duplicado') && !str_contains($identificacion, 'a'))) {
                        // Es Duplicado B
                        $controlData['tipo'] = 'duplicado_b';
                        $precisionData[] = $controlData;
                        \Log::info('Agregado a precisionData (Duplicado B):', $controlData);
                    } elseif (str_contains($identificacion, 'material de referencia') || 
                               str_contains($identificacion, 'referencia')) {
                        // Es Material de Referencia
                        $controlData['tipo'] = 'material_referencia';
                        $accuracyData[] = $controlData;
                        \Log::info('Agregado a accuracyData (Material de Referencia):', $controlData);
                    } else {
                        // Si no coincide con ninguno, intentar identificar por el contenido
                        if (isset($controlData['arena_1']) && isset($controlData['arcilla_1'])) {
                            // Si tiene datos de arena y arcilla, probablemente sea un duplicado
                            if (count($precisionData) == 0) {
                                $controlData['tipo'] = 'duplicado_a';
                                $precisionData[] = $controlData;
                                \Log::info('Agregado a precisionData (Duplicado A por contenido):', $controlData);
                            } elseif (count($precisionData) == 1) {
                                $controlData['tipo'] = 'duplicado_b';
                                $precisionData[] = $controlData;
                                \Log::info('Agregado a precisionData (Duplicado B por contenido):', $controlData);
                            } else {
                                $controlData['tipo'] = 'material_referencia';
                                $accuracyData[] = $controlData;
                                \Log::info('Agregado a accuracyData (Material de Referencia por contenido):', $controlData);
                            }
                        }
                    }
                }
            }
        }

        // Debug: Log todos los datos del análisis para verificar qué se está cargando
        \Log::info('Datos del análisis rechazado cargado:', [
            'id' => $analysis->id,
            'consecutive_no' => $analysis->consecutive_no,
            'analysis_date' => $analysis->analysis_date,
            'analyst_name' => $analysis->analyst_name,
            'samples_count' => is_array($analysis->samples) ? count($analysis->samples) : (is_string($analysis->samples) ? 'string' : 'null'),
            'analytical_controls_count' => is_array($analysis->analytical_controls) ? count($analysis->analytical_controls) : (is_string($analysis->analytical_controls) ? 'string' : 'null'),
            'precision_data' => $precisionData,
            'accuracy_data' => $accuracyData,
            'all_fillable_fields' => $analysis->toArray()
        ]);

        // Pasar el análisis rechazado y los datos extraídos para que se pueda editar
        return view('lscefa::analyses.texture.batch_process', compact('processes', 'analysis', 'precisionData', 'accuracyData'));
    }

    /**
     * Actualizar un análisis de textura rechazado
     */
    public function updateRejected(Request $request, $id)
    {
        $analysis = \Modules\LSCEFA\Entities\BatchTextureAnalysis::findOrFail($id);
        
        // Verificar que el análisis esté rechazado
        if ($analysis->review_status !== 'rejected') {
            return redirect()->route('lscefa.technical.analyses.texture.index')
                ->with('error', 'Este análisis no está rechazado o ya fue corregido.');
        }

        try {
            DB::beginTransaction();

            // Validar los datos del formulario (usando los nombres de campos del formulario)
            $request->validate([
                'consecutivo_no' => 'required|string',
                'fecha_analisis' => 'required|date',
                'nombre_analista' => 'nullable|string',
                'metodologia_utilizada' => 'nullable|string',
                'codigo_termometro' => 'nullable|string',
                'codigo_hidrometro' => 'nullable|string',
                'equipment_used' => 'nullable|string',
                'method_interval' => 'nullable|string',
                'samples' => 'required|array',
                'analytical_controls' => 'required|array',
                'duplicate_a_code' => 'nullable|string',
                'duplicate_a_avg_sand' => 'nullable|numeric',
                'duplicate_a_avg_clay' => 'nullable|numeric',
                'duplicate_a_avg_silt' => 'nullable|numeric',
                'duplicate_a_dpr_sand' => 'nullable|numeric',
                'duplicate_a_dpr_clay' => 'nullable|numeric',
                'duplicate_a_dpr_silt' => 'nullable|numeric',
                'duplicate_a_acceptability' => 'nullable|string',
                'duplicate_a_observations' => 'nullable|string',
                'duplicate_b_code' => 'nullable|string',
                'duplicate_b_avg_sand' => 'nullable|numeric',
                'duplicate_b_avg_clay' => 'nullable|numeric',
                'duplicate_b_avg_silt' => 'nullable|numeric',
                'duplicate_b_dpr_sand' => 'nullable|numeric',
                'duplicate_b_dpr_clay' => 'nullable|numeric',
                'duplicate_b_dpr_silt' => 'nullable|numeric',
                'duplicate_b_acceptability' => 'nullable|string',
                'duplicate_b_observations' => 'nullable|string',
                'reference_material_expected_sand' => 'nullable|numeric',
                'reference_material_expected_clay' => 'nullable|numeric',
                'reference_material_expected_silt' => 'nullable|numeric',
                'reference_material_obtained_sand' => 'nullable|numeric',
                'reference_material_obtained_clay' => 'nullable|numeric',
                'reference_material_obtained_silt' => 'nullable|numeric',
                'reference_material_error_percent' => 'nullable|numeric',
                'reference_material_acceptability' => 'nullable|string',
                'reference_material_observations' => 'nullable|string',
                'general_observations' => 'nullable|string',
            ]);

            // Actualizar el análisis con los nuevos datos (usando los nombres de campos del formulario)
            $analysis->update([
                'consecutive_no' => $request->consecutivo_no,
                'analysis_date' => $request->fecha_analisis,
                'analyst_name' => $request->nombre_analista,
                'methodology_used' => $request->metodologia_utilizada,
                'thermometer_code' => $request->codigo_termometro,
                'hydrometer_code' => $request->codigo_hidrometro,
                'equipment_used' => $request->equipment_used,
                'method_interval' => $request->method_interval,
                'samples' => json_encode($request->samples),
                'analytical_controls' => json_encode($request->analytical_controls),
                'duplicate_a_code' => $request->duplicate_a_code,
                'duplicate_a_avg_sand' => $request->duplicate_a_avg_sand,
                'duplicate_a_avg_clay' => $request->duplicate_a_avg_clay,
                'duplicate_a_avg_silt' => $request->duplicate_a_avg_silt,
                'duplicate_a_dpr_sand' => $request->duplicate_a_dpr_sand,
                'duplicate_a_dpr_clay' => $request->duplicate_a_dpr_clay,
                'duplicate_a_dpr_silt' => $request->duplicate_a_dpr_silt,
                'duplicate_a_acceptability' => $request->duplicate_a_acceptability,
                'duplicate_a_observations' => $request->duplicate_a_observations,
                'duplicate_b_code' => $request->duplicate_b_code,
                'duplicate_b_avg_sand' => $request->duplicate_b_avg_sand,
                'duplicate_b_avg_clay' => $request->duplicate_b_avg_clay,
                'duplicate_b_avg_silt' => $request->duplicate_b_avg_silt,
                'duplicate_b_dpr_sand' => $request->duplicate_b_dpr_sand,
                'duplicate_b_dpr_clay' => $request->duplicate_b_dpr_clay,
                'duplicate_b_dpr_silt' => $request->duplicate_b_dpr_silt,
                'duplicate_b_acceptability' => $request->duplicate_b_acceptability,
                'duplicate_b_observations' => $request->duplicate_b_observations,
                'reference_material_expected_sand' => $request->reference_material_expected_sand,
                'reference_material_expected_clay' => $request->reference_material_expected_clay,
                'reference_material_expected_silt' => $request->reference_material_expected_silt,
                'reference_material_obtained_sand' => $request->reference_material_obtained_sand,
                'reference_material_obtained_clay' => $request->reference_material_obtained_clay,
                'reference_material_obtained_silt' => $request->reference_material_obtained_silt,
                'reference_material_error_percent' => $request->reference_material_error_percent,
                'reference_material_acceptability' => $request->reference_material_acceptability,
                'reference_material_observations' => $request->reference_material_observations,
                'general_observations' => $request->general_observations,
                'review_status' => 'pending', // Cambiar a pending para que vuelva a revisión
                'review_observations' => null, // Limpiar observaciones anteriores
                'reviewed_by' => null,
                'review_date' => null,
            ]);

            // Actualizar el estado del ServiceProcessDetail a completed
            $spd = \Modules\LSCEFA\Models\ServiceProcessDetail::where('process_id', $analysis->process_id)
                ->where('service_id', $analysis->service_id)
                ->first();
            
            if ($spd) {
                $spd->update(['status' => 'completed']);
            }

            DB::commit();

            return redirect()->route('lscefa.technical.analyses.texture.index')
                ->with('success', 'Análisis de textura corregido exitosamente. Ha sido enviado nuevamente para revisión.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating rejected texture analysis', [
                'analysis_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withInput()->with('error', 'Error al actualizar el análisis: ' . $e->getMessage());
        }
    }
}
