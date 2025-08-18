<?php

namespace Modules\LSCEFA\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Modules\LSCEFA\Entities\HumidityAnalysis;
use Modules\LSCEFA\Entities\AnalyticalControl;
use Illuminate\Support\Facades\Log;
use Modules\LSCEFA\Models\Process;
use Modules\LSCEFA\Models\Service;



class HumidityAnalysisController extends Controller
{
 public function index()
    {
        try {
            Log::info('Usuario accede a index de análisis de humedad', [
                'user_id' => Auth::id(),
                'role' => Auth::user()->role ?? 'N/A'
            ]);

            // Buscar el servicio de humedad
            $humidityService = Service::where('descripcion', 'like', '%humedad%')->first();

            // Inicializar variables
            $processes = collect();
            $humidityAnalyses = collect();
            $serviceMessage = null;

            if ($humidityService) {
                $processes = Process::with(['humidityAnalyses', 'serviceProcessDetails'])
                    ->whereHas('serviceProcessDetails', function($query) use ($humidityService) {
                        $query->where('service_id', $humidityService->services_id)
                              ->where('status', 'pending');
                    })
                    ->orderBy('reception_date', 'desc')
                    ->get();

                $humidityAnalyses = HumidityAnalysis::with('process.serviceProcessDetails')
                    ->whereHas('process.serviceProcessDetails', function($query) use ($humidityService) {
                        $query->where('service_id', $humidityService->services_id)
                              ->where('status', 'pending');
                    })
                    ->get();
            } else {
                $serviceMessage = 'No hay ningún servicio de humedad configurado en el sistema';
                Log::warning('Servicio de humedad no encontrado');
            }

            return view('lscefa::analyses.humidity.index', [
                'processes' => $processes,
                'humidityAnalyses' => $humidityAnalyses,
                'serviceMessage' => $serviceMessage
            ]);

        } catch (\Exception $e) {
            Log::error('Error en índice de humedad: '.$e->getMessage());
            return back()->with('error', 'Error al cargar los análisis: '.$e->getMessage());
        }
    }

 public function humidityAnalysis($processId)
{
    try {
        // Obtener el proceso con sus relaciones necesarias
        $process = Process::with(['serviceProcessDetails.service'])
                        ->where('process_id', (string)$processId)
                        ->firstOrFail();
        
        // Obtener el servicio asociado si existe
        $service = optional($process->serviceProcessDetails->first())->service;

        return view('lscefa::analyses.humidity.process', compact('process', 'service'));

    } catch (\Exception $e) {
        Log::error('Error al cargar análisis de humedad: '.$e->getMessage());
        return back()->with('error', 'No se pudo cargar el análisis solicitado');
    }
}
public function storeHumidityAnalysis(Request $request)
{
    // Normalizar campos numéricos para todas las filas
    $numericFields = [
        'peso_capsula',
        'peso_muestra',
        'peso_capsula_muestra_humedad',
        'peso_capsula_muestra_seca',
        'porcentaje_humedad',
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
        'rows.*.codigo_interno' => 'nullable|regex:/^[A-Za-z0-9\-]+$/',
        'rows.*.peso_capsula' => 'required|numeric',
        'rows.*.peso_muestra' => 'required|numeric',
        'rows.*.peso_capsula_muestra_humedad' => 'required|numeric',
        'rows.*.peso_capsula_muestra_seca' => 'required|numeric',
        'rows.*.porcentaje_humedad' => 'required|numeric',
        'rows.*.observaciones' => 'nullable|string',
        'process_id' => 'required|string',
        
        'fecha_analisis' => 'required|date',
        'hora_ingreso_horno' => 'required',
        'hora_salida_horno' => 'required',
        'temperatura_horno' => 'required|numeric',
        'nombre_metodo' => 'required|string|max:255',
        'intervalo_metodo' => 'required|string|max:255',
        'equipo_utilizado' => 'required|string|max:255',
        'unidades_reporte_equipo' => 'required|string|max:255',
        'resolucion_instrumental' => 'nullable|string|max:255',
        'fecha_fin_analisis' => 'nullable|date',
        'controles_analiticos' => 'nullable|array',
        'controles_analiticos.masa_suelo' => 'nullable|numeric',
        'controles_analiticos.masa_agua' => 'nullable|numeric', 
        'controles_analiticos.masa_suelo_seco' => 'nullable|numeric',
        'controles_analiticos.humedad_fortificada_teorica' => 'nullable|numeric',
        'controles_analiticos.humedad_obtenida' => 'nullable|numeric',
        'controles_analiticos.humedad_fortificada' => 'nullable|numeric',
        'controles_analiticos.recuperacion' => 'nullable|numeric',  
        'controles_analiticos.valor_referencia' => 'nullable|numeric',
        'controles_analiticos.valor_obtenido' => 'nullable|numeric',
        'controles_analiticos.blanco_metodo' => 'nullable|numeric',
        'controles_analiticos.resultado' => 'nullable|string|max:255',
        'controles_analiticos.limite_cuantificacion_metodo' => 'nullable|string|max:255',
        'controles_analiticos.rango_metodo' => 'nullable|string',
        'controles_analiticos.humedad_replica_1' => 'nullable|numeric',
        'controles_analiticos.humedad_replica_2' => 'nullable|numeric',
        'controles_analiticos.dpr' => 'nullable|numeric',
        'controles_analiticos.identificacion_mf' => 'nullable|regex:/^[A-Za-z0-9\-]+$/',
        'controles_analiticos.identificacion_mr' => 'nullable|regex:/^[A-Za-z0-9\-]+$/',
        'controles_analiticos.identificacion_dm' => 'nullable|regex:/^[A-Za-z0-9\-]+$/',
        'controles_analiticos.identificacion_bm' => 'nullable|regex:/^[A-Za-z0-9\-]+$/',
        'controles_analiticos.aceptable' => 'nullable|string|in:aceptable,no_aceptable',
        'controles_analiticos.observaciones' => 'nullable|string',
        
  
    ]);

