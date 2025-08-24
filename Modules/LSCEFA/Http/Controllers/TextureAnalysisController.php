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
        $analysis = \Modules\LSCEFA\Entities\BatchTextureAnalysis::with(['analyticalControls'])->findOrFail($id);
        
        // Generar reporte PDF
        return $this->generateTextureReport($analysis);
    }

    /**
     * Generar reporte PDF de análisis de textura
     */
    private function generateTextureReport($analysis)
    {
        // Crear el PDF usando TCPDF o similar
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Configurar información del documento
        $pdf->SetCreator('LSCEFA');
        $pdf->SetAuthor('Laboratorio de Ciencias Básicas');
        $pdf->SetTitle('Reporte de Análisis de Textura - ' . $analysis->consecutive_no);
        $pdf->SetSubject('Análisis de Textura');
        
        // Configurar márgenes
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(10);
        
        // Configurar auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 25);
        
        // Agregar página
        $pdf->AddPage();
        
        // Contenido del reporte
        $html = $this->generateTextureReportHTML($analysis);
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Generar nombre del archivo
        $filename = 'Reporte_Textura_' . $analysis->consecutive_no . '_' . date('Y-m-d') . '.pdf';
        
        // Descargar el PDF
        return $pdf->Output($filename, 'D');
    }

    /**
     * Generar HTML para el reporte de textura
     */
    private function generateTextureReportHTML($analysis)
    {
        $samples = is_string($analysis->samples) ? json_decode($analysis->samples, true) : $analysis->samples;
        $analyticalControls = is_string($analysis->analytical_controls) ? json_decode($analysis->analytical_controls, true) : $analysis->analytical_controls;
        
        $html = '
        <style>
            body { font-family: Arial, sans-serif; font-size: 12px; }
            .header { text-align: center; margin-bottom: 20px; }
            .title { font-size: 18px; font-weight: bold; margin-bottom: 10px; }
            .subtitle { font-size: 14px; margin-bottom: 20px; }
            .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            .info-table td { border: 1px solid #ddd; padding: 8px; }
            .info-table .label { font-weight: bold; background-color: #f5f5f5; }
            .results-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            .results-table th, .results-table td { border: 1px solid #ddd; padding: 8px; text-align: center; }
            .results-table th { background-color: #f0f0f0; font-weight: bold; }
            .footer { margin-top: 30px; font-size: 10px; text-align: center; }
        </style>
        
        <div class="header">
            <div class="title">LABORATORIO DE CIENCIAS BÁSICAS</div>
            <div class="subtitle">REPORTE DE ANÁLISIS DE TEXTURA</div>
        </div>
        
        <table class="info-table">
            <tr>
                <td class="label" width="30%">Consecutivo No.:</td>
                <td width="70%">' . ($analysis->consecutive_no ?? 'N/A') . '</td>
            </tr>
            <tr>
                <td class="label">Fecha del Análisis:</td>
                <td>' . ($analysis->analysis_date ?? 'N/A') . '</td>
            </tr>
            <tr>
                <td class="label">Analista:</td>
                <td>' . ($analysis->analyst_name ?? 'N/A') . '</td>
            </tr>
            <tr>
                <td class="label">Metodología:</td>
                <td>' . ($analysis->methodology_used ?? 'Hidrómetro de Bouyoucos') . '</td>
            </tr>
        </table>
        
        <h3>Resultados de Análisis de Muestras</h3>
        <table class="results-table">
            <thead>
                <tr>
                    <th>Código Interno</th>
                    <th>Peso (g)</th>
                    <th>% Arena</th>
                    <th>% Arcilla</th>
                    <th>% Limo</th>
                    <th>Clase Textural</th>
                </tr>
            </thead>
            <tbody>';
        
        if (is_array($samples)) {
            foreach ($samples as $sample) {
                if (is_array($sample) && isset($sample['codigo_interno']) && $sample['codigo_interno'] !== 'Blanco del proceso') {
                    $html .= '
                    <tr>
                        <td>' . ($sample['codigo_interno'] ?? 'N/A') . '</td>
                        <td>' . ($sample['peso'] ?? 'N/A') . '</td>
                        <td>' . ($sample['porcentaje_arena'] ?? 'N/A') . '</td>
                        <td>' . ($sample['porcentaje_arcilla'] ?? 'N/A') . '</td>
                        <td>' . ($sample['porcentaje_limo'] ?? 'N/A') . '</td>
                        <td>' . ($sample['clase_textural'] ?? 'N/A') . '</td>
                    </tr>';
                }
            }
        }
        
        $html .= '
            </tbody>
        </table>
        
        <div class="footer">
            <p>Reporte generado el ' . date('d/m/Y H:i:s') . '</p>
            <p>Documento: NTC 5264:2023 - Determinación de Textura por Hidrómetro de Bouyoucos</p>
        </div>';
        
        return $html;
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

        // Cargar los datos del blanco del proceso si existen
        $blancoData = null;
        if (isset($analysis->samples)) {
            $samples = is_string($analysis->samples) ? json_decode($analysis->samples, true) : $analysis->samples;
            if (is_array($samples) && count($samples) > 0) {
                $blancoData = $samples[0] ?? null; // El primer elemento es el blanco
            }
        }

        // Pasar el análisis rechazado y los datos extraídos para que se pueda editar
        return view('lscefa::analyses.texture.batch_process', compact('processes', 'analysis', 'precisionData', 'accuracyData', 'blancoData'));
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

        // Log para debug - ver todos los datos que llegan
        Log::info('Datos recibidos en updateRejected:', $request->all());

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
                'analyses' => 'required|array',
                'analyses.*.items' => 'required|array',
                'analyses.*.analytical_controls' => 'nullable|array',
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

            // Extraer datos del formulario
            $analysesInput = $request->input('analyses');
            $firstAnalysis = $analysesInput[0] ?? null; // Tomar el primer análisis
            
            if (!$firstAnalysis) {
                throw new \Exception('No se encontraron datos de análisis en el formulario.');
            }

            // Preparar datos de muestras y controles
            $samples = isset($firstAnalysis['items']) ? $firstAnalysis['items'] : [];
            $analyticalControls = isset($firstAnalysis['analytical_controls']) ? $firstAnalysis['analytical_controls'] : [];

            // Log para debug
            Log::info('Datos extraídos:', [
                'samples_count' => count($samples),
                'analytical_controls_count' => count($analyticalControls),
                'first_analysis' => $firstAnalysis
            ]);

            // Actualizar el análisis con los nuevos datos
            $analysis->update([
                'consecutive_no' => $request->consecutivo_no,
                'analysis_date' => $request->fecha_analisis,
                'analyst_name' => $request->nombre_analista,
                'methodology_used' => $request->metodologia_utilizada,
                'thermometer_code' => $request->codigo_termometro,
                'hydrometer_code' => $request->codigo_hidrometro,
                'equipment_used' => $firstAnalysis['equipment_used'] ?? null,
                'method_interval' => $firstAnalysis['method_interval'] ?? null,
                'samples' => json_encode($samples),
                'analytical_controls' => json_encode($analyticalControls),
                'duplicate_a_code' => $firstAnalysis['duplicate_a_code'] ?? null,
                'duplicate_a_avg_sand' => $firstAnalysis['duplicate_a_avg_sand'] ?? null,
                'duplicate_a_avg_clay' => $firstAnalysis['duplicate_a_avg_clay'] ?? null,
                'duplicate_a_avg_silt' => $firstAnalysis['duplicate_a_avg_silt'] ?? null,
                'duplicate_a_dpr_sand' => $firstAnalysis['duplicate_a_dpr_sand'] ?? null,
                'duplicate_a_dpr_clay' => $firstAnalysis['duplicate_a_dpr_clay'] ?? null,
                'duplicate_a_dpr_silt' => $firstAnalysis['duplicate_a_dpr_silt'] ?? null,
                'duplicate_a_acceptability' => $firstAnalysis['duplicate_a_acceptability'] ?? null,
                'duplicate_a_observations' => $firstAnalysis['duplicate_a_observations'] ?? null,
                'duplicate_b_code' => $firstAnalysis['duplicate_b_code'] ?? null,
                'duplicate_b_avg_sand' => $firstAnalysis['duplicate_b_avg_sand'] ?? null,
                'duplicate_b_avg_clay' => $firstAnalysis['duplicate_b_avg_clay'] ?? null,
                'duplicate_b_avg_silt' => $firstAnalysis['duplicate_b_avg_silt'] ?? null,
                'duplicate_b_dpr_sand' => $firstAnalysis['duplicate_b_dpr_sand'] ?? null,
                'duplicate_b_dpr_clay' => $firstAnalysis['duplicate_b_dpr_clay'] ?? null,
                'duplicate_b_dpr_silt' => $firstAnalysis['duplicate_b_dpr_silt'] ?? null,
                'duplicate_b_acceptability' => $firstAnalysis['duplicate_b_acceptability'] ?? null,
                'duplicate_b_observations' => $firstAnalysis['duplicate_b_observations'] ?? null,
                'reference_material_expected_sand' => $firstAnalysis['reference_material_expected_sand'] ?? null,
                'reference_material_expected_clay' => $firstAnalysis['reference_material_expected_clay'] ?? null,
                'reference_material_expected_silt' => $firstAnalysis['reference_material_expected_silt'] ?? null,
                'reference_material_obtained_sand' => $firstAnalysis['reference_material_obtained_sand'] ?? null,
                'reference_material_obtained_clay' => $firstAnalysis['reference_material_obtained_clay'] ?? null,
                'reference_material_obtained_silt' => $firstAnalysis['reference_material_obtained_silt'] ?? null,
                'reference_material_error_percent' => $firstAnalysis['reference_material_error_percent'] ?? null,
                'reference_material_acceptability' => $firstAnalysis['reference_material_acceptability'] ?? null,
                'reference_material_observations' => $firstAnalysis['reference_material_observations'] ?? null,
                'general_observations' => $firstAnalysis['general_observations'] ?? null,
                'review_status' => 'pending', // Cambiar a pending para que vuelva a revisión
                'review_observations' => null, // Limpiar observaciones anteriores
                'reviewed_by' => null,
                'review_date' => null,
            ]);

            // Actualizar o crear controles analíticos en la tabla analytical_controls
            if (!empty($analyticalControls)) {
                // Eliminar controles analíticos existentes para este análisis
                \Modules\LSCEFA\Entities\AnalyticalControl::where('analysis_id', $analysis->id)
                    ->where('analysis_type', 'texture')
                    ->delete();

                // Crear nuevos controles analíticos
                foreach ($analyticalControls as $control) {
                    \Modules\LSCEFA\Entities\AnalyticalControl::create([
                        'analysis_id' => $analysis->id,
                        'process_id' => $analysis->process_id,
                        'analysis_type' => 'texture',
                        'controles_analiticos' => json_encode($control),
                    ]);
                }
            }

            // Actualizar el estado del ServiceProcessDetail a completed
            $spd = \Modules\LSCEFA\Models\ServiceProcessDetail::where('process_id', $analysis->process_id)
                ->where('service_id', $analysis->service_id)
                ->first();
            
            if ($spd) {
                $spd->update(['status' => 'completed']);
            }

            DB::commit();

            Log::info('Análisis de textura actualizado exitosamente', [
                'analysis_id' => $analysis->id,
                'new_status' => 'pending',
                'samples_count' => count($samples),
                'analytical_controls_count' => count($analyticalControls)
            ]);

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

    /**
     * Descarga el informe de análisis de textura en formato Excel
     */
    public function downloadTextureReport($analysisId)
    {
        $textureAnalysis = BatchTextureAnalysis::findOrFail($analysisId);
        
        // Crear el archivo Excel usando PhpSpreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Configurar encabezados del informe
        $sheet->setCellValue('A1', 'LABORATORIO DE CIENCIAS BÁSICAS');
        $sheet->setCellValue('A2', 'PROCEDIMIENTO DETERMINACIÓN DE TEXTURA EN SUELOS');
        $sheet->setCellValue('A3', 'FORMATO REPORTE RESULTADOS TEXTURA EN SUELOS');
        $sheet->setCellValue('D3', 'Versión: 1');
        $sheet->setCellValue('D4', 'Código: F-TSS-001');
        $sheet->setCellValue('D5', 'Página: 1 de 1');

        // Información general del análisis
        $sheet->setCellValue('A6', 'Consecutivo No.:');
        $sheet->setCellValue('B6', $textureAnalysis->consecutive_no);
        $sheet->setCellValue('A7', 'Fecha del análisis:');
        $sheet->setCellValue('B7', $textureAnalysis->analysis_date);
        $sheet->setCellValue('A8', 'Nombre Analista:');
        $sheet->setCellValue('B8', $textureAnalysis->analyst_name);
        $sheet->setCellValue('A9', 'Metodología Utilizada:');
        $sheet->setCellValue('B9', $textureAnalysis->methodology_used);
        $sheet->setCellValue('A10', 'Código Termómetro:');
        $sheet->setCellValue('B10', $textureAnalysis->thermometer_code);
        $sheet->setCellValue('A11', 'Código Hidrómetro:');
        $sheet->setCellValue('B11', $textureAnalysis->hydrometer_code);

        // Controles analíticos
        $sheet->setCellValue('A13', 'Controles analíticos');
        $sheet->setCellValue('A14', 'Identificación');
        $sheet->setCellValue('B14', 'Valor esperado Arena (%)');
        $sheet->setCellValue('C14', 'Valor leído Arena (%)');
        $sheet->setCellValue('D14', 'Valor esperado Limo (%)');
        $sheet->setCellValue('E14', 'Valor leído Limo (%)');
        $sheet->setCellValue('F14', 'Valor esperado Arcilla (%)');
        $sheet->setCellValue('G14', 'Valor leído Arcilla (%)');
        $sheet->setCellValue('H14', '% Error');
        $sheet->setCellValue('I14', 'Aceptabilidad del control');

        $row = 15;
        $analyticalControls = is_string($textureAnalysis->analytical_controls) ? 
            json_decode($textureAnalysis->analytical_controls, true) : 
            $textureAnalysis->analytical_controls;

        if (is_array($analyticalControls)) {
            foreach ($analyticalControls as $control) {
                $sheet->setCellValue('A' . $row, $control['identificacion'] ?? '');
                $sheet->setCellValue('B' . $row, $control['valor_esperado_arena'] ?? '');
                $sheet->setCellValue('C' . $row, $control['valor_leido_arena'] ?? '');
                $sheet->setCellValue('D' . $row, $control['valor_esperado_limo'] ?? '');
                $sheet->setCellValue('E' . $row, $control['valor_leido_limo'] ?? '');
                $sheet->setCellValue('F' . $row, $control['valor_esperado_arcilla'] ?? '');
                $sheet->setCellValue('G' . $row, $control['valor_leido_arcilla'] ?? '');
                $sheet->setCellValue('H' . $row, $control['porcentaje_error'] ?? '');
                $sheet->setCellValue('I' . $row, $control['aceptabilidad_error'] ?? '');
                $row++;
            }
        }

        // Precisión analítica (duplicados)
        $row += 2;
        $sheet->setCellValue('A' . $row, 'Precisión analítica');
        $sheet->setCellValue('A' . ($row + 1), 'Identificación');
        $sheet->setCellValue('B' . ($row + 1), 'Promedio Arena (%)');
        $sheet->setCellValue('C' . ($row + 1), 'Promedio Limo (%)');
        $sheet->setCellValue('D' . ($row + 1), 'Promedio Arcilla (%)');
        $sheet->setCellValue('E' . ($row + 1), 'DPR Arena (%)');
        $sheet->setCellValue('F' . ($row + 1), 'DPR Limo (%)');
        $sheet->setCellValue('G' . ($row + 1), 'DPR Arcilla (%)');
        $sheet->setCellValue('H' . ($row + 1), 'Aceptabilidad');

        $row += 2;
        // Duplicado A
        $sheet->setCellValue('A' . $row, $textureAnalysis->duplicate_a_code ?? '');
        $sheet->setCellValue('B' . $row, $textureAnalysis->duplicate_a_avg_sand ?? '');
        $sheet->setCellValue('C' . $row, $textureAnalysis->duplicate_a_avg_silt ?? '');
        $sheet->setCellValue('D' . $row, $textureAnalysis->duplicate_a_avg_clay ?? '');
        $sheet->setCellValue('E' . $row, $textureAnalysis->duplicate_a_dpr_sand ?? '');
        $sheet->setCellValue('F' . $row, $textureAnalysis->duplicate_a_dpr_silt ?? '');
        $sheet->setCellValue('G' . $row, $textureAnalysis->duplicate_a_dpr_clay ?? '');
        $sheet->setCellValue('H' . $row, $textureAnalysis->duplicate_a_acceptability ?? '');

        $row++;
        // Duplicado B
        $sheet->setCellValue('A' . $row, $textureAnalysis->duplicate_b_code ?? '');
        $sheet->setCellValue('B' . $row, $textureAnalysis->duplicate_b_avg_sand ?? '');
        $sheet->setCellValue('C' . $row, $textureAnalysis->duplicate_b_avg_silt ?? '');
        $sheet->setCellValue('D' . $row, $textureAnalysis->duplicate_b_avg_clay ?? '');
        $sheet->setCellValue('E' . $row, $textureAnalysis->duplicate_b_dpr_sand ?? '');
        $sheet->setCellValue('F' . $row, $textureAnalysis->duplicate_b_dpr_silt ?? '');
        $sheet->setCellValue('G' . $row, $textureAnalysis->duplicate_b_dpr_clay ?? '');
        $sheet->setCellValue('H' . $row, $textureAnalysis->duplicate_b_acceptability ?? '');

        // Material de referencia
        $row += 2;
        $sheet->setCellValue('A' . $row, 'Material de referencia');
        $sheet->setCellValue('A' . ($row + 1), 'Valor esperado Arena (%)');
        $sheet->setCellValue('B' . ($row + 1), 'Valor obtenido Arena (%)');
        $sheet->setCellValue('C' . ($row + 1), 'Valor esperado Limo (%)');
        $sheet->setCellValue('D' . ($row + 1), 'Valor obtenido Limo (%)');
        $sheet->setCellValue('E' . ($row + 1), 'Valor esperado Arcilla (%)');
        $sheet->setCellValue('F' . ($row + 1), 'Valor obtenido Arcilla (%)');
        $sheet->setCellValue('G' . ($row + 1), '% Error');
        $sheet->setCellValue('H' . ($row + 1), 'Aceptabilidad');

        $row += 2;
        $sheet->setCellValue('A' . $row, 'Material de referencia');
        $sheet->setCellValue('B' . $row, $textureAnalysis->reference_material_expected_sand ?? '');
        $sheet->setCellValue('C' . $row, $textureAnalysis->reference_material_obtained_sand ?? '');
        $sheet->setCellValue('D' . $row, $textureAnalysis->reference_material_expected_silt ?? '');
        $sheet->setCellValue('E' . $row, $textureAnalysis->reference_material_obtained_silt ?? '');
        $sheet->setCellValue('F' . $row, $textureAnalysis->reference_material_expected_clay ?? '');
        $sheet->setCellValue('G' . $row, $textureAnalysis->reference_material_obtained_clay ?? '');
        $sheet->setCellValue('H' . $row, $textureAnalysis->reference_material_error_percent ?? '');
        $sheet->setCellValue('I' . $row, $textureAnalysis->reference_material_acceptability ?? '');

        // Ítems de ensayo (muestras)
        $row += 3;
        $sheet->setCellValue('A' . $row, 'Ítems de ensayo');
        $sheet->setCellValue('A' . ($row + 1), 'Código interno');
        $sheet->setCellValue('B' . ($row + 1), 'Peso Arena (g)');
        $sheet->setCellValue('C' . ($row + 1), 'Peso Limo (g)');
        $sheet->setCellValue('D' . ($row + 1), 'Peso Arcilla (g)');
        $sheet->setCellValue('E' . ($row + 1), 'Peso Total (g)');
        $sheet->setCellValue('F' . ($row + 1), '% Arena');
        $sheet->setCellValue('G' . ($row + 1), '% Limo');
        $sheet->setCellValue('H' . ($row + 1), '% Arcilla');
        $sheet->setCellValue('I' . ($row + 1), 'Clase Textural');
        $sheet->setCellValue('J' . ($row + 1), 'Observaciones');

        $row += 2;
        $samples = is_string($textureAnalysis->samples) ? 
            json_decode($textureAnalysis->samples, true) : 
            $textureAnalysis->samples;

        if (is_array($samples)) {
            foreach ($samples as $sample) {
                if (is_array($sample) && isset($sample['codigo_interno']) && $sample['codigo_interno'] !== 'Blanco del proceso') {
                    $sheet->setCellValue('A' . $row, $sample['codigo_interno'] ?? '');
                    $sheet->setCellValue('B' . $row, $sample['peso_arena'] ?? '');
                    $sheet->setCellValue('C' . $row, $sample['peso_limo'] ?? '');
                    $sheet->setCellValue('D' . $row, $sample['peso_arcilla'] ?? '');
                    $sheet->setCellValue('E' . $row, $sample['peso_total'] ?? '');
                    $sheet->setCellValue('F' . $row, $sample['porcentaje_arena'] ?? '');
                    $sheet->setCellValue('G' . $row, $sample['porcentaje_limo'] ?? '');
                    $sheet->setCellValue('H' . $row, $sample['porcentaje_arcilla'] ?? '');
                    $sheet->setCellValue('I' . $row, $sample['clase_textural'] ?? '');
                    $sheet->setCellValue('J' . $row, $sample['observaciones'] ?? '');
                    $row++;
                }
            }
        }

        // Observaciones generales
        $row += 2;
        $sheet->setCellValue('A' . $row, 'Observaciones generales:');
        $sheet->setCellValue('A' . ($row + 1), $textureAnalysis->general_observations ?? '');

        // Auto-size columns
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Crear el archivo Excel
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'reporte_textura_' . $textureAnalysis->consecutive_no . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
}
