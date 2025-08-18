<?php

namespace Modules\LSCEFA\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\LSCEFA\Entities\AnalyticalControl;
use Modules\LSCEFA\Models\Process;
use Modules\LSCEFA\Models\Service;
use Modules\LSCEFA\Entities\CarbonoAnalysis;

class CarbonoAnalysisController extends Controller
{
    /**
     * Lista de procesos pendientes para análisis de carbono.
     */
       public function index()
    {
        try {
            Log::info('Usuario accede a index de análisis de carbono orgánico', [
                'user_id' => Auth::id(),
                'role' => optional(Auth::user())->role ?? 'N/A'
            ]);

            // Buscar el servicio de carbono
            $carbonService = Service::where('descripcion', 'like', '%carbono%')
                                  ->orWhere('descripcion', 'like', '%carbon%')
                                  ->orWhere('descripcion', 'like', '%orgánico%')
                                  ->first();

            // Inicializar variables
            $processes = collect();
            $carbonAnalyses = collect();
            $serviceMessage = null;

            if ($carbonService) {
                $processes = Process::with(['carbonoAnalyses', 'serviceProcessDetails'])
                    ->whereHas('serviceProcessDetails', function($query) use ($carbonService) {
                        $query->where('service_id', $carbonService->services_id)
                              ->where('status', 'pending');
                    })
                    ->orderBy('reception_date', 'desc')
                    ->get();

                $carbonAnalyses = CarbonoAnalysis::with('process.serviceProcessDetails')
                    ->whereHas('process.serviceProcessDetails', function($query) use ($carbonService) {
                        $query->where('service_id', $carbonService->services_id)
                              ->where('status', 'pending');
                    })
                    ->get();
            } else {
                $serviceMessage = 'No hay ningún servicio de carbono orgánico configurado en el sistema';
                Log::warning('Servicio de carbono no encontrado');
            }

            return view('lscefa::analyses.carbon.index', [
                'processes' => $processes,
                'carbonAnalyses' => $carbonAnalyses,
                'serviceMessage' => $serviceMessage
            ]);

        } catch (\Exception $e) {
            Log::error('Error en índice de carbono: '.$e->getMessage());
            return back()->with('error', 'Error al cargar los análisis: '.$e->getMessage());
        }
    }

    public function carbonAnalysis($processId, $serviceId)
    {
        try {
            $process = Process::with(['quote.customer', 'serviceProcessDetails'])
                            ->where('process_id', $processId)
                            ->firstOrFail();

            $service = Service::findOrFail($serviceId);

            // Validar que el proceso tenga la relación con el servicio
            if (!$process->serviceProcessDetails->where('service_id', $serviceId)->first()) {
                throw new \Exception('El proceso no tiene asociado este servicio');
            }

            $carbonAnalysis = new CarbonoAnalysis();

            return view('lscefa::analyses.carbon.process', compact(
                'process',
                'service',
                'serviceId',
                'carbonAnalysis'
            ));

        } catch (\Exception $e) {
            Log::error('Error al cargar análisis de carbono: '.$e->getMessage());
            return back()->with('error', 'No se pudo cargar el análisis: '.$e->getMessage());
        }
    }

    /**
     * Guardar un nuevo análisis de carbono.
     */
    public function storeCarbonoAnalysis(Request $request)
    {
        // Debug: Log todos los datos recibidos
        Log::info('Datos recibidos en storeCarbonoAnalysis:', $request->all());

        $process = Process::find($request->process_id);
        if (!$process) {
            Log::error('Proceso no encontrado: ' . $request->process_id);
            return back()->with('error', 'Proceso no encontrado');
        }

        // Normalizar campos numéricos
        $numericFields = [
            'peso_muestra',
            'volumen_sulfato_blanco',
            'volumen_sulfato_muestra',
            'volumen_dicromato',
            'molaridad_sulfato',
            'porcentaje_co_total',
            'porcentaje_cot',
            'porcentaje_mo',
            'porcentaje_humedad',
            'fortificado',
            'cot_muestra',
            'error_analitico',
            'valor_leido',
            'valor_cot_leido',
        ];

        // Normalizar campos de controles analíticos
        if ($request->has('controles_analiticos') && is_array($request->controles_analiticos)) {
            $controles = $request->controles_analiticos;
            $numericControlFields = [
                'limite_cuantificacion_metodo', 'valor_leido', 'valor_obtenido', 'recuperacion',
                'valor_referencia', 'replica_1', 'replica_2', 'dpr'
            ];
            
            foreach ($numericControlFields as $field) {
                if (isset($controles[$field])) {
                    $controles[$field] = str_replace(',', '.', $controles[$field]);
                }
            }
            $request->merge(['controles_analiticos' => $controles]);
        }

        foreach ($numericFields as $field) {
            if ($request->has($field) && $request->input($field) !== null) {
                $value = str_replace(',', '.', $request->input($field));
                $request->merge([$field => is_numeric($value) ? (float)$value : null]);
            }
        }

        // Validaciones más flexibles
        try {
            $validated = $request->validate([
                'process_id' => 'required|string',
                'service_id' => 'required|integer',
                'consecutivo_no' => 'required|string|max:255',
                'fecha_analisis' => 'required|date',
                'nombre_metodo' => 'required|string|max:255',
                'equipo_utilizado' => 'required|string|max:255',
                'intervalo_metodo' => 'required|string|max:255',
                'unidades_reporte_equipo' => 'required|string|max:255',
                'resolucion_instrumental' => 'nullable|string|max:255',
                'codigo_interno' => 'required|string|max:255',
                'peso_muestra' => 'required|numeric|min:0',
                'volumen_sulfato_blanco' => 'required|numeric|min:0',
                'volumen_sulfato_muestra' => 'required|numeric|min:0',
                'volumen_dicromato' => 'required|numeric|min:0',
                'molaridad_sulfato' => 'nullable|numeric',
                'porcentaje_co_total' => 'nullable|numeric',
                'porcentaje_cot' => 'nullable|numeric',
                'porcentaje_mo' => 'nullable|numeric',
                'porcentaje_humedad' => 'nullable|numeric',
                'fortificado' => 'nullable|numeric',
                'cot_muestra' => 'nullable|numeric',
                'valor_leido' => 'nullable|numeric',
                'error_analitico' => 'nullable|numeric',
                'observaciones' => 'nullable|string',
                'controles_analiticos' => 'nullable|array',

            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Errores de validación:', $e->errors());
            return back()->withErrors($e->errors())->withInput();
        }

        // Verificar unicidad del consecutivo
        $existingAnalysis = CarbonoAnalysis::where('consecutivo_no', $validated['consecutivo_no'])->first();
        if ($existingAnalysis) {
            return back()->with('error', 'El consecutivo ya existe. Por favor, use uno diferente.')->withInput();
        }

        DB::beginTransaction();

        try {
            Log::info('Iniciando creación de análisis de carbono:', $validated);

            $carbonoAnalysis = CarbonoAnalysis::create([
                'process_id' => $validated['process_id'],
                'service_id' => $validated['service_id'],
                'user_id' => auth()->id(),
                'consecutivo_no' => $validated['consecutivo_no'],
                'fecha_analisis' => $validated['fecha_analisis'],
                'nombre_metodo' => $validated['nombre_metodo'],
                'equipo_utilizado' => $validated['equipo_utilizado'],
                'intervalo_metodo' => $validated['intervalo_metodo'],
                'unidades_reporte_equipo' => $validated['unidades_reporte_equipo'],
                'resolucion_instrumental' => $validated['resolucion_instrumental'] ?? null,
                'codigo_interno' => $validated['codigo_interno'],
                'peso_muestra' => $validated['peso_muestra'],
                'volumen_sulfato_blanco' => $validated['volumen_sulfato_blanco'],
                'volumen_sulfato_muestra' => $validated['volumen_sulfato_muestra'],
                'volumen_dicromato' => $validated['volumen_dicromato'],
                'molaridad_sulfato' => $validated['molaridad_sulfato'] ?? null,
                'porcentaje_co_total' => $validated['porcentaje_co_total'] ?? null,
                'porcentaje_cot' => $validated['porcentaje_cot'] ?? null,
                'porcentaje_mo' => $validated['porcentaje_mo'] ?? null,
                'porcentaje_humedad' => $validated['porcentaje_humedad'] ?? null,
                'fortificado' => $validated['fortificado'] ?? null,
                'cot_muestra' => $validated['cot_muestra'] ?? null,
                'error_analitico' => $validated['error_analitico'] ?? null,
                'observaciones' => $validated['observaciones'] ?? null,
            ]);

            Log::info('Análisis de carbono creado con ID: ' . $carbonoAnalysis->id);

            // Guardar controles analíticos si vienen
            if (!empty($validated['controles_analiticos'])) {
                $controles = $validated['controles_analiticos'];
                
                $analyticalControl = AnalyticalControl::create([
                    'carbono_analysis_id' => $carbonoAnalysis->id,
                    'process_id' => $validated['process_id'],
                    'service_id' => $validated['service_id'],
                    'identificacion_mf' => $controles['identificacion_mf'] ?? null,
                    'identificacion_mr' => $controles['identificacion_mr'] ?? null,
                    'identificacion_dm' => $controles['identificacion_dm'] ?? null,
                    'identificacion_bm' => $controles['identificacion_bm'] ?? null,
                    'limite_cuantificacion_metodo' => isset($controles['limite_cuantificacion_metodo']) ? (float)$controles['limite_cuantificacion_metodo'] : null,
                    'valor_leido' => isset($controles['valor_leido']) ? (float)$controles['valor_leido'] : null,
                    'valor_referencia' => isset($controles['valor_referencia']) ? (float)$controles['valor_referencia'] : null,
                    'valor_obtenido' => isset($controles['valor_obtenido']) ? (float)$controles['valor_obtenido'] : null,
                    'recuperacion' => isset($controles['recuperacion']) ? (float)$controles['recuperacion'] : null,
                    'replica_1' => isset($controles['replica_1']) ? (float)$controles['replica_1'] : null,
                    'replica_2' => isset($controles['replica_2']) ? (float)$controles['replica_2'] : null,
                    'dpr' => isset($controles['dpr']) ? (float)$controles['dpr'] : null,
                    'aceptable_blanco' => $controles['aceptable_blanco'] ?? null,
                    'aceptable_fortificada' => $controles['aceptable_fortificada'] ?? null,
                    'aceptable_referencia' => $controles['aceptable_referencia'] ?? null,
                    'aceptable_duplicado' => $controles['aceptable_duplicado'] ?? null,
                    'observaciones' => $controles['observaciones'] ?? null,
                ]);

                Log::info('Control analítico creado con ID: ' . $analyticalControl->id);
            }

            // Actualizar estado del servicio
            $updated = $process->serviceProcessDetails()
                ->where('service_id', $validated['service_id'])
                ->update(['status' => 'completed']);

            Log::info('Servicios actualizados: ' . $updated);

            // Verificar si todos los servicios están completados
            $pendingServices = $process->serviceProcessDetails()
                ->where('status', 'pending')
                ->count();

            if ($pendingServices === 0) {
                $process->status = 'completed';
                $process->save();
                Log::info('Proceso marcado como completado: ' . $process->process_id);
            }

            DB::commit();

            Log::info('Transacción completada exitosamente');

            return redirect()->route('lscefa.technical.analyses.carbon.index')
                ->with('success', 'Análisis de carbono registrado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar análisis de carbono: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->with('error', 'Hubo un error al guardar el análisis: ' . $e->getMessage())->withInput();
        }
    }
}