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
        Log::info('BatchStore called', ['request_data' => $request->all()]);
        $request->validate([
            'analyses' => 'required|array',
            'analyses.*.process_id' => 'required|exists:processes,process_id',
            'analyses.*.service_id' => 'required|exists:services,services_id',
        ]);
        Log::info('DEBUG: validación pasada');

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
}
