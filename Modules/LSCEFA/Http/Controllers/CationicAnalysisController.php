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

            // 🔍 Usar el ID directo del servicio de INTERCAMBIO CATIÓNICO (ID: 11)
            $cationicServiceId = 11;
            $cationicService = Service::find($cationicServiceId);

            if (!$cationicService) {
                Log::warning('No se encontró el servicio de intercambio catiónico con ID: ' . $cationicServiceId);
                $processes = collect();
                $cationicAnalyses = collect();
            } else {
                Log::info('Servicio encontrado', [
                    'service_id' => $cationicService->services_id,
                    'descripcion' => $cationicService->descripcion
                ]);
                
                // 🔍 Procesos que tienen el servicio de intercambio catiónico PENDIENTE
                $processes = Process::whereHas('serviceProcessDetails', function ($query) use ($cationicServiceId) {
                        $query->where('service_id', $cationicServiceId)
                              ->where('status', 'pending');
                    })
                    ->with(['serviceProcessDetails' => function($query) use ($cationicServiceId) {
                        $query->where('service_id', $cationicServiceId);
                    }])
                    ->get();

                Log::info('Procesos encontrados', [
                    'processes_count' => $processes->count(),
                    'process_ids' => $processes->pluck('process_id')->toArray()
                ]);

                // 🔍 Análisis de intercambio catiónico relacionados a procesos con servicio pendiente
                $cationicAnalyses = CationicAnalysis::with(['process.serviceProcessDetails' => function($query) use ($cationicServiceId) {
                        $query->where('service_id', $cationicServiceId);
                    }])
                    ->whereHas('process.serviceProcessDetails', function ($query) use ($cationicServiceId) {
                        $query->where('service_id', $cationicServiceId)
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

        // Validar que se recibieron los IDs necesarios
        if (!$processId) {
            Log::error('No se recibió process_id en la solicitud', [
                'request_data' => $request->all()
            ]);
            return back()->with('error', 'No se recibió el ID del proceso.')->withInput();
        }

        if (!$serviceId) {
            Log::error('No se recibió service_id en la solicitud', [
                'request_data' => $request->all()
            ]);
            return back()->with('error', 'No se recibió el ID del servicio.')->withInput();
        }

        Log::info('Datos recibidos en storeCationicAnalysis', [
            'process_id' => $processId,
            'service_id' => $serviceId,
            'request_data' => $request->all()
        ]);

        // Validación para los campos del formulario
        try {
            $request->validate([
                'process_id' => 'required|string',
                'service_id' => 'required|string',
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
                'vol_naoh_muestras' => $volNaohMuestras,
                'vol_naoh_blancos' => $volNaohBlancos,
                'normalidades_naoh' => $normalidadesNaoh,
                'humedades_porcentaje' => $humedadesPorcentaje,
                'cic_resultados' => $cicResultados
            ]);

            if (empty($codigosInternos)) {
                Log::error('No se recibieron códigos internos para procesar');
                return back()->with('error', 'No se recibieron datos para procesar.')->withInput();
            }

            for ($i = 0; $i < count($codigosInternos); $i++) {
                $analysisData = [
                    'process_id' => (string)$processId,
                    'service_id' => $request->service_id,
                    'consecutivo_no' => $request->consecutivo_no,
                    'fecha_analisis' => $request->fecha_analisis,
                    'nombre_metodo' => $request->nombre_metodo,
                    'intervalo_metodo' => $request->intervalo_metodo,
                    'equipo_utilizado' => $request->equipo_utilizado,
                    'unidades_reporte_equipo' => $request->unidades_reporte_equipo,
                    'nombre_analista' => $request->nombre_analista,
                    'resolucion_instrumental' => $request->resolucion_instrumental,
                    'codigo_interno' => $codigosInternos[$i] ?? null,
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
                'analysis_type' => 'cationic',
                
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
                'process_ids' => 'required|array',
                'process_ids.*' => 'required|string',
                'consecutivo_no' => 'required|string',
                'fecha_analisis' => 'required|date',
                'unidades_reporte_equipo' => 'required|string',
                'nombre_metodo' => 'required|string',
                'equipo_utilizado' => 'required|string',
                'intervalo_metodo' => 'required|string',
                'nombre_analista' => 'required|string',
                'resolucion_instrumental' => 'nullable|string',
                'observaciones' => 'nullable|string',
                
                // Campos de resultados organizados por process_id
                'items_ensayo' => 'nullable|array',
                'items_ensayo.*.codigo_interno' => 'nullable|string',
                'items_ensayo.*.peso_muestra' => 'nullable|numeric|min:0',
                'items_ensayo.*.vol_naoh_muestra' => 'nullable|numeric|min:0',
                'items_ensayo.*.vol_naoh_blanco' => 'nullable|numeric|min:0',
                'items_ensayo.*.normalidad_naoh' => 'nullable|numeric|min:0',
                'items_ensayo.*.humedad_porcentaje' => 'nullable|numeric|min:0',
                'items_ensayo.*.cic_resultado' => 'nullable|numeric',
                'items_ensayo.*.observaciones' => 'nullable|string',
                
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
            
            Log::info('Validación exitosa en batchStore', [
                'process_ids_validated' => $request->input('process_ids'),
                'validation_passed' => true
            ]);

            DB::beginTransaction();

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

            // Verificar si se está actualizando un análisis rechazado
            $rejectedAnalysisId = $request->input('rejected_analysis_id');
            $isUpdatingRejected = false;
            $rejectedAnalysis = null;

            if ($rejectedAnalysisId) {
                $rejectedAnalysis = CationicAnalysis::find($rejectedAnalysisId);
                if ($rejectedAnalysis && $rejectedAnalysis->review_status === 'rejected') {
                    $isUpdatingRejected = true;
                    Log::info('Actualizando análisis rechazado', [
                        'analysis_id' => $rejectedAnalysisId,
                        'process_id' => $rejectedAnalysis->process_id
                    ]);
                }
            }

            foreach ($request->process_ids as $processId) {
                Log::info("Procesando proceso: {$processId}");

                // Verificar si ya existe un control analítico para este proceso
                $existingControl = AnalyticalControl::where('process_id', $processId)->first();
                if ($existingControl && !$isUpdatingRejected) {
                    Log::warning('Ya existe un control analítico para este proceso', [
                        'process_id' => $processId,
                        'control_id' => $existingControl->id
                    ]);
                    continue;
                }

                // Guardar análisis de CIC para este proceso
                if (isset($request->items_ensayo[$processId])) {
                    foreach ($request->items_ensayo[$processId] as $item) {
                        $analysisData = [
                            'process_id' => (string)$processId,
                            'service_id' => $cationicService->services_id,
                            'consecutivo_no' => $request->consecutivo_no,
                            'fecha_analisis' => $request->fecha_analisis,
                            'nombre_metodo' => $request->nombre_metodo,
                            'intervalo_metodo' => $request->intervalo_metodo,
                            'equipo_utilizado' => $request->equipo_utilizado,
                            'unidades_reporte_equipo' => $request->unidades_reporte_equipo,
                            'nombre_analista' => $request->nombre_analista,
                            'resolucion_instrumental' => $request->resolucion_instrumental,
                            'codigo_interno' => $item['codigo_interno'] ?? null,
                            'peso_muestra' => $item['peso_muestra'] ?? null,
                            'vol_naoh_muestra' => $item['vol_naoh_muestra'] ?? null,
                            'vol_naoh_blanco' => $item['vol_naoh_blanco'] ?? null,
                            'normalidad_naoh' => $item['normalidad_naoh'] ?? null,
                            'humedad_porcentaje' => $item['humedad_porcentaje'] ?? null,
                            'cic_resultado' => $item['cic_resultado'] ?? null,
                            'observaciones' => $item['observaciones'] ?? $request->observaciones,
                        ];

                        Log::info("Datos del análisis a guardar:", $analysisData);

                        if ($isUpdatingRejected && $rejectedAnalysis && $rejectedAnalysis->process_id === $processId) {
                            // Actualizar el análisis rechazado
                            $rejectedAnalysis->update($analysisData);
                            $rejectedAnalysis->update([
                                'review_status' => 'pending',
                                'review_observations' => null,
                                'reviewed_by' => null,
                                'review_date' => null,
                            ]);
                            $savedAnalyses[] = $rejectedAnalysis;
                            
                            Log::info("Análisis rechazado actualizado para proceso {$processId}", [
                                'analysis_id' => $rejectedAnalysis->id,
                                'codigo_interno' => $item['codigo_interno'] ?? '',
                                'peso_muestra' => $item['peso_muestra'] ?? '',
                                'vol_naoh_muestra' => $item['vol_naoh_muestra'] ?? '',
                                'cic_resultado' => $item['cic_resultado'] ?? ''
                            ]);
                        } else {
                            // Crear nuevo análisis
                            $cationicAnalysis = CationicAnalysis::create($analysisData);
                            $savedAnalyses[] = $cationicAnalysis;
                            
                            Log::info("Análisis creado para proceso {$processId}", [
                                'analysis_id' => $cationicAnalysis->id,
                                'codigo_interno' => $item['codigo_interno'] ?? '',
                                'peso_muestra' => $item['peso_muestra'] ?? '',
                                'vol_naoh_muestra' => $item['vol_naoh_muestra'] ?? '',
                                'cic_resultado' => $item['cic_resultado'] ?? ''
                            ]);
                        }
                    }
                } else {
                    Log::warning("No se encontraron items_ensayo para el proceso: {$processId}");
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

                if ($isUpdatingRejected && $existingControl) {
                    // Actualizar el control analítico existente
                    $existingControl->update($controlData);
                    $savedControls[] = $existingControl;
                    
                    Log::info('Control analítico actualizado para proceso', [
                        'control_id' => $existingControl->id,
                        'process_id' => $processId
                    ]);
                } else {
                    // Crear nuevo control analítico
                    $analyticalControl = AnalyticalControl::create($controlData);
                    $savedControls[] = $analyticalControl;

                    Log::info('Control analítico creado para proceso', [
                        'control_id' => $analyticalControl->id,
                        'process_id' => $processId
                    ]);
                }

                // Actualizar el estado del servicio
                $serviceProcessDetail = ServiceProcessDetail::where('process_id', $processId)
                    ->where('service_id', $cationicService->services_id)
                    ->first();

                if ($serviceProcessDetail) {
                    if ($isUpdatingRejected) {
                        // Para análisis rechazados, poner en pending para que vuelva a revisión
                        $serviceProcessDetail->status = 'pending';
                        Log::info('Estado del detalle del proceso actualizado a pending (análisis rechazado)', [
                            'service_process_detail_id' => $serviceProcessDetail->id,
                            'process_id' => $processId
                        ]);
                    } else {
                        // Para análisis nuevos, marcar como completado
                        $serviceProcessDetail->status = 'completed';
                        Log::info('Estado del detalle del proceso actualizado a completado', [
                            'service_process_detail_id' => $serviceProcessDetail->id,
                            'process_id' => $processId
                        ]);
                    }
                    $serviceProcessDetail->save();
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
            }

            DB::commit();

            Log::info('Procesamiento por lotes completado', [
                'total_processes' => count($request->process_ids),
                'saved_analyses' => count($savedAnalyses),
                'saved_controls' => count($savedControls)
            ]);

            if ($isUpdatingRejected) {
                return redirect()->route('lscefa.technical.analyses.cationic.index')
                    ->with('success', 'Análisis de intercambio catiónico rechazado corregido y enviado para revisión.');
            } else {
                return redirect()->route('lscefa.technical.analyses.cationic.index')
                    ->with('success', 'Análisis de intercambio catiónico procesados exitosamente por lotes.');
            }

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al procesar análisis de intercambio catiónico por lotes', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withInput()->with('error', 'Error al procesar los análisis por lotes: ' . $e->getMessage());
        }
    }

    /**
     * Editar un análisis de intercambio catiónico rechazado
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function editRejected($id)
    {
        // Cargar el análisis con todas las relaciones necesarias
        $analysis = CationicAnalysis::with([
            'analyticalControl',
            'process',
            'process.quote'
        ])->findOrFail($id);
        
        // Verificar que el análisis esté rechazado
        if ($analysis->review_status !== 'rejected') {
            return redirect()->route('lscefa.technical.analyses.cationic.index')
                ->with('error', 'Este análisis no está rechazado o ya fue corregido.');
        }

        // Obtener el proceso y servicio asociados
        $process = Process::find($analysis->process_id);
        
        // Intentar obtener el servicio del análisis, si no existe, buscarlo por el proceso
        $service = null;
        if ($analysis->service_id) {
            $service = Service::find($analysis->service_id);
        }
        
        // Si no se encontró el servicio, intentar buscarlo por el proceso
        if (!$service) {
            $serviceProcessDetail = ServiceProcessDetail::where('process_id', $analysis->process_id)
                ->first();
            
            if ($serviceProcessDetail) {
                $service = Service::find($serviceProcessDetail->service_id);
            }
        }
        
        // Si aún no se encuentra, buscar el servicio de intercambio catiónico por defecto
        if (!$service) {
            $service = Service::whereRaw('LOWER(descripcion) LIKE ?', ['%intercambio%'])
                ->orWhereRaw('LOWER(descripcion) LIKE ?', ['%catiónico%'])
                ->orWhereRaw('LOWER(descripcion) LIKE ?', ['%cationic%'])
                ->orWhereRaw('LOWER(descripcion) = ?', ['intercambio catiónico'])
                ->first();
        }
        
        // Si no se puede encontrar el servicio, redirigir con error
        if (!$service) {
            Log::error('No se pudo encontrar el servicio para el análisis de intercambio catiónico', [
                'analysis_id' => $analysis->id,
                'process_id' => $analysis->process_id,
                'service_id' => $analysis->service_id
            ]);
            return redirect()->route('lscefa.technical.analyses.cationic.index')
                ->with('error', 'No se pudo encontrar el servicio asociado al análisis.');
        }

        // Crear un array de procesos con el proceso del análisis rechazado
        $processes = collect([$process]);

        // Cargar los controles analíticos desde la tabla analytical_controls
        $analyticalControls = AnalyticalControl::where('process_id', $analysis->process_id)
            ->where('analysis_type', 'cationic')
            ->get();

        // Debug: Log detallado de la estructura de analytical_controls
        Log::info('Estructura completa de analytical_controls para cationic:', [
            'total_controls' => $analyticalControls->count(),
            'controls_data' => $analyticalControls->toArray()
        ]);

        // Extraer datos de controles analíticos
        $controlesAnaliticos = [];
        
        foreach ($analyticalControls as $control) {
            Log::info('Procesando control individual de cationic:', [
                'control_id' => $control->id,
                'process_id' => $control->process_id
            ]);
            
            // Agregar datos de blanco
            if ($control->blanco_identificacion) {
                $controlesAnaliticos[] = [
                    'tipo' => 'blanco',
                    'datos_completos' => [
                        'identificacion' => $control->blanco_identificacion,
                        'lcm' => $control->blanco_lcm,
                        'valor_leido' => $control->blanco_valor_leido,
                        'aceptable' => $control->blanco_aceptable,
                        'observaciones' => $control->blanco_observaciones
                    ]
                ];
            }
            
            // Agregar datos de error
            if ($control->error_identificacion) {
                $controlesAnaliticos[] = [
                    'tipo' => 'error',
                    'datos_completos' => [
                        'identificacion' => $control->error_identificacion,
                        'valor_teorico' => $control->error_valor_teorico,
                        'valor_leido' => $control->error_valor_leido,
                        'porcentaje' => $control->error_porcentaje,
                        'aceptable' => $control->error_aceptable,
                        'observaciones' => $control->error_observaciones
                    ]
                ];
            }
            
            // Agregar datos de recuperación
            if ($control->recuperacion_identificacion) {
                $controlesAnaliticos[] = [
                    'tipo' => 'recuperacion',
                    'datos_completos' => [
                        'identificacion' => $control->recuperacion_identificacion,
                        'valor_teorico' => $control->recuperacion_valor_teorico,
                        'valor_leido' => $control->recuperacion_valor_leido,
                        'porcentaje' => $control->recuperacion_porcentaje,
                        'aceptable' => $control->recuperacion_aceptable,
                        'observaciones' => $control->recuperacion_observaciones
                    ]
                ];
            }
            
            // Agregar datos de duplicados
            if ($control->dpr_identificacion) {
                $controlesAnaliticos[] = [
                    'tipo' => 'duplicados',
                    'datos_completos' => [
                        'identificacion' => $control->dpr_identificacion,
                        'replica1' => $control->dpr_replica1,
                        'replica2' => $control->dpr_replica2,
                        'porcentaje' => $control->dpr_porcentaje,
                        'aceptable' => $control->dpr_aceptable,
                        'observaciones' => $control->dpr_observaciones
                    ]
                ];
            }
        }

        // Debug: Log todos los datos del análisis para verificar qué se está cargando
        Log::info('Datos del análisis de intercambio catiónico rechazado cargado:', [
            'analysis_id' => $analysis->id,
            'process_id' => $analysis->process_id,
            'review_status' => $analysis->review_status,
            'review_observations' => $analysis->review_observations,
            'controles_analiticos_count' => count($controlesAnaliticos)
        ]);

        // Crear un array de procesos con el proceso del análisis rechazado
        // La vista espera $pendingProcesses, no $processes
        $pendingProcesses = collect([$process]);

        // Cargar los controles analíticos desde la tabla analytical_controls
        $analyticalControls = AnalyticalControl::where('process_id', $analysis->process_id)
            ->where('analysis_type', 'cationic')
            ->get();

        // Extraer datos de controles analíticos
        $controlesAnaliticos = [];
        
        foreach ($analyticalControls as $control) {
            // Agregar datos de blanco
            if ($control->blanco_identificacion) {
                $controlesAnaliticos[] = [
                    'tipo' => 'blanco',
                    'datos_completos' => [
                        'identificacion' => $control->blanco_identificacion,
                        'lcm' => $control->blanco_lcm,
                        'valor_leido' => $control->blanco_valor_leido,
                        'aceptable' => $control->blanco_aceptable,
                        'observaciones' => $control->blanco_observaciones
                    ]
                ];
            }
            
            // Agregar datos de error
            if ($control->error_identificacion) {
                $controlesAnaliticos[] = [
                    'tipo' => 'error',
                    'datos_completos' => [
                        'identificacion' => $control->error_identificacion,
                        'valor_teorico' => $control->error_valor_teorico,
                        'valor_leido' => $control->error_valor_leido,
                        'porcentaje' => $control->error_porcentaje,
                        'aceptable' => $control->error_aceptable,
                        'observaciones' => $control->error_observaciones
                    ]
                ];
            }
            
            // Agregar datos de recuperación
            if ($control->recuperacion_identificacion) {
                $controlesAnaliticos[] = [
                    'tipo' => 'recuperacion',
                    'datos_completos' => [
                        'identificacion' => $control->recuperacion_identificacion,
                        'valor_teorico' => $control->recuperacion_valor_teorico,
                        'valor_leido' => $control->recuperacion_valor_leido,
                        'porcentaje' => $control->recuperacion_porcentaje,
                        'aceptable' => $control->recuperacion_aceptable,
                        'observaciones' => $control->recuperacion_observaciones
                    ]
                ];
            }
            
            // Agregar datos de duplicados
            if ($control->dpr_identificacion) {
                $controlesAnaliticos[] = [
                    'tipo' => 'duplicados',
                    'datos_completos' => [
                        'identificacion' => $control->dpr_identificacion,
                        'replica1' => $control->dpr_replica1,
                        'replica2' => $control->dpr_replica2,
                        'porcentaje' => $control->dpr_porcentaje,
                        'aceptable' => $control->dpr_aceptable,
                        'observaciones' => $control->dpr_observaciones
                    ]
                ];
            }
        }

        // Pasar el análisis rechazado y controles analíticos para que se pueda editar
        return view('lscefa::analyses.cationic.batch_process', compact('processes', 'analysis', 'controlesAnaliticos'));
    }

    /**
     * Actualizar un análisis de intercambio catiónico rechazado
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateRejected(Request $request, $id)
    {
        $analysis = CationicAnalysis::findOrFail($id);
        
        // Verificar que el análisis esté rechazado
        if ($analysis->review_status !== 'rejected') {
            return redirect()->route('lscefa.technical.analyses.cationic.index')
                ->with('error', 'Este análisis no está rechazado o ya fue corregido.');
        }

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
            'consecutivo_no' => 'required|string',
            'fecha_analisis' => 'required|date',
            'unidades_reporte_equipo' => 'required|string',
            'nombre_metodo' => 'required|string',
            'equipo_utilizado' => 'required|string',
            'intervalo_metodo' => 'required|string',
            'nombre_analista' => 'required|string',
            'resolucion_instrumental' => 'nullable|string',
            'observaciones' => 'nullable|string',
            
            // Campos de resultados
            'codigo_interno' => 'required|string',
            'peso_muestra' => 'required|numeric|min:0',
            'vol_naoh_muestra' => 'required|numeric|min:0',
            'vol_naoh_blanco' => 'required|numeric|min:0',
            'normalidad_naoh' => 'required|numeric|min:0',
            'humedad_porcentaje' => 'required|numeric|min:0',
            'cic_resultado' => 'required|numeric',
            
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

        try {
            // Obtener el servicio si no está definido en el análisis
            $serviceId = $analysis->service_id;
            if (!$serviceId) {
                $serviceProcessDetail = ServiceProcessDetail::where('process_id', $analysis->process_id)
                    ->first();
                
                if ($serviceProcessDetail) {
                    $serviceId = $serviceProcessDetail->service_id;
                } else {
                    // Buscar el servicio de intercambio catiónico por defecto
                    $cationicService = Service::whereRaw('LOWER(descripcion) LIKE ?', ['%intercambio%'])
                        ->orWhereRaw('LOWER(descripcion) LIKE ?', ['%catiónico%'])
                        ->orWhereRaw('LOWER(descripcion) LIKE ?', ['%cationic%'])
                        ->orWhereRaw('LOWER(descripcion) = ?', ['intercambio catiónico'])
                        ->first();
                    
                    if ($cationicService) {
                        $serviceId = $cationicService->services_id;
                    }
                }
            }
            
            // Actualizar el análisis
            $analysis->update([
                'service_id' => $serviceId,
                'consecutivo_no' => $request->consecutivo_no,
                'fecha_analisis' => $request->fecha_analisis,
                'nombre_metodo' => $request->nombre_metodo,
                'intervalo_metodo' => $request->intervalo_metodo,
                'equipo_utilizado' => $request->equipo_utilizado,
                'unidades_reporte_equipo' => $request->unidades_reporte_equipo,
                'nombre_analista' => $request->nombre_analista,
                'resolucion_instrumental' => $request->resolucion_instrumental,
                'codigo_interno' => $request->codigo_interno,
                'peso_muestra' => $request->peso_muestra,
                'vol_naoh_muestra' => $request->vol_naoh_muestra,
                'vol_naoh_blanco' => $request->vol_naoh_blanco,
                'normalidad_naoh' => $request->normalidad_naoh,
                'humedad_porcentaje' => $request->humedad_porcentaje,
                'cic_resultado' => $request->cic_resultado,
                'observaciones' => $request->observaciones,
                'review_status' => 'pending', // Cambiar a pending para que vuelva a revisión
                'review_observations' => null, // Limpiar observaciones de rechazo
                'reviewed_by' => null,
                'review_date' => null,
            ]);

            // Actualizar o crear el control analítico
            $analyticalControl = AnalyticalControl::where('process_id', $analysis->process_id)
                ->where('analysis_type', 'cationic')
                ->first();

            if (!$analyticalControl) {
                $analyticalControl = new AnalyticalControl();
                $analyticalControl->process_id = $analysis->process_id;
                $analyticalControl->analysis_type = 'cationic';
            }

            $analyticalControl->fill([
                'blanco_identificacion' => $request->blanco_identificacion,
                'blanco_lcm' => $request->blanco_lcm,
                'blanco_valor_leido' => $request->blanco_valor_leido,
                'blanco_aceptable' => $request->blanco_aceptable,
                'blanco_observaciones' => $request->blanco_observaciones,
                
                'error_identificacion' => $request->error_identificacion,
                'error_valor_teorico' => $request->error_valor_teorico,
                'error_valor_leido' => $request->error_valor_leido,
                'error_porcentaje' => $request->error_porcentaje,
                'error_aceptable' => $request->error_aceptable,
                'error_observaciones' => $request->error_observaciones,
                
                'recuperacion_identificacion' => $request->recuperacion_identificacion,
                'recuperacion_valor_teorico' => $request->recuperacion_valor_teorico,
                'recuperacion_valor_leido' => $request->recuperacion_valor_leido,
                'recuperacion_porcentaje' => $request->recuperacion_porcentaje,
                'recuperacion_aceptable' => $request->recuperacion_aceptable,
                'recuperacion_observaciones' => $request->recuperacion_observaciones,
                
                'dpr_identificacion' => $request->dpr_identificacion,
                'dpr_replica1' => $request->dpr_replica1,
                'dpr_replica2' => $request->dpr_replica2,
                'dpr_porcentaje' => $request->dpr_porcentaje,
                'dpr_aceptable' => $request->dpr_aceptable,
                'dpr_observaciones' => $request->dpr_observaciones,
            ]);

            $analyticalControl->save();

            // Actualizar el estado del detalle del servicio a 'completed' para que vaya a revisión
            $serviceProcessDetail = ServiceProcessDetail::where('process_id', $analysis->process_id)
                ->where('service_id', $serviceId)
                ->first();

            if ($serviceProcessDetail) {
                $serviceProcessDetail->status = 'completed';
                $serviceProcessDetail->save();
            }

            DB::commit();

            Log::info('Análisis de intercambio catiónico rechazado actualizado exitosamente', [
                'analysis_id' => $analysis->id,
                'process_id' => $analysis->process_id,
                'user_id' => Auth::id()
            ]);

            return redirect()->route('lscefa.technical.analyses.cationic.index')
                ->with('success', 'Análisis de intercambio catiónico corregido y enviado para revisión.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al actualizar análisis de intercambio catiónico rechazado: ' . $e->getMessage(), [
                'analysis_id' => $analysis->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Error al actualizar el análisis: ' . $e->getMessage());
        }
    }
} 