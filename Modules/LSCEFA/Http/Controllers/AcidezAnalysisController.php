<?php

namespace Modules\LSCEFA\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Modules\LSCEFA\Entities\AnalyticalControl;
use Modules\LSCEFA\Entities\AcidezAnalysis; // Importación correcta del modelo
use Modules\LSCEFA\Models\Process;
use Modules\LSCEFA\Models\Service;

class AcidezAnalysisController extends Controller
{
public function index()
{
    try {
        Log::info('Usuario accede a index de análisis de acidez', [
            'user_id' => Auth::id(),
            'role' => Auth::user()->role ?? 'N/A'
        ]);

        // Buscar el servicio de acidez
        $acidityService = Service::where('descripcion', 'like', '%acidez%')->first();

        // Inicializar variables
        $pendingAnalyses = collect();
        $returnedAnalyses = collect();
        $serviceMessage = null;

        if ($acidityService) {
            // Obtener análisis pendientes (para el procesamiento por lotes)
            $pendingAnalyses = Process::with(['serviceProcessDetails.service', 'acidezAnalyses'])
                ->whereHas('serviceProcessDetails', function($query) use ($acidityService) {
                    $query->where('service_id', $acidityService->services_id);
                })
                ->where(function($query) {
                    $query->where('status', 'pending')
                          ->orWhereNull('status');
                })
                ->orderBy('reception_date', 'asc')
                ->get();

            // Obtener análisis devueltos (si los manejas)
            $returnedAnalyses = Process::with(['serviceProcessDetails.service', 'acidezAnalyses'])
                ->whereHas('serviceProcessDetails', function($query) use ($acidityService) {
                    $query->where('service_id', $acidityService->services_id);
                })
                ->where('status', 'returned')
                ->orderBy('reception_date', 'desc')
                ->get();

            if ($pendingAnalyses->isEmpty()) {
                $serviceMessage = 'No hay análisis de acidez pendientes';
            }
        } else {
            $serviceMessage = 'No hay servicio de acidez configurado en el sistema';
            Log::warning('Servicio de acidez no encontrado');
        }

        return view('lscefa::analyses.acidity.index', [
            'pendingAnalyses' => $pendingAnalyses,
            'acidityAnalyses' => $returnedAnalyses, // Cambiado para coincidir con la vista
            'serviceMessage' => $serviceMessage,
            'hasPendingProcesses' => $pendingAnalyses->isNotEmpty()
        ]);

    } catch (\Exception $e) {
        Log::error('Error en índice de acidez: '.$e->getMessage(), [
            'exception' => $e
        ]);
        return back()->with('error', 'Error al cargar los análisis: '.$e->getMessage());
    }
}
  public function acidityAnalysis($processId)
{
    try {
        $process = Process::with(['quote.customer'])
                        ->where('process_id', $processId)
                        ->firstOrFail();

        $analysis = new AcidezAnalysis();

        return view('lscefa::analyses.acidity.process', ['process' => $process,'analysis' => $analysis, 'user' => Auth::user()]);

    } catch (\Exception $e) {
        Log::error('Error al cargar formulario de acidez: ' . $e->getMessage());
        return back()->with('error', 'Error al cargar el formulario: ' . $e->getMessage());
    }
}

public function storeAcidezAnalysis(Request $request)
{
    // Validar que existan filas
    $request->validate([
        'rows' => 'required|array|min:1',
        'rows.*.codigo_interno' => 'required|string',
        'rows.*.peso_muestra' => 'required|numeric',
        'rows.*.porcentaje_humedad' => 'nullable|numeric',
        'rows.*.consumido_blanco' => 'required|numeric',
        'rows.*.molaridad' => 'required|numeric',
        'rows.*.consumido_muestra' => 'required|numeric',
        'rows.*.acidez' => 'required|numeric',
        'process_id' => 'required|string',
        'fecha_analisis' => 'required|date',
        'nombre_metodo' => 'required|string',
        'equipo_utilizado' => 'required|string',
        'controles_analiticos' => 'nullable|array',
    ]);

    DB::beginTransaction();

    try {
        $process = Process::findOrFail($request->process_id);

        // Generar un consecutivo base para todas las muestras
        $baseConsecutivo = 'ACID-' . date('Ymd') . '-' . str_pad(AcidezAnalysis::whereDate('created_at', today())->count() + 1, 3, '0', STR_PAD_LEFT);

        foreach ($request->rows as $index => $row) {
            // Crear un consecutivo único para cada fila
            $consecutivo = $baseConsecutivo . '-' . ($index + 1);

            $acidezAnalysis = AcidezAnalysis::create([
                'process_id' => $request->process_id,
                'consecutivo_no' => $consecutivo,
                'fecha_analisis' => $request->fecha_analisis,
                'unidades_reporte_equipo' => $request->unidades_reporte_equipo ?? null,
                'nombre_metodo' => $request->nombre_metodo,
                'equipo_utilizado' => $request->equipo_utilizado,
                'intervalo_metodo' => $request->intervalo_metodo ?? null,
                
                'resolucion_instrumental' => $request->resolucion_instrumental ?? null,
                'codigo_interno' => $row['codigo_interno'],
                'peso_muestra' => $row['peso_muestra'],
                'consumido_blanco' => $row['consumido_blanco'],
                'porcentaje_humedad' => $row['porcentaje_humedad'] ?? null,
                'molaridad' => $row['molaridad'],
                'consumido_muestra' => $row['consumido_muestra'] ?? null,
                'acidez' => $row['acidez'],
                'valor_obtenido' => $row['valor_obtenido'] ?? null,
                'valor_referencia' => $row['valor_referencia'] ?? null,
                'error_analitico' => $row['error_analitico'] ?? null,
                'recuperacion' => $row['recuperacion'] ?? null,
                'status' => 'pending',         
                
            ]);

            // Guardar controles analíticos si existen
            if (!empty($request->controles_analiticos)) {
                AnalyticalControl::create([
                    'analysis_id' => $acidezAnalysis->id,
                    'process_id' => $request->process_id,
                    'identificacion_mf' => $request->controles_analiticos['identificacion_mf'] ?? null,
                    'identificacion_mr' => $request->controles_analiticos['identificacion_mr'] ?? null,
                    'identificacion_dm' => $request->controles_analiticos['identificacion_dm'] ?? null,
                    'identificacion_bm' => $request->controles_analiticos['identificacion_bm'] ?? null,
                    'valor_referencia' => $request->controles_analiticos['valor_referencia'] ?? null,
                    'valor_obtenido' => $request->controles_analiticos['valor_obtenido'] ?? null,
                    'recuperacion' => $request->controles_analiticos['recuperacion'] ?? null,
                    'blanco_metodo' => $request->controles_analiticos['blanco_metodo'] ?? null,
                    
                    'limite_cuantificacion_metodo' => $request->controles_analiticos['limite_cuantificacion_metodo'] ?? null,
                    'replica_1' => $request->controles_analiticos['replica_1'] ?? null,
                    'replica_2' => $request->controles_analiticos['replica_2'] ?? null,
                    'dpr' => $request->controles_analiticos['dpr'] ?? null,
                    'estado' => isset($request->controles_analiticos['aceptable']) 
                        ? ($request->controles_analiticos['aceptable'] === 'aceptable' ? 'Aceptable' : 'No Aceptable') 
                        : null,
                    'observaciones' => $request->controles_analiticos['observaciones'] ?? null,
                ]);
            }
        }

        // Actualiza el estado del proceso
        $process->status = 'completed';
        $process->save();

        DB::commit();

        return redirect()->route('lscefa.technical.analyses.acidity.index')
            ->with('success', 'Análisis de acidez registrados correctamente. Esperando revisión.');

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error al guardar análisis de acidez: ' . $e->getMessage());
        return back()->with('error', 'Hubo un error al guardar los análisis: ' . $e->getMessage())->withInput();
    }
}

public function batchProcess(Request $request)
{
    try {
        // Validar que hay selección
        if (!$request->has('selected_analyses') || empty($request->selected_analyses)) {
            return back()->with('error', 'Debe seleccionar al menos un análisis para procesar.');
        }

        // Preparar datos para la vista de procesamiento
        $analysesData = [];
        foreach ($request->selected_analyses as $selected) {
            list($processId, $serviceId) = explode('_', $selected);
            
            $process = Process::findOrFail($processId);
            $service = Service::findOrFail($serviceId);

            $analysesData[] = [
                'process_id' => $processId,
                'service_id' => $serviceId,
                'process_code' => $process->item_code,
                'service_name' => $service->descripcion
            ];
        }

        // Pasar a la vista de procesamiento por lotes
        return view('lscefa::analyses.acidity.batchprocess', [
            'analyses' => $analysesData
        ]);

    } catch (\Exception $e) {
        return back()->with('error', 'Error al preparar el lote: ' . $e->getMessage());
    }
}
public function batchStore(Request $request)
{
    DB::beginTransaction();
    
    try {
        // Depuración: Ver datos recibidos
        \Log::debug('Datos recibidos en batchStore:', [
            'analyses' => $request->analyses,
            'muestras_especiales' => $request->muestras_especiales,
            'controles_analiticos' => $request->controles_analiticos
        ]);

        if (!$request->has('analyses') || empty($request->analyses)) {
            throw new \Exception('No hay análisis seleccionados para procesar');
        }

        $savedCount = 0;
        $errors = [];

        // Procesar cada análisis del lote
        foreach ($request->analyses as $index => $analysisData) {
            try {
                // Depuración: Ver datos del análisis actual
                \Log::debug("Procesando análisis #$index:", $analysisData);

                // Validar datos mínimos
                if (empty($analysisData['process_id']) || empty($analysisData['service_id'])) {
                    $errorMsg = "El análisis #$index no tiene process_id o service_id";
                    $errors[] = $errorMsg;
                    \Log::warning($errorMsg);
                    continue;
                }

                // Verificar que los campos requeridos estén presentes
                $requiredFields = [
                    'peso_muestra', 'consumido_blanco', 
                    'molaridad', 'consumido_muestra'
                ];
                
                foreach ($requiredFields as $field) {
                    if (!isset($analysisData[$field]) || $analysisData[$field] === '') {
                        $errorMsg = "El análisis #$index (Proceso: {$analysisData['process_id']}) no tiene el campo requerido: $field";
                        $errors[] = $errorMsg;
                        \Log::warning($errorMsg);
                        continue 2; // Saltar al siguiente análisis
                    }
                }

                // Crear nuevo registro de análisis
                $analysis = new AcidezAnalysis();
                
                // Información general (ajusta según tu modelo)
                $analysis->fill([
                    'process_id' => $analysisData['process_id'],
                    'consecutivo_no' => $request->consecutivo_no,
                    'fecha_analisis' => $request->fecha_analisis,
                    'unidades_reporte_equipo' => $request->unidades_reporte_equipo,
                    'nombre_metodo' => $request->nombre_metodo,
                    'equipo_utilizado' => $request->equipo_utilizado,
                    'intervalo_metodo' => $request->intervalo_metodo,
                    'resolucion_instrumental' => $request->resolucion_instrumental,
                    'codigo_interno' => $analysisData['codigo_interno'] ?? $analysisData['process_id'],
                    'peso_muestra' => $analysisData['peso_muestra'],
                    'consumido_blanco' => $analysisData['consumido_blanco'],
                    'porcentaje_humedad' => $analysisData['porcentaje_humedad'] ?? null,
                    'molaridad' => $analysisData['molaridad'],
                    'consumido_muestra' => $analysisData['consumido_muestra'],
                    'acidez' => $analysisData['acidez'] ?? null,
                ]);

                // Controles analíticos
                if ($request->has('controles_analiticos')) {
                    $analysis->controles_analiticos = json_encode($request->controles_analiticos);
                }
                
                // Resultados de muestras (incluyendo las especiales si existen)
                $muestras = [];
                
                // Agregar muestras especiales si existen
                if ($request->has('muestras_especiales')) {
                    foreach ($request->muestras_especiales as $tipo => $muestra) {
                        if (!empty($muestra['codigo_interno'])) {
                            $muestras[$tipo] = $muestra;
                        }
                    }
                }
                
                // Agregar la muestra del análisis actual
                $muestras['muestra_'.$analysisData['process_id']] = [
                    'codigo_interno' => $analysisData['codigo_interno'] ?? $analysisData['process_id'],
                    'peso_muestra' => $analysisData['peso_muestra'],
                    'consumido_blanco' => $analysisData['consumido_blanco'],
                    'porcentaje_humedad' => $analysisData['porcentaje_humedad'] ?? null,
                    'molaridad' => $analysisData['molaridad'],
                    'consumido_muestra' => $analysisData['consumido_muestra'],
                    'acidez' => $analysisData['acidez'] ?? null
                ];
                
                $analysis->muestras = json_encode($muestras);
                
                // Guardar el análisis
                if ($analysis->save()) {
                    $savedCount++;
                    
                    // Actualizar estado del proceso
                    Process::where('process_id', $analysisData['process_id'])
                          ->update(['status' => 'completed']);
                } else {
                    $errorMsg = "No se pudo guardar el análisis #$index (Proceso: {$analysisData['process_id']})";
                    $errors[] = $errorMsg;
                    \Log::error($errorMsg);
                }

            } catch (\Exception $e) {
                $errorMsg = "Error en análisis #$index (Proceso: {$analysisData['process_id']}): " . $e->getMessage();
                $errors[] = $errorMsg;
                \Log::error($errorMsg, [
                    'process_id' => $analysisData['process_id'] ?? null,
                    'exception' => $e
                ]);
            }
        }

        DB::commit();

        // Preparar mensaje de respuesta
        $message = "Se guardaron correctamente $savedCount análisis de acidez";
        if (!empty($errors)) {
            $message .= ". Hubo " . count($errors) . " errores";
            \Log::warning("Errores al procesar lote de acidez", ['errors' => $errors]);
            
            // Agregar errores a la sesión para mostrarlos
            foreach ($errors as $error) {
                \Session::flash('error_details', $errors);
            }
        }

        return redirect()
            ->route('lscefa.technical.analyses.acidity.index')
            ->with('success', $message)
            ->with('errors', $errors);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error("Error crítico al procesar lote de acidez: " . $e->getMessage());
        return back()
            ->withInput()
            ->with('error', 'Error al guardar el lote: ' . $e->getMessage());
    }
}
    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('lscefa::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }
}
