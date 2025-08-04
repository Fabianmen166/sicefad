<?php

namespace Modules\LSCEFA\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Modules\LSCEFA\Entities\CationicAnalysis;
use Modules\LSCEFA\Entities\AnalyticalControl;
use Illuminate\Support\Facades\Log;
use Modules\LSCEFA\Models\Process;
use Modules\LSCEFA\Models\Service;
use Modules\LSCEFA\Models\ServiceProcessDetail;

class CationicAnalysisController extends Controller
{
    public function index()
    {
        try {
            Log::info('Usuario accede a index de análisis de intercambio catiónico', [
                'user_id' => Auth::id(),
                'role' => Auth::user()->role ?? 'N/A'
            ]);

            // 🔍 Buscar el ID del servicio de INTERCAMBIO CATIÓNICO por su descripción
            $cationicService = Service::whereRaw('LOWER(descripcion) LIKE ?', ['%intercambio%'])
                                    ->orWhereRaw('LOWER(descripcion) LIKE ?', ['%catiónico%'])
                                    ->orWhereRaw('LOWER(descripcion) LIKE ?', ['%cationic%'])
                                    ->orWhereRaw('LOWER(descripcion) = ?', ['intercambio catiónico'])
                                    ->first();

            if (!$cationicService) {
                Log::warning('No se encontró el servicio de intercambio catiónico');
                $processes = collect();
                $cationicAnalyses = collect();
            } else {
                Log::info('Servicio encontrado', [
                    'service_id' => $cationicService->services_id,
                    'descripcion' => $cationicService->descripcion
                ]);
                // 🔍 Procesos que tienen el servicio de intercambio catiónico PENDIENTE
                $processes = Process::whereHas('serviceProcessDetails', function ($query) use ($cationicService) {
                        $query->where('service_id', $cationicService->services_id)
                              ->where('status', 'pending');
                    })
                    ->with('cationicAnalyses') // Relación con CationicAnalysis
                    ->get();

                Log::info('Procesos encontrados', [
                    'processes_count' => $processes->count(),
                    'process_ids' => $processes->pluck('process_id')->toArray()
                ]);

                // 🔍 Análisis de intercambio catiónico relacionados a procesos con servicio pendiente
                $cationicAnalyses = CationicAnalysis::with('process.serviceProcessDetails')
                    ->whereHas('process.serviceProcessDetails', function ($query) use ($cationicService) {
                        $query->where('service_id', $cationicService->services_id)
                              ->where('status', 'pending');
                    })
                    ->get();

                Log::info('Análisis encontrados', [
                    'cationic_analyses_count' => $cationicAnalyses->count(),
                    'analysis_ids' => $cationicAnalyses->pluck('id')->toArray()
                ]);
            }

            Log::info('Datos encontrados', [
                'processes_count' => $processes->count(),
                'cationic_analyses_count' => $cationicAnalyses->count()
            ]);

            return view('lscefa::analyses.cationic.index', compact('cationicAnalyses', 'processes'));

        } catch (\Exception $e) {
            Log::error('Error al cargar el índice de análisis de intercambio catiónico: ' . $e->getMessage());
            return back()->with('error', 'No se pudo cargar el listado.');
        }
    }

    public function cationicAnalysis($processId, $serviceId)
    {
        $process = Process::where('process_id', (string)$processId)->firstOrFail();
        $service = Service::findOrFail($serviceId);

        return view('lscefa::analyses.cationic.process', compact('process', 'service'));
    }

