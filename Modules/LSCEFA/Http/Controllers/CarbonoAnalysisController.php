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

    public function carbonoAnalysis($processId, $serviceId)
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
    // Normalizar campos numéricos para todas las filas
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

    if ($request->has('rows')) {
        foreach ($request->rows as $index => $row) {
            foreach ($numericFields as $field) {
                if (isset($row[$field])) {
                    $value = str_replace(',', '.', $row[$field]);
                    $request->merge(["rows.$index.$field" => $value]);
                }
            }
        }
    }

    // Validación para múltiples filas
    $validated = $request->validate([
        'rows' => 'required|array|min:1',
        'rows.*.codigo_interno' => 'required|string|max:255',
        'rows.*.peso_muestra' => 'required|numeric|min:0',
        'rows.*.volumen_sulfato_blanco' => 'required|numeric|min:0',
        'rows.*.volumen_sulfato_muestra' => 'required|numeric|min:0',
        'rows.*.volumen_dicromato' => 'required|numeric|min:0',
        'rows.*.molaridad_sulfato' => 'nullable|numeric',
        'rows.*.porcentaje_co_total' => 'nullable|numeric',
        'rows.*.porcentaje_cot' => 'nullable|numeric',
        'rows.*.porcentaje_mo' => 'nullable|numeric',
        'rows.*.porcentaje_humedad' => 'nullable|numeric',
        'rows.*.fortificado' => 'nullable|numeric',
        'rows.*.cot_muestra' => 'nullable|numeric',
        'rows.*.error_analitico' => 'nullable|numeric',
        'rows.*.valor_leido' => 'nullable|numeric',
        'rows.*.valor_cot_leido' => 'nullable|numeric',
        'rows.*.observaciones' => 'nullable|string',
        'process_id' => 'required|string',
        
        'fecha_analisis' => 'required|date',
        'nombre_metodo' => 'required|string|max:255',
        'equipo_utilizado' => 'required|string|max:255',
        'intervalo_metodo' => 'required|string|max:255',
        'unidades_reporte_equipo' => 'required|string|max:255',
        'resolucion_instrumental' => 'nullable|string|max:255',
        'controles_analiticos' => 'nullable|array',
        'controles_analiticos.identificacion_mf' => 'nullable|regex:/^[A-Za-z0-9\-]+$/',
        'controles_analiticos.identificacion_mr' => 'nullable|regex:/^[A-Za-z0-9\-]+$/',
        'controles_analiticos.identificacion_dm' => 'nullable|regex:/^[A-Za-z0-9\-]+$/',
        'controles_analiticos.identificacion_bm' => 'nullable|regex:/^[A-Za-z0-9\-]+$/',
        'controles_analiticos.limite_cuantificacion_metodo' => 'nullable|numeric',
        'controles_analiticos.valor_leido' => 'nullable|numeric',
        'controles_analiticos.valor_referencia' => 'nullable|numeric',
        'controles_analiticos.valor_obtenido' => 'nullable|numeric',
        'controles_analiticos.recuperacion' => 'nullable|numeric',
        'controles_analiticos.replica_1' => 'nullable|numeric',
        'controles_analiticos.replica_2' => 'nullable|numeric',
        'controles_analiticos.dpr' => 'nullable|numeric',
        'controles_analiticos.aceptable_blanco' => 'nullable|string',
        'controles_analiticos.aceptable_fortificada' => 'nullable|string',
        'controles_analiticos.aceptable_referencia' => 'nullable|string',
        'controles_analiticos.aceptable_duplicado' => 'nullable|string',
        'controles_analiticos.observaciones' => 'nullable|string',
    ]);

    DB::beginTransaction();

    try {
        $process = Process::with('serviceProcessDetails')->findOrFail($validated['process_id']);

        // Generar un consecutivo base para todas las muestras
        $baseConsecutivo = 'CARB-' . date('Ymd') . '-' . str_pad(CarbonoAnalysis::whereDate('created_at', today())->count() + 1, 3, '0', STR_PAD_LEFT);

        foreach ($validated['rows'] as $index => $row) {
            // Crear un consecutivo único para cada fila
            $consecutivo = $baseConsecutivo . '-' . ($index + 1);

            $carbonoAnalysis = CarbonoAnalysis::create([
                'process_id' => $validated['process_id'],
                'consecutivo_no' => $consecutivo,
                'fecha_analisis' => $validated['fecha_analisis'],
                'nombre_metodo' => $validated['nombre_metodo'],
                'equipo_utilizado' => $validated['equipo_utilizado'],
                'intervalo_metodo' => $validated['intervalo_metodo'],
                'unidades_reporte_equipo' => $validated['unidades_reporte_equipo'],
                'resolucion_instrumental' => $validated['resolucion_instrumental'] ?? null,
                'codigo_interno' => $row['codigo_interno'],
                'peso_muestra' => $row['peso_muestra'],
                'volumen_sulfato_blanco' => $row['volumen_sulfato_blanco'],
                'volumen_sulfato_muestra' => $row['volumen_sulfato_muestra'],
                'volumen_dicromato' => $row['volumen_dicromato'],
                'molaridad_sulfato' => $row['molaridad_sulfato'] ?? null,
                'porcentaje_co_total' => $row['porcentaje_co_total'] ?? null,
                'porcentaje_cot' => $row['porcentaje_cot'] ?? null,
                'porcentaje_mo' => $row['porcentaje_mo'] ?? null,
                'porcentaje_humedad' => $row['porcentaje_humedad'] ?? null,
                'fortificado' => $row['fortificado'] ?? null,
                'cot_muestra' => $row['cot_muestra'] ?? null,
                'error_analitico' => $row['error_analitico'] ?? null,
                'valor_leido' => $row['valor_leido'] ?? null,
                'valor_cot_leido' => $row['valor_cot_leido'] ?? null,
                'observaciones' => $row['observaciones'] ?? null,
                'review_status' => 'pending',
            ]);

            // Guardar controles analíticos solo para la primera fila
            if ($index === 0 && !empty($validated['controles_analiticos'])) {
                $controles = $validated['controles_analiticos'];
                
                AnalyticalControl::create([
                    'carbono_analysis_id' => $carbonoAnalysis->id,
                    'process_id' => $validated['process_id'],
                    'identificacion_mf' => $controles['identificacion_mf'] ?? null,
                    'identificacion_mr' => $controles['identificacion_mr'] ?? null,
                    'identificacion_dm' => $controles['identificacion_dm'] ?? null,
                    'identificacion_bm' => $controles['identificacion_bm'] ?? null,
                    'limite_cuantificacion_metodo' => $controles['limite_cuantificacion_metodo'] ?? null,
                    'valor_leido' => $controles['valor_leido'] ?? null,
                    'valor_referencia' => $controles['valor_referencia'] ?? null,
                    'valor_obtenido' => $controles['valor_obtenido'] ?? null,
                    'recuperacion' => $controles['recuperacion'] ?? null,
                    'replica_1' => $controles['replica_1'] ?? null,
                    'replica_2' => $controles['replica_2'] ?? null,
                    'dpr' => $controles['dpr'] ?? null,
                    'aceptable_blanco' => $controles['aceptable_blanco'] ?? null,
                    'aceptable_fortificada' => $controles['aceptable_fortificada'] ?? null,
                    'aceptable_referencia' => $controles['aceptable_referencia'] ?? null,
                    'aceptable_duplicado' => $controles['aceptable_duplicado'] ?? null,
                    'observaciones' => $controles['observaciones'] ?? null,
                ]);
            }
        }

        // Actualizar estado del proceso
        $process->serviceProcessDetails()->update(['status' => 'completed']);

        DB::commit();

        return redirect()->route('lscefa.technical.analyses.carbon.index')
            ->with('success', 'Análisis de carbono registrados correctamente.');

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error al guardar análisis de carbono: ' . $e->getMessage(), [
            'exception' => $e,
            'request_data' => $request->all()
        ]);
        return back()->with('error', 'Hubo un error al guardar los análisis: ' . $e->getMessage())->withInput();
    }
}   
}