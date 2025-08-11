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

        // 🔍 Buscar el ID del servicio de HUMEDAD por su descripción
        $humidityService = Service::where('descripcion', 'like', '%humedad%')->first();

        if (!$humidityService) {
            return back()->with('error', 'No se encontró el servicio de humedad en la base de datos.');
        }

        // 🔍 Procesos que tienen el servicio de humedad PENDIENTE
        $processes = Process::whereHas('serviceProcessDetails', function ($query) use ($humidityService) {
                $query->where('service_id', $humidityService->services_id)
                      ->where('status', 'pending');
            })
            ->with('analyses') // Relación con HumidityAnalysis
            ->get();

        // 🔍 Análisis de humedad relacionados a procesos con servicio pendiente de humedad
        $humidityAnalyses = HumidityAnalysis::with('process.serviceProcessDetails')
            ->whereHas('process.serviceProcessDetails', function ($query) use ($humidityService) {
                $query->where('service_id', $humidityService->services_id)
                      ->where('status', 'pending');
            })
            ->get();

        return view('lscefa::analyses.humidity.index', compact('humidityAnalyses', 'processes'));

    } catch (\Exception $e) {
        Log::error('Error al cargar el índice de análisis de humedad: ' . $e->getMessage());
        return back()->with('error', 'No se pudo cargar el listado.');
    }
}

    public function humidityAnalysis($processId, $serviceId)
    {
        $process = Process::where('process_id', (string)$processId)->firstOrFail();
        $service = Service::findOrFail($serviceId);

        return view('lscefa::analyses.humidity.process', compact('process', 'service'));
    }

    public function storeHumidityAnalysis(Request $request)
    {
            $numericFields = [
            'peso_capsula',
            'peso_muestra',
            'peso_capsula_muestra_humedad',
            'peso_capsula_muestra_seca',
            'porcentaje_humedad',
        ];

        foreach ($numericFields as $field) {
            if ($request->has($field)) {
                $value = str_replace(',', '.', $request->input($field));
                $request->merge([$field => $value]);
            }
        }
         $processId = $request->input('process_id');
        $serviceId = $request->input('service_id');

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
            'fecha_fin_analisis' => 'nullable|date',
            'codigo_interno' => 'nullable|regex:/^[A-Za-z0-9\-]+$/',
            'peso_capsula' => 'required|numeric',
            'peso_muestra' => 'required|numeric',
            'peso_capsula_muestra_humedad' => 'required|numeric',
            'peso_capsula_muestra_seca' => 'required|numeric',
            'porcentaje_humedad' => 'required|numeric',
            'observaciones' => 'nullable|string',
            'consecutivo_no' => 'required|unique:humidity_analyses,consecutivo_no',
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
            
        ]);

        DB::beginTransaction();

        try {
            $humidityAnalysis = HumidityAnalysis::create([
                'process_id' => (string)$processId, 
                'consecutivo_no' => $request->consecutivo_no,
                'fecha_analisis' => $request->fecha_analisis,
                'hora_ingreso_horno' => $request->hora_ingreso_horno,
                'hora_salida_horno' => $request->hora_salida_horno,
                'temperatura_horno' => $request->temperatura_horno,
                'nombre_metodo' => $request->nombre_metodo,
                'intervalo_metodo' => $request->intervalo_metodo,
                'equipo_utilizado' => $request->equipo_utilizado,
                'unidades_reporte_equipo' => $request->unidades_reporte_equipo,
                'resolucion_instrumental' => $request->resolucion_instrumental,
                'fecha_fin_analisis' => $request->fecha_fin_analisis,
                'codigo_interno' => $request->codigo_interno,
                'peso_capsula' => $request->peso_capsula,
                'peso_muestra' => $request->peso_muestra,
                'peso_capsula_muestra_humedad' => $request->peso_capsula_muestra_humedad,
                'peso_capsula_muestra_seca' => $request->peso_capsula_muestra_seca,
                'porcentaje_humedad' => $request->porcentaje_humedad,
                'observaciones' => $request->observaciones,
            ]);

          $validated = $request->only(['controles_analiticos', 'cumple', 'no_cumple']);

$analyticalData = [
    'humidity_analysis_id' => $humidityAnalysis->id,
    'process_id' => $processId,
    'cumple' => $request->cumple,
    'no_cumple' => $request->no_cumple,
];

if (!empty($validated['controles_analiticos'])) {
    $controles = $validated['controles_analiticos'];

    $analyticalData += [
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
        'limite_cuantificacion_metodo' => $controles['lcm'] ?? null,
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
    ];
}

AnalyticalControl::create($analyticalData);

            DB::commit();

            return redirect()->route('lscefa.technical.analyses.humidity.index')->with('success', 'Análisis de humedad registrado correctamente.');
        } catch (\Exception $e) {
    Log::error('Error al guardar análisis: '.$e->getMessage(), [
        'trace' => $e->getTraceAsString()
    ]);
    return back()->with('error', 'Hubo un error al guardar el análisis.');
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