    DB::beginTransaction();

    try {
       $process = Process::with('serviceProcessDetails')->findOrFail($validated['process_id']);
      

        // Generar un consecutivo base para todas las muestras
        $baseConsecutivo = 'HUM-' . date('Ymd') . '-' . str_pad(HumidityAnalysis::whereDate('created_at', today())->count() + 1, 3, '0', STR_PAD_LEFT);

        foreach ($validated['rows'] as $index => $row) {
            // Crear un consecutivo único para cada fila
            $consecutivo = $baseConsecutivo . '-' . ($index + 1);

            $humidityAnalysis = HumidityAnalysis::create([
                'process_id' => $validated['process_id'],
                'consecutivo_no' => $consecutivo,
                'fecha_analisis' => $validated['fecha_analisis'],
                'hora_ingreso_horno' => $validated['hora_ingreso_horno'],
                'hora_salida_horno' => $validated['hora_salida_horno'],
                'temperatura_horno' => $validated['temperatura_horno'],
                'nombre_metodo' => $validated['nombre_metodo'],
                'intervalo_metodo' => $validated['intervalo_metodo'],
                'equipo_utilizado' => $validated['equipo_utilizado'],
                'unidades_reporte_equipo' => $validated['unidades_reporte_equipo'],
                'resolucion_instrumental' => $validated['resolucion_instrumental'] ?? null,
                'fecha_fin_analisis' => $validated['fecha_fin_analisis'] ?? null,
                'codigo_interno' => $row['codigo_interno'] ?? null,
                'peso_capsula' => $row['peso_capsula'],
                'peso_muestra' => $row['peso_muestra'],
                'peso_capsula_muestra_humedad' => $row['peso_capsula_muestra_humedad'],
                'peso_capsula_muestra_seca' => $row['peso_capsula_muestra_seca'],
                'porcentaje_humedad' => $row['porcentaje_humedad'],
                'observaciones' => $row['observaciones'] ?? null,
                'status' => 'pending',
            ]);

            // Guardar controles analíticos solo para la primera fila (o adaptar si es necesario)
            if ($index === 0 && !empty($validated['controles_analiticos'])) {
                $controles = $validated['controles_analiticos'];
                
                AnalyticalControl::create([
                    'analysis_id' => $humidityAnalysis->id,
                    'process_id' => $validated['process_id'],
                    'masa_suelo' => $controles['masa_suelo'] ?? null,
                    'masa_agua' => $controles['masa_agua'] ?? null,
                    'masa_suelo_seco' => $controles['masa_suelo_seco'] ?? null,
                    'humedad_fortificada_teorica' => $controles['humedad_fortificada_teorica'] ?? null,
                    'humedad_obtenida' => $controles['humedad_obtenida'] ?? null,
                    'humedad_fortificada' => $controles['humedad_fortificada'] ?? null,
                    'recuperacion' => $controles['recuperacion'] ?? null,
                    'valor_referencia' => $controles['valor_referencia'] ?? null,
                    'valor_obtenido' => $controles['valor_obtenido'] ?? null,
                    'blanco_metodo' => $controles['blanco_metodo'] ?? null,
                    'resultado' => $controles['resultado'] ?? null,
                    'limite_cuantificacion_metodo' => $controles['limite_cuantificacion_metodo'] ?? null,
                    'rango_metodo' => $controles['rango_metodo'] ?? null,
                    'humedad_replica_1' => $controles['humedad_replica_1'] ?? null,
                    'humedad_replica_2' => $controles['humedad_replica_2'] ?? null,
                    'dpr' => $controles['dpr'] ?? null,
                    'identificacion_mf' => $controles['identificacion_mf'] ?? null,
                    'identificacion_mr' => $controles['identificacion_mr'] ?? null,
                    'identificacion_dm' => $controles['identificacion_dm'] ?? null, 
                    'identificacion_bm' => $controles['identificacion_bm'] ?? null,
                    

                    'estado' => isset($controles['aceptable']) 
                        ? ($controles['aceptable'] === 'aceptable' ? 'Aceptable' : 'No Aceptable')
                        : null,
                    'observaciones' => $controles['observaciones'] ?? null,
                ]);
            }
        }

        // Actualizar estado del proceso
            $process->serviceProcessDetails()->update(['status' => 'completed']);
        DB::commit();

        return redirect()->route('lscefa.technical.analyses.humidity.index')
            ->with('success', 'Análisis de humedad registrados correctamente.');

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error al guardar análisis de humedad: ' . $e->getMessage(), [
            'exception' => $e,
            'request_data' => $request->all()
        ]);
        return back()->with('error', 'Hubo un error al guardar los análisis: ' . $e->getMessage())->withInput();
    }
}
    public function process(Request $request)
{
    $request->validate([
        'process_id' => 'required|exists:processes,process_id',
    ]);

    $processId = $request->input('process_id');

    // Buscar el proceso y su servicio de humedad
    $process = Process::where('process_id', $processId)->firstOrFail();

    // Obtener el servicio de humedad
    $service = Service::where('descripcion', 'like', '%humedad%')->firstOrFail();

    // Redirigir a la vista del formulario de análisis
    return view('lscefa::analyses.humidity.store', compact('process', 'service'));
}

    public function edit($id)
    {
        $analysis = HumidityAnalysis::findOrFail($id);
        return view('lscefa::humidity_analyses.edit', compact('analysis'));
    }

    public function update(Request $request, $id)
    {
        $analysis = HumidityAnalysis::findOrFail($id);

        $request->validate([
            'fecha_analisis' => 'required|date',
            'hora_ingreso_horno' => 'required',
            'hora_salida_horno' => 'required',
            'temperatura_horno' => 'required|numeric',
            'nombre_metodo' => 'required|string|max:255',
            'intervalo_metodo' => 'required|string|max:255',
            'equipo_utilizado' => 'required|string|max:255',
            'unidades_reporte_equipo' => 'required|string|max:255',
            'resolucion_instrumental' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string',
            'fecha_fin_analisis' => 'nullable|date',
            'codigo_interno' => 'nullable|regex:/^[A-Za-z0-9\-]+$/',
            'peso_capsula' => 'nullable|numeric',
            'peso_muestra' => 'nullable|numeric',
            'peso_capsula_muestra_humedad' => 'nullable|numeric',
            'peso_capsula_muestra_seca' => 'nullable|numeric',
            'porcentaje_humedad' => 'required|numeric',
            'consecutivo_no' => 'required|unique:humidity_analyses,consecutivo_no,' . $analysis->id,
             'controles_analiticos' => 'nullable|array',
            'controles_analiticos.masa_suelo' => 'nullable|numeric',
            'controles_analiticos.masa_agua' => 'nullable|numeric',
            'controles_analiticos.masa_suelo_seco' => 'nullable|numeric',
            'controles_analiticos.humedad_fortificada_teorica' => 'nullable|numeric',
            'controles_analiticos.humedad_obtenida' => 'nullable|numeric',
            'controles_analiticos.humedad_fortificada' => 'nullable|numeric',
            'controles_analiticos.recuperacion' => 'nullable|numeric',
            'controles_analiticos.valor_referencia' => 'nullable|numeric',
            'controles_analiticos.valor_obtenido' => 'nullable|numeric',
            'controles_analiticos.blanco_metodo' => 'nullable|numeric',
            'controles_analiticos.resultado' => 'nullable|string|max:255',
            'controles_analiticos.lcm' => 'nullable|string|max:255',
            'controles_analiticos.rango_metodo' => 'nullable|string',
            'controles_analiticos.humedad_replica_1' => 'nullable|numeric',
            'controles_analiticos.humedad_replica_2' => 'nullable|numeric',
            'controles_analiticos.dpr' => 'nullable|numeric',
            'controles_analiticos.identificacion_mf' => 'nullable|regex:/^[A-Za-z0-9\-]+$/',
            'controles_analiticos.identificacion_mr' => 'nullable|regex:/^[A-Za-z0-9\-]+$/',
            'controles_analiticos.identificacion_dm' => 'nullable|regex:/^[A-Za-z0-9\-]+$/',
            'controles_analiticos.identificacion_bm' => 'nullable|regex:/^[A-Za-z0-9\-]+$/',
            'controles_analiticos.aceptable' => 'nullable|string|in:aceptable,no_aceptable',
            'controles_analiticos.observaciones' => 'nullable|string',
            ],);

        $analysis->update($request->all());

        return redirect()->route('humidity_analysis.index')->with('success', 'Análisis actualizado correctamente.');
    }

    public function review(Request $request, $id)
    {
        $request->validate([
            'cumple' => 'nullable|boolean',
            'no_cumple' => 'nullable|boolean',
        ]);

        $analysis = HumidityAnalysis::findOrFail($id);

        $control = $analysis->analyticalControl ?? new AnalyticalControl(['humidity_analysis_id' => $id]);

        $control->fill($request->only(['cumple', 'no_cumple']));
        $control->save();

        return redirect()->route('humidity_analysis.index')->with('success', 'Control analítico registrado.');
    }

    public function destroy($id)
    {
        $analysis = HumidityAnalysis::findOrFail($id);
        $analysis->delete();

        return redirect()->route('humidity_analysis.index')->with('success', 'Análisis eliminado correctamente.');
    }
}
