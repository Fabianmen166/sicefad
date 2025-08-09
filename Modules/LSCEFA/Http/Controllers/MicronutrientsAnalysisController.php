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

class MicronutrientsAnalysisController extends Controller
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
        try {
            DB::beginTransaction();

            $request->validate([
                'processes' => 'required|array',
                'processes.*.process_id' => 'required|string',
                'processes.*.service_id' => 'required|integer',
                'processes.*.consecutivo_no' => 'required|string',
                'processes.*.fecha_analisis' => 'required|date',
                'processes.*.equipo_utilizado' => 'nullable|string',
                'processes.*.intervalo_metodo' => 'nullable|string',
                'processes.*.analista' => 'nullable|string',
                'processes.*.controles_analiticos' => 'required|array',
                'processes.*.items' => 'required|array',
            ]);

            // Validar que no se procesen más de 10 procesos a la vez
            if (count($request->processes) > 10) {
                return back()->with('error', 'No se pueden procesar más de 10 procesos a la vez. Por favor, reduce la cantidad de procesos.')
                    ->withInput();
            }

            $savedCount = 0;

            foreach ($request->processes as $processData) {
                $processId = $processData['process_id'];
                $serviceId = $processData['service_id'];

                // Create or update micronutrients analysis
                $micronutrientsAnalysis = MicronutrientsAnalysis::updateOrCreate(
                    ['process_id' => $processId, 'service_id' => $serviceId],
                    [
                        'consecutivo_no' => $processData['consecutivo_no'],
                        'fecha_analisis' => $processData['fecha_analisis'],
                        'equipo_utilizado' => $processData['equipo_utilizado'] ?? null,
                        'intervalo_metodo' => $processData['intervalo_metodo'] ?? null,
                        'analista' => $processData['analista'] ?? Auth::user()->name,
                    ]
                );

                // Save analytical controls
                foreach ($processData['controles_analiticos'] as $control) {
                    AnalyticalControl::updateOrCreate(
                        [
                            'analysis_type' => 'micronutrients',
                            'analysis_id' => $micronutrientsAnalysis->id,
                            'identificacion' => $control['identificacion']
                        ],
                        [
                            'valor_esperado' => $control['valor_esperado'] ?? null,
                            'valor_leido' => $control['valor_leido'] ?? null,
                            'porcentaje_error' => $control['porcentaje_error'] ?? null,
                            'aceptabilidad_error' => $control['aceptabilidad_error'] ?? null,
                            'porcentaje_recuperacion' => $control['porcentaje_recuperacion'] ?? null,
                            'aceptabilidad_recuperacion' => $control['aceptabilidad_recuperacion'] ?? null,
                            'porcentaje_dpr' => $control['porcentaje_dpr'] ?? null,
                            'aceptabilidad_dpr' => $control['aceptabilidad_dpr'] ?? null,
                        ]
                    );
                }

                // Save test items
                foreach ($processData['items'] as $item) {
                    $micronutrientsAnalysis->items()->updateOrCreate(
                        [
                            'codigo_interno' => $item['codigo_interno'] ?? 'Item-' . uniqid(),
                        ],
                        [
                            'peso_muestra' => $item['peso_muestra'] ?? null,
                            'vol_extractante' => $item['vol_extractante'] ?? null,
                            'lectura_blanco' => $item['lectura_blanco'] ?? null,
                            'factor_dilucion' => $item['factor_dilucion'] ?? null,
                            'concentracion_mg_l' => $item['concentracion_mg_l'] ?? null,
                            'concentracion_mg_kg' => $item['concentracion_mg_kg'] ?? null,
                            'observaciones_item' => $item['observaciones_item'] ?? null,
                        ]
                    );
                }

                // Update service process detail status
                ServiceProcessDetail::where('process_id', $processId)
                    ->where('service_id', $serviceId)
                    ->update(['status' => 'completed']);

                $savedCount++;
            }

            DB::commit();

            Log::info('Procesamiento en lote de micronutrientes completado', [
                'saved_count' => $savedCount,
                'user_id' => Auth::id()
            ]);

            return redirect()->route('lscefa.technical.analyses.micronutrients.index')
                ->with('success', "Se procesaron exitosamente {$savedCount} análisis de micronutrientes.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en procesamiento en lote de micronutrientes: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'exception' => $e
            ]);

            return back()->with('error', 'Error al procesar los análisis en lote. Por favor, intente nuevamente.')
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