    public function storeCationicAnalysis(Request $request)
    {
        // Procesar campos numéricos para evitar comas
        $numericFields = [
            'peso_muestra', 'vol_naoh_muestra', 'vol_naoh_blanco', 'normalidad_naoh', 'humedad_porcentaje', 'cic_resultado',
            'blanco_lcm', 'blanco_valor_leido', 'error_valor_teorico', 'error_valor_leido', 'error_porcentaje',
            'recuperacion_valor_teorico', 'recuperacion_valor_leido', 'recuperacion_porcentaje',
            'dpr_replica1', 'dpr_replica2', 'dpr_porcentaje'
        ];

        foreach ($numericFields as $field) {
            if ($request->has($field)) {
                // If it's an array field (like peso_muestra[]), process each element
                if (is_array($request->input($field))) {
                    $processedValues = [];
                    foreach ($request->input($field) as $value) {
                        $processedValues[] = str_replace(',', '.', $value);
                    }
                    $request->merge([$field => $processedValues]);
                } else {
                    // Otherwise, process as a single value
                    $value = str_replace(',', '.', $request->input($field));
                    $request->merge([$field => $value]);
                }
            }
        }

        $processId = $request->input('process_id');
        $serviceId = $request->input('service_id');

        // Validación para los campos del formulario
        try {
            $request->validate([
                'consecutivo_no' => 'required|string',
                'fecha_analisis' => 'required|date',
                'unidades_reporte_equipo' => 'required|string',
                'nombre_metodo' => 'required|string',
                'equipo_utilizado' => 'required|string',
                'intervalo_metodo' => 'required|string',
                'nombre_analista' => 'required|string',
                'resolucion_instrumental' => 'nullable|string',
                'observaciones' => 'nullable|string',
                
                // Campos de resultados (arrays)
                'codigo_interno' => 'required|array',
                'codigo_interno.*' => 'required|string',
                'peso_muestra' => 'required|array',
                'peso_muestra.*' => 'required|numeric|min:0',
                'vol_naoh_muestra' => 'required|array',
                'vol_naoh_muestra.*' => 'required|numeric|min:0',
                'vol_naoh_blanco' => 'required|array',
                'vol_naoh_blanco.*' => 'required|numeric|min:0',
                'normalidad_naoh' => 'required|array',
                'normalidad_naoh.*' => 'required|numeric|min:0',
                'humedad_porcentaje' => 'required|array',
                'humedad_porcentaje.*' => 'required|numeric|min:0',
                'cic_resultado' => 'required|array',
                'cic_resultado.*' => 'required|numeric',
                
                // Campos de controles de calidad
                'blanco_identificacion' => 'nullable|string',
                'blanco_lcm' => 'nullable|numeric',
                'blanco_valor_leido' => 'nullable|numeric',
                'blanco_aceptable' => 'nullable|string',
                'blanco_observaciones' => 'nullable|string',
                
                'error_identificacion' => 'nullable|string',
                'error_valor_teorico' => 'nullable|numeric',
                'error_valor_leido' => 'nullable|numeric',
                'error_porcentaje' => 'nullable|numeric',
                'error_aceptable' => 'nullable|string',
                'error_observaciones' => 'nullable|string',
                
                'recuperacion_identificacion' => 'nullable|string',
                'recuperacion_valor_teorico' => 'nullable|numeric',
                'recuperacion_valor_leido' => 'nullable|numeric',
                'recuperacion_porcentaje' => 'nullable|numeric',
                'recuperacion_aceptable' => 'nullable|string',
                'recuperacion_observaciones' => 'nullable|string',
                
                'dpr_identificacion' => 'nullable|string',
                'dpr_replica1' => 'nullable|numeric',
                'dpr_replica2' => 'nullable|numeric',
                'dpr_porcentaje' => 'nullable|numeric',
                'dpr_aceptable' => 'nullable|string',
                'dpr_observaciones' => 'nullable|string',
            ]);
            
            Log::info('Validación exitosa');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            return back()->withErrors($e->errors())->withInput();
        }

        DB::beginTransaction();

        try {
            Log::info('Iniciando guardado de análisis de intercambio catiónico', [
                'process_id' => $processId,
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);

            // Verificar si ya existe un control analítico para este proceso (solo debe haber uno por proceso)
            $existingControl = AnalyticalControl::where('process_id', $processId)->first();
            if ($existingControl) {
                Log::warning('Ya existe un control analítico para este proceso', [
                    'process_id' => $processId,
                    'control_id' => $existingControl->id
                ]);
                return back()->with('error', 'Ya existe un control analítico para este proceso.');
            }

            // Guardar múltiples análisis de CIC (uno por cada fila de resultados)
            $cationicAnalyses = [];
            $codigosInternos = $request->input('codigo_interno', []);
            $pesosMuestra = $request->input('peso_muestra', []);
            $volNaohMuestras = $request->input('vol_naoh_muestra', []);
            $volNaohBlancos = $request->input('vol_naoh_blanco', []);
            $normalidadesNaoh = $request->input('normalidad_naoh', []);
            $humedadesPorcentaje = $request->input('humedad_porcentaje', []);
            $cicResultados = $request->input('cic_resultado', []);

            Log::info('Guardando análisis de CIC', [
                'total_rows' => count($codigosInternos),
                'codigos_internos' => $codigosInternos,
                'pesos_muestra' => $pesosMuestra,
                'vol_naoh_muestras' => $volNaohMuestras
            ]);

            for ($i = 0; $i < count($codigosInternos); $i++) {
                $analysisData = [
                    'process_id' => (string)$processId,
                    'consecutivo_no' => $request->consecutivo_no,
                    'fecha_analisis' => $request->fecha_analisis,
                    'nombre_metodo' => $request->nombre_metodo,
                    'intervalo_metodo' => $request->intervalo_metodo,
                    'equipo_utilizado' => $request->equipo_utilizado,
                    'unidades_reporte_equipo' => $request->unidades_reporte_equipo,
                    'nombre_analista' => $request->nombre_analista,
                    'resolucion_instrumental' => $request->resolucion_instrumental,
                    'peso_muestra' => $pesosMuestra[$i] ?? null,
                    'vol_naoh_muestra' => $volNaohMuestras[$i] ?? null,
                    'vol_naoh_blanco' => $volNaohBlancos[$i] ?? null,
                    'normalidad_naoh' => $normalidadesNaoh[$i] ?? null,
                    'humedad_porcentaje' => $humedadesPorcentaje[$i] ?? null,
                    'cic_resultado' => $cicResultados[$i] ?? null,
                    'observaciones' => $request->observaciones,
                ];

                Log::info("Creando análisis {$i}", $analysisData);

                $cationicAnalysis = CationicAnalysis::create($analysisData);
                
                $cationicAnalyses[] = $cationicAnalysis;
                
                Log::info("Análisis {$i} creado con ID: {$cationicAnalysis->id}");
            }



            // Guardar controles analíticos
            $controlData = [
                'process_id' => $processId,
                
                // 1. Blanco método
                'blanco_identificacion' => $request->blanco_identificacion,
                'blanco_lcm' => $request->blanco_lcm,
                'blanco_valor_leido' => $request->blanco_valor_leido,
                'blanco_aceptable' => $request->blanco_aceptable,
                'blanco_observaciones' => $request->blanco_observaciones,
                
                // 2. Control de Laboratorio (CRM/SRM)
                'error_identificacion' => $request->error_identificacion,
                'error_valor_teorico' => $request->error_valor_teorico,
                'error_valor_leido' => $request->error_valor_leido,
                'error_porcentaje' => $request->error_porcentaje,
                'error_aceptable' => $request->error_aceptable,
                'error_observaciones' => $request->error_observaciones,
                
                // 3. Recuperación de Estándar (Spike Recovery)
                'recuperacion_identificacion' => $request->recuperacion_identificacion,
                'recuperacion_valor_teorico' => $request->recuperacion_valor_teorico,
                'recuperacion_valor_leido' => $request->recuperacion_valor_leido,
                'recuperacion_porcentaje' => $request->recuperacion_porcentaje,
                'recuperacion_aceptable' => $request->recuperacion_aceptable,
                'recuperacion_observaciones' => $request->recuperacion_observaciones,
                
                // 4. Duplicados (DPR/RPD)
                'dpr_identificacion' => $request->dpr_identificacion,
                'dpr_replica1' => $request->dpr_replica1,
                'dpr_replica2' => $request->dpr_replica2,
                'dpr_porcentaje' => $request->dpr_porcentaje,
                'dpr_aceptable' => $request->dpr_aceptable,
                'dpr_observaciones' => $request->dpr_observaciones,
            ];

            Log::info('Guardando control analítico', $controlData);

            $analyticalControl = AnalyticalControl::create($controlData);

            Log::info('Control analítico creado con ID: ' . $analyticalControl->id);

            // Actualizar el estado del servicio a 'completed'
            $cationicService = Service::whereRaw('LOWER(descripcion) LIKE ?', ['%intercambio%'])
                                    ->orWhereRaw('LOWER(descripcion) LIKE ?', ['%catiónico%'])
                                    ->orWhereRaw('LOWER(descripcion) LIKE ?', ['%cationic%'])
                                    ->orWhereRaw('LOWER(descripcion) = ?', ['intercambio catiónico'])
                                    ->first();

            Log::info('Buscando servicio de intercambio catiónico', [
                'process_id' => $processId,
                'cationic_service_found' => $cationicService ? true : false,
                'service_id' => $cationicService ? $cationicService->services_id : null,
                'service_descripcion' => $cationicService ? $cationicService->descripcion : null
            ]);

            if ($cationicService) {
                // Verificar que el ServiceProcessDetail existe antes de actualizar
                $serviceProcessDetail = ServiceProcessDetail::where('process_id', $processId)
                    ->where('service_id', $cationicService->services_id)
                    ->first();

                if ($serviceProcessDetail) {
                    $updatedRows = ServiceProcessDetail::where('process_id', $processId)
                        ->where('service_id', $cationicService->services_id)
                        ->update([
                            'status' => 'completed',
                            'result' => 'Análisis de intercambio catiónico completado',
                            'observations' => 'Análisis guardado exitosamente con ' . count($cationicAnalyses) . ' muestras'
                        ]);

                    Log::info('Actualización del estado del servicio', [
                        'process_id' => $processId,
                        'service_id' => $cationicService->services_id,
                        'rows_updated' => $updatedRows,
                        'service_process_detail_id' => $serviceProcessDetail->id
                    ]);
                } else {
                    Log::warning('ServiceProcessDetail no encontrado para actualizar', [
                        'process_id' => $processId,
                        'service_id' => $cationicService->services_id
                    ]);
                }
            } else {
                Log::warning('No se encontró el servicio de intercambio catiónico para actualizar estado');
            }

            DB::commit();

            Log::info('Análisis de intercambio catiónico guardado exitosamente', [
                'user_id' => Auth::id(),
                'process_id' => $processId,
                'analyses_count' => count($cationicAnalyses),
                'analytical_control_id' => $analyticalControl->id
            ]);

            return redirect()->route('lscefa.technical.analyses.cationic.index')
                ->with('success', 'Análisis de intercambio catiónico registrado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar análisis de intercambio catiónico: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'process_id' => $processId,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Error al guardar el análisis: ' . $e->getMessage());
        }
    }

    public function process(Request $request)
    {
        $request->validate([
            'process_id' => 'required|exists:processes,process_id',
        ]);

        $processId = $request->input('process_id');

        // Buscar el proceso y su servicio de intercambio catiónico
        $process = Process::where('process_id', $processId)->firstOrFail();

        // Obtener el servicio de intercambio catiónico
        $service = Service::where('descripcion', 'like', '%intercambio%')
                        ->orWhere('descripcion', 'like', '%catiónico%')
                        ->orWhere('descripcion', 'like', '%cationic%')
                        ->firstOrFail();

        // Redirigir a la vista del formulario de análisis
        return view('lscefa::analyses.cationic.store', compact('process', 'service'));
    }

    public function edit($id)
    {
        $analysis = CationicAnalysis::findOrFail($id);
        return view('lscefa::cationic_analyses.edit', compact('analysis'));
    }

    public function update(Request $request, $id)
    {
        $analysis = CationicAnalysis::findOrFail($id);

        $request->validate([
            'fecha_analisis' => 'required|date',
            'hora_inicio' => 'required',
            'hora_fin' => 'required',
            'temperatura_laboratorio' => 'required|numeric',
            'nombre_metodo' => 'required|string|max:255',
            'intervalo_metodo' => 'required|string|max:255',
            'equipo_utilizado' => 'required|string|max:255',
            'unidades_reporte_equipo' => 'required|string|max:255',
            'resolucion_instrumental' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string',
            'fecha_fin_analisis' => 'nullable|date',
            'codigo_interno' => 'nullable|regex:/^[A-Za-z0-9\-]+$/',
            'peso_muestra' => 'nullable|numeric',
            'volumen_extractante' => 'nullable|numeric',
            'concentracion_nh4oac' => 'nullable|numeric',
            'ph_extractante' => 'nullable|numeric',
            'temperatura_extraccion' => 'nullable|numeric',
            'tiempo_agitation' => 'nullable|numeric',
            'concentracion_calcio' => 'nullable|numeric',
            'concentracion_magnesio' => 'nullable|numeric',
            'concentracion_sodio' => 'nullable|numeric',
            'concentracion_potasio' => 'nullable|numeric',
            'capacidad_intercambio_cationico' => 'required|numeric',
            'consecutivo_no' => 'required|unique:cationic_analyses,consecutivo_no,' . $analysis->id,
        ]);

        $analysis->update($request->all());

        return redirect()->route('cationic_analysis.index')->with('success', 'Análisis actualizado correctamente.');
    }

    public function show($id)
    {
        $analysis = CationicAnalysis::with('process', 'analyticalControl')->findOrFail($id);
        return view('lscefa::analyses.cationic.show', compact('analysis'));
    }

    public function destroy($id)
    {
        $analysis = CationicAnalysis::findOrFail($id);
        $analysis->delete();

        return redirect()->route('cationic_analysis.index')->with('success', 'Análisis eliminado correctamente.');
    }

    public function report($id)
    {
        $analysis = CationicAnalysis::with('process', 'analyticalControl')->findOrFail($id);
        
        // Aquí iría la lógica para generar el reporte PDF
        // Por ahora retornamos una vista
        return view('lscefa::analyses.cationic.report', compact('analysis'));
    }

    public function batchProcess(Request $request)
    {
        try {
            Log::info('Usuario accede a procesamiento por lotes de análisis de intercambio catiónico', [
                'user_id' => Auth::id(),
                'role' => Auth::user()->role ?? 'N/A'
            ]);

            $processIds = $request->input('process_ids', []);
            
            if (empty($processIds)) {
                return redirect()->route('lscefa.technical.analyses.cationic.index')
                    ->with('error', 'No se seleccionaron procesos para procesar.');
            }

            // Obtener los procesos pendientes
            $pendingProcesses = Process::whereIn('process_id', $processIds)
                ->whereHas('serviceProcessDetails', function ($query) {
                    $query->where('status', 'pending');
                })
                ->with(['serviceProcessDetails' => function ($query) {
                    $query->where('status', 'pending');
                }])
                ->get();

            if ($pendingProcesses->isEmpty()) {
                return redirect()->route('lscefa.technical.analyses.cationic.index')
                    ->with('error', 'No se encontraron procesos pendientes para procesar.');
            }

            // Obtener el servicio de intercambio catiónico
            $cationicService = Service::whereRaw('LOWER(descripcion) LIKE ?', ['%intercambio%'])
                                    ->orWhereRaw('LOWER(descripcion) LIKE ?', ['%catiónico%'])
                                    ->orWhereRaw('LOWER(descripcion) LIKE ?', ['%cationic%'])
                                    ->orWhereRaw('LOWER(descripcion) = ?', ['intercambio catiónico'])
                                    ->first();

            if (!$cationicService) {
                return redirect()->route('lscefa.technical.analyses.cationic.index')
                    ->with('error', 'No se encontró el servicio de intercambio catiónico.');
            }

            Log::info('Procesos seleccionados para procesamiento por lotes', [
                'process_ids' => $processIds,
                'pending_processes_count' => $pendingProcesses->count(),
                'service_id' => $cationicService->services_id
            ]);

            return view('lscefa::analyses.cationic.batch_process', compact('pendingProcesses', 'cationicService'));

        } catch (\Exception $e) {
            Log::error('Error al cargar el procesamiento por lotes: ' . $e->getMessage());
            return back()->with('error', 'No se pudo cargar el procesamiento por lotes.');
        }
    }

    public function batchStore(Request $request)
    {
        try {
            Log::info('Usuario guarda análisis de intercambio catiónico por lotes', [
                'user_id' => Auth::id(),
                'role' => Auth::user()->role ?? 'N/A'
            ]);

            // Procesar campos numéricos para evitar comas
            $numericFields = [
                'peso_muestra', 'vol_naoh_muestra', 'vol_naoh_blanco', 'normalidad_naoh', 'humedad_porcentaje', 'cic_resultado',
                'blanco_lcm', 'blanco_valor_leido', 'error_valor_teorico', 'error_valor_leido', 'error_porcentaje',
                'recuperacion_valor_teorico', 'recuperacion_valor_leido', 'recuperacion_porcentaje',
                'dpr_replica1', 'dpr_replica2', 'dpr_porcentaje'
            ];

            foreach ($numericFields as $field) {
                if ($request->has($field)) {
                    if (is_array($request->input($field))) {
                        $processedValues = [];
                        foreach ($request->input($field) as $value) {
                            $processedValues[] = str_replace(',', '.', $value);
                        }
                        $request->merge([$field => $processedValues]);
                    } else {
                        $value = str_replace(',', '.', $request->input($field));
                        $request->merge([$field => $value]);
                    }
                }
            }

            $request->validate([
                'proceso_numero' => 'required|array',
                'proceso_numero.*' => 'required|exists:processes,process_id',
                'consecutivo_no' => 'required|string',
                'fecha_analisis' => 'required|date',
                'unidades_reporte_equipo' => 'required|string',
                'nombre_metodo' => 'required|string',
                'equipo_utilizado' => 'required|string',
                'intervalo_metodo' => 'required|string',
                'nombre_analista' => 'required|string',
                'resolucion_instrumental' => 'nullable|string',
                'observaciones' => 'nullable|string',
                
                // Campos de resultados (arrays)
                'codigo_interno' => 'required|array',
                'codigo_interno.*' => 'required|string',
                'peso_muestra' => 'required|array',
                'peso_muestra.*' => 'required|numeric|min:0',
                'vol_naoh_muestra' => 'required|array',
                'vol_naoh_muestra.*' => 'required|numeric|min:0',
                'vol_naoh_blanco' => 'required|array',
                'vol_naoh_blanco.*' => 'required|numeric|min:0',
                'normalidad_naoh' => 'required|array',
                'normalidad_naoh.*' => 'required|numeric|min:0',
                'humedad_porcentaje' => 'required|array',
                'humedad_porcentaje.*' => 'required|numeric|min:0',
                'cic_resultado' => 'required|array',
                'cic_resultado.*' => 'required|numeric',
                
                // Campos de controles de calidad
                'blanco_identificacion' => 'nullable|string',
                'blanco_lcm' => 'nullable|numeric',
                'blanco_valor_leido' => 'nullable|numeric',
                'blanco_aceptable' => 'nullable|string',
                'blanco_observaciones' => 'nullable|string',
                
                'error_identificacion' => 'nullable|string',
                'error_valor_teorico' => 'nullable|numeric',
                'error_valor_leido' => 'nullable|numeric',
                'error_porcentaje' => 'nullable|numeric',
                'error_aceptable' => 'nullable|string',
                'error_observaciones' => 'nullable|string',
                
                'recuperacion_identificacion' => 'nullable|string',
                'recuperacion_valor_teorico' => 'nullable|numeric',
                'recuperacion_valor_leido' => 'nullable|numeric',
                'recuperacion_porcentaje' => 'nullable|numeric',
                'recuperacion_aceptable' => 'nullable|string',
                'recuperacion_observaciones' => 'nullable|string',
                
                'dpr_identificacion' => 'nullable|string',
                'dpr_replica1' => 'nullable|numeric',
                'dpr_replica2' => 'nullable|numeric',
                'dpr_porcentaje' => 'nullable|numeric',
                'dpr_aceptable' => 'nullable|string',
                'dpr_observaciones' => 'nullable|string',
            ]);

            DB::beginTransaction();

            $procesosNumeros = $request->input('proceso_numero');
            $cationicService = Service::whereRaw('LOWER(descripcion) LIKE ?', ['%intercambio%'])
                                    ->orWhereRaw('LOWER(descripcion) LIKE ?', ['%catiónico%'])
                                    ->orWhereRaw('LOWER(descripcion) LIKE ?', ['%cationic%'])
                                    ->orWhereRaw('LOWER(descripcion) = ?', ['intercambio catiónico'])
                                    ->first();

            Log::info('Buscando servicio de intercambio catiónico para batch', [
                'cationic_service_found' => $cationicService ? true : false,
                'service_id' => $cationicService ? $cationicService->services_id : null,
                'service_descripcion' => $cationicService ? $cationicService->descripcion : null
            ]);

            $savedAnalyses = [];
            $savedControls = [];

            // Agrupar análisis por proceso
            $analysesByProcess = [];
            $codigosInternos = $request->input('codigo_interno', []);
            $pesosMuestra = $request->input('peso_muestra', []);
            $volNaohMuestras = $request->input('vol_naoh_muestra', []);
            $volNaohBlancos = $request->input('vol_naoh_blanco', []);
            $normalidadesNaoh = $request->input('normalidad_naoh', []);
            $humedadesPorcentaje = $request->input('humedad_porcentaje', []);
            $cicResultados = $request->input('cic_resultado', []);

            // Agrupar los datos por proceso
            for ($i = 0; $i < count($codigosInternos); $i++) {
                $processId = $procesosNumeros[$i] ?? null;
                if ($processId) {
                    if (!isset($analysesByProcess[$processId])) {
                        $analysesByProcess[$processId] = [];
                    }
                    $analysesByProcess[$processId][] = [
                        'codigo_interno' => $codigosInternos[$i] ?? null,
                        'peso_muestra' => $pesosMuestra[$i] ?? null,
                        'vol_naoh_muestra' => $volNaohMuestras[$i] ?? null,
                        'vol_naoh_blanco' => $volNaohBlancos[$i] ?? null,
                        'normalidad_naoh' => $normalidadesNaoh[$i] ?? null,
                        'humedad_porcentaje' => $humedadesPorcentaje[$i] ?? null,
                        'cic_resultado' => $cicResultados[$i] ?? null,
                    ];
                }
            }

            foreach ($analysesByProcess as $processId => $analyses) {
                Log::info("Procesando proceso: {$processId}");

                // Verificar si ya existe un control analítico para este proceso
                $existingControl = AnalyticalControl::where('process_id', $processId)->first();
                if ($existingControl) {
                    Log::warning('Ya existe un control analítico para este proceso', [
                        'process_id' => $processId,
                        'control_id' => $existingControl->id
                    ]);
                    // No saltar el proceso, solo actualizar el estado del servicio
                    $processAnalyses = [];
                } else {
                    // Guardar múltiples análisis de CIC para este proceso
                    $processAnalyses = [];
                    foreach ($analyses as $analysis) {
                        $analysisData = [
                            'process_id' => (string)$processId,
                            'consecutivo_no' => $request->consecutivo_no,
                            'fecha_analisis' => $request->fecha_analisis,
                            'nombre_metodo' => $request->nombre_metodo,
                            'intervalo_metodo' => $request->intervalo_metodo,
                            'equipo_utilizado' => $request->equipo_utilizado,
                            'unidades_reporte_equipo' => $request->unidades_reporte_equipo,
                            'nombre_analista' => $request->nombre_analista,
                            'resolucion_instrumental' => $request->resolucion_instrumental,
                            'peso_muestra' => $analysis['peso_muestra'] ?? null,
                            'vol_naoh_muestra' => $analysis['vol_naoh_muestra'] ?? null,
                            'vol_naoh_blanco' => $analysis['vol_naoh_blanco'] ?? null,
                            'normalidad_naoh' => $analysis['normalidad_naoh'] ?? null,
                            'humedad_porcentaje' => $analysis['humedad_porcentaje'] ?? null,
                            'cic_resultado' => $analysis['cic_resultado'] ?? null,
                            'observaciones' => $request->observaciones,
                        ];

                        $cationicAnalysis = CationicAnalysis::create($analysisData);
                        $processAnalyses[] = $cationicAnalysis;
                        $savedAnalyses[] = $cationicAnalysis;
                    }

                    // Guardar control analítico para este proceso
                    $controlData = [
                        'process_id' => $processId,
                        
                        // 1. Blanco método
                        'blanco_identificacion' => $request->blanco_identificacion,
                        'blanco_lcm' => $request->blanco_lcm,
                        'blanco_valor_leido' => $request->blanco_valor_leido,
                        'blanco_aceptable' => $request->blanco_aceptable,
                        'blanco_observaciones' => $request->blanco_observaciones,
                        
                        // 2. Control de Laboratorio (CRM/SRM)
                        'error_identificacion' => $request->error_identificacion,
                        'error_valor_teorico' => $request->error_valor_teorico,
                        'error_valor_leido' => $request->error_valor_leido,
                        'error_porcentaje' => $request->error_porcentaje,
                        'error_aceptable' => $request->error_aceptable,
                        'error_observaciones' => $request->error_observaciones,
                        
                        // 3. Recuperación de Estándar (Spike Recovery)
                        'recuperacion_identificacion' => $request->recuperacion_identificacion,
                        'recuperacion_valor_teorico' => $request->recuperacion_valor_teorico,
                        'recuperacion_valor_leido' => $request->recuperacion_valor_leido,
                        'recuperacion_porcentaje' => $request->recuperacion_porcentaje,
                        'recuperacion_aceptable' => $request->recuperacion_aceptable,
                        'recuperacion_observaciones' => $request->recuperacion_observaciones,
                        
                        // 4. Duplicados (DPR/RPD)
                        'dpr_identificacion' => $request->dpr_identificacion,
                        'dpr_replica1' => $request->dpr_replica1,
                        'dpr_replica2' => $request->dpr_replica2,
                        'dpr_porcentaje' => $request->dpr_porcentaje,
                        'dpr_aceptable' => $request->dpr_aceptable,
                        'dpr_observaciones' => $request->dpr_observaciones,
                    ];

                    $analyticalControl = AnalyticalControl::create($controlData);
                    $savedControls[] = $analyticalControl;
                }

                // Actualizar el estado del servicio a 'completed'
                Log::info("Intentando actualizar estado para proceso: {$processId}");
                
                if ($cationicService) {
                    Log::info("Servicio catiónico encontrado: {$cationicService->services_id}");
                    
                    // Verificar que el ServiceProcessDetail existe antes de actualizar
                    $serviceProcessDetail = ServiceProcessDetail::where('process_id', $processId)
                        ->where('service_id', $cationicService->services_id)
                        ->first();

                    if ($serviceProcessDetail) {
                        Log::info("ServiceProcessDetail encontrado: ID {$serviceProcessDetail->id}, Status actual: {$serviceProcessDetail->status}");
                        
                        $updatedRows = ServiceProcessDetail::where('process_id', $processId)
                            ->where('service_id', $cationicService->services_id)
                            ->update([
                                'status' => 'completed',
                                'result' => 'Análisis de intercambio catiónico completado',
                                'observations' => 'Análisis guardado exitosamente con ' . count($processAnalyses) . ' muestras'
                            ]);

                        Log::info('Actualización del estado del servicio', [
                            'process_id' => $processId,
                            'service_id' => $cationicService->services_id,
                            'rows_updated' => $updatedRows,
                            'service_process_detail_id' => $serviceProcessDetail->id
                        ]);
                        
                        // Verificar si realmente se actualizó
                        $updatedSpd = ServiceProcessDetail::find($serviceProcessDetail->id);
                        Log::info("Status después de actualización: {$updatedSpd->status}");
                    } else {
                        Log::warning('ServiceProcessDetail no encontrado para actualizar', [
                            'process_id' => $processId,
                            'service_id' => $cationicService->services_id
                        ]);
                    }
                } else {
                    Log::warning('No se encontró el servicio de intercambio catiónico para actualizar estado');
                }

                Log::info("Proceso {$processId} procesado exitosamente", [
                    'analyses_count' => count($processAnalyses),
                    'control_id' => isset($analyticalControl) ? $analyticalControl->id : 'existing_control'
                ]);
            }

            DB::commit();

            Log::info('Análisis de intercambio catiónico guardados exitosamente por lotes', [
                'user_id' => Auth::id(),
                'process_ids' => array_keys($analysesByProcess),
                'total_analyses_saved' => count($savedAnalyses),
                'total_controls_saved' => count($savedControls)
            ]);

            return redirect()->route('lscefa.technical.analyses.cationic.index')
                ->with('success', 'Análisis de intercambio catiónico procesados exitosamente por lotes.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar análisis de intercambio catiónico por lotes: ' . $e->getMessage());
            return back()->with('error', 'Error al guardar los análisis: ' . $e->getMessage());
        }
    }
} 