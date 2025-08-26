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
        Log::info('Accediendo a humidityAnalysis', ['processId' => $processId]);
        
        // Obtener el proceso con sus relaciones necesarias
        $process = Process::with(['serviceProcessDetails.service'])
                        ->where('process_id', (string)$processId)
                        ->firstOrFail();
        
        Log::info('Proceso encontrado', ['process_id' => $process->process_id]);
        
        // Obtener el servicio asociado si existe
        $service = optional($process->serviceProcessDetails->first())->service;
        
        if (!$service) {
            Log::error('No se encontró servicio para el proceso', ['process_id' => $processId]);
            return back()->with('error', 'No se encontró el servicio para este proceso');
        }
        
        Log::info('Servicio encontrado', ['service_id' => $service->services_id, 'descripcion' => $service->descripcion]);
        
        // Obtener el ServiceProcessDetail para este proceso y servicio
        $serviceProcessDetail = $process->serviceProcessDetails()
            ->where('service_id', $service->services_id)
            ->first();
        
        if (!$serviceProcessDetail) {
            Log::error('No se encontró ServiceProcessDetail', [
                'process_id' => $processId,
                'service_id' => $service->services_id
            ]);
            return back()->with('error', 'No se encontró el detalle del servicio para este proceso');
        }
        
        Log::info('ServiceProcessDetail encontrado', [
            'id' => $serviceProcessDetail->id,
            'process_id' => $serviceProcessDetail->process_id,
            'service_id' => $serviceProcessDetail->service_id
        ]);

        return view('lscefa::analyses.humidity.process', compact('process', 'service', 'serviceProcessDetail') + ['user' => Auth::user()]);

    } catch (\Exception $e) {
        Log::error('Error al cargar análisis de humedad: '.$e->getMessage(), [
            'processId' => $processId,
            'exception' => $e
        ]);
        return back()->with('error', 'No se pudo cargar el análisis solicitado');
    }
}

public function storeHumidityAnalysis(Request $request)
{
    // Log para debug de los datos recibidos
    Log::info('Datos recibidos en storeHumidityAnalysis:', [
        'request_data' => $request->all(),
        'has_rows' => $request->has('rows'),
        'has_process_id' => $request->has('process_id'),
        'has_analysis_id' => $request->has('analysis_id'),
        'process_id_value' => $request->input('process_id'),
        'analysis_id_value' => $request->input('analysis_id'),
    ]);
    
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
        'analysis_id' => 'required|exists:service_process_details,id',
        
        'fecha_analisis' => 'required|date',
        'hora_ingreso_horno' => 'required',
        'hora_salida_horno' => 'required',
        'temperatura_horno' => 'required|numeric',
        'recuperacion' => 'nullable|numeric',
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
        // Obtener el proceso con sus detalles de servicio
        $process = Process::with(['serviceProcessDetails.service'])
                         ->findOrFail($validated['process_id']);
        
        // Buscar el servicio de humedad específico
        $humidityService = $process->serviceProcessDetails()
            ->where('id', $validated['analysis_id'])
            ->first();
            
        if (!$humidityService) {
            throw new \Exception("No se encontró el servicio de humedad para el proceso {$validated['process_id']} y análisis {$validated['analysis_id']}");
        }
        
        $serviceId = $humidityService->service->services_id;
        $analysisId = $validated['analysis_id']; // Usar el analysis_id validado del formulario
        
        // Log para debug
        Log::info('Datos para análisis de humedad:', [
            'process_id' => $validated['process_id'],
            'analysis_id' => $analysisId,
            'service_id' => $serviceId
        ]);

        // Generar un consecutivo base para todas las muestras
        $baseConsecutivo = 'HUM-' . date('Ymd') . '-' . str_pad(HumidityAnalysis::whereDate('created_at', today())->count() + 1, 3, '0', STR_PAD_LEFT);

        foreach ($validated['rows'] as $index => $row) {
            // Crear un consecutivo único para cada fila
            $consecutivo = $baseConsecutivo . '-' . ($index + 1);

            $humidityAnalysis = HumidityAnalysis::create([
                'process_id' => $validated['process_id'],
                'service_id' => $serviceId,
                'analysis_id' => $analysisId,
                'user_id' => auth()->id(),
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
                'review_status' => 'pending',
            ]);

            // Guardar controles analíticos solo para la primera fila (o adaptar si es necesario)
            if ($index === 0 && !empty($validated['controles_analiticos'])) {
                $controles = $validated['controles_analiticos'];
                
                AnalyticalControl::create([
                    'analysis_id' => $analysisId, // ID del ServiceProcessDetail (ya obtenido arriba)
                    'humidity_analysis_id' => $humidityAnalysis->id, // ID del análisis de humedad
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
                    'identificacion_bm' => $controles['identificacion_mr'] ?? null,
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
        $errorMessage = 'Hubo un error al guardar los análisis: ' . $e->getMessage();
        
        // Si es un error de validación, hacer el mensaje más amigable
        if (str_contains($e->getMessage(), 'Undefined array key')) {
            $errorMessage = 'Error en el formulario: Faltan datos requeridos. Por favor, verifique que todos los campos estén completos.';
        }
        
        return back()->with('error', $errorMessage)->withInput();
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

    // Obtener el ServiceProcessDetail para este proceso y servicio
    $serviceProcessDetail = $process->serviceProcessDetails()
        ->where('service_id', $service->services_id)
        ->first();
    
    if (!$serviceProcessDetail) {
        return back()->with('error', 'No se encontró el detalle del servicio para este proceso');
    }
    
    // Redirigir a la vista del formulario de análisis
    return view('lscefa::analyses.humidity.process', compact('process', 'service', 'serviceProcessDetail'));
}

   public function reviewIndex(Request $request)
{
    try {
        Log::info('Administrador accede a revisión de análisis de humedad', [
            'user_id' => Auth::id(),
            'role' => Auth::user()->role ?? 'N/A'
        ]);

        // Obtener parámetros de filtrado
        $filters = [
            'consecutivo' => $request->input('consecutivo'),
            'process_id' => $request->input('process_id'),
            'fecha_inicio' => $request->input('fecha_inicio'),
            'fecha_fin' => $request->input('fecha_fin'),
            'review_status' => $request->input('review_status', 'pending'),
            'codigo_interno' => $request->input('codigo_interno')
        ];

        // Consulta base con relaciones
        $query = HumidityAnalysis::with([
            'process', 
            'analyticalControl',
            'process.serviceProcessDetails.service'
        ]);

        // Aplicar filtros
        if (!empty($filters['consecutivo'])) {
            $query->where('consecutivo_no', 'like', '%' . $filters['consecutivo'] . '%');
        }

        if (!empty($filters['process_id'])) {
            $query->where('process_id', $filters['process_id']);
        }

        if (!empty($filters['codigo_interno'])) {
            $query->where('codigo_interno', 'like', '%' . $filters['codigo_interno'] . '%');
        }

        if (!empty($filters['fecha_inicio']) && !empty($filters['fecha_fin'])) {
            $query->whereBetween('fecha_analisis', [
                $filters['fecha_inicio'],
                $filters['fecha_fin']
            ]);
        } elseif (!empty($filters['fecha_inicio'])) {
            $query->where('fecha_analisis', '>=', $filters['fecha_inicio']);
        } elseif (!empty($filters['fecha_fin'])) {
            $query->where('fecha_analisis', '<=', $filters['fecha_fin']);
        }

        if (!empty($filters['review_status'])) {
            $query->where('review_status', $filters['review_status']);
        }

        // Ordenar y paginar
        $analyses = $query->orderBy('created_at', 'desc')
                         ->paginate(20)
                         ->appends($filters);

        // Obtener procesos únicos para el dropdown de filtro
        $processes = Process::whereHas('humidityAnalyses')
                           ->orderBy('reception_date', 'desc')
                           ->get();

        return view('lscefa::analyses.humidity.review-index', [
            'analyses' => $analyses,
            'filters' => $filters,
            'processes' => $processes,
            'reviewStatuses' => [
                'pending' => 'Pendiente',
                'approved' => 'Aprobado',
                'rejected' => 'Rechazado',
                'in_review' => 'En Revisión'
            ]
        ]);

    } catch (\Exception $e) {
        Log::error('Error en revisión de análisis de humedad: '.$e->getMessage());
        return back()->with('error', 'Error al cargar la revisión: '.$e->getMessage());
    }
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
            ],);

        $analysis->update($request->all());

        return redirect()->route('humidity_analysis.index')->with('success', 'Análisis actualizado correctamente.');
    }
    public function reviewShow($id)
{
    try {
        $analysis = HumidityAnalysis::with([
            'process',
            'analyticalControl',
            'reviewer:id,name,email',
            'process.serviceProcessDetails.service'
        ])->findOrFail($id);

        return view('lscefa::analyses.humidity.review-show', compact('analysis'));

    } catch (\Exception $e) {
        Log::error('Error al cargar detalle de revisión: '.$e->getMessage());
        return back()->with('error', 'No se pudo cargar el análisis: '.$e->getMessage());
    }
}

    public function updateReviewStatus(Request $request, $id)
{
    try {
        $request->validate([
            'review_status' => 'required|in:pending,approved,rejected,in_review',
            'review_notes' => 'nullable|string|max:1000'
        ]);

        $analysis = HumidityAnalysis::findOrFail($id);
        
        $analysis->update([
            'review_status' => $request->review_status,
            'review_notes' => $request->review_notes,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now()
        ]);

        Log::info('Estado de revisión actualizado', [
            'analysis_id' => $id,
            'new_status' => $request->review_status,
            'reviewer_id' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Estado de revisión actualizado correctamente'
        ]);

    } catch (\Exception $e) {
        Log::error('Error al actualizar estado de revisión: '.$e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error al actualizar: '.$e->getMessage()
        ], 500);
    }
}
    public function destroy($id)
    {
        $analysis = HumidityAnalysis::findOrFail($id);
        $analysis->delete();

        return redirect()->route('humidity_analysis.index')->with('success', 'Análisis eliminado correctamente.');
    }

    /**
     * Descarga el informe de análisis de humedad en formato Excel
     */
    public function downloadHumidityReport($analysisId)
    {
        $humidityAnalysis = HumidityAnalysis::findOrFail($analysisId);
        
        // Crear el archivo Excel usando PhpSpreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Configurar encabezados del informe
        $sheet->setCellValue('A1', 'LABORATORIO DE CIENCIAS BÁSICAS');
        $sheet->setCellValue('A2', 'PROCEDIMIENTO DETERMINACIÓN DE HUMEDAD EN SUELOS');
        $sheet->setCellValue('A3', 'FORMATO REPORTE RESULTADOS HUMEDAD EN SUELOS');
        $sheet->setCellValue('D3', 'Versión: 1');
        $sheet->setCellValue('D4', 'Código: F-HSS-001');
        $sheet->setCellValue('D5', 'Página: 1 de 1');

        // Información general del análisis
        $sheet->setCellValue('A6', 'Consecutivo No.:');
        $sheet->setCellValue('B6', $humidityAnalysis->consecutivo_no);
        $sheet->setCellValue('A7', 'Fecha del análisis:');
        $sheet->setCellValue('B7', $humidityAnalysis->fecha_analisis);
        $sheet->setCellValue('A8', 'Nombre Analista:');
        $sheet->setCellValue('B8', optional($humidityAnalysis->user)->name ?? 'N/A');
        $sheet->setCellValue('A9', 'Metodología Utilizada:');
        $sheet->setCellValue('B9', $humidityAnalysis->nombre_metodo ?? 'N/A');
        $sheet->setCellValue('A10', 'Intervalo del método:');
        $sheet->setCellValue('B10', $humidityAnalysis->intervalo_metodo ?? 'N/A');
        $sheet->setCellValue('A11', 'Equipo utilizado:');
        $sheet->setCellValue('B11', $humidityAnalysis->equipo_utilizado ?? 'N/A');

        // Condiciones del horno
        $sheet->setCellValue('A13', 'Condiciones del horno');
        $sheet->setCellValue('A14', 'Hora de ingreso:');
        $sheet->setCellValue('B14', $humidityAnalysis->hora_ingreso_horno ?? 'N/A');
        $sheet->setCellValue('A15', 'Hora de salida:');
        $sheet->setCellValue('B15', $humidityAnalysis->hora_salida_horno ?? 'N/A');
        $sheet->setCellValue('A16', 'Temperatura del horno (°C):');
        $sheet->setCellValue('B16', $humidityAnalysis->temperatura_horno ?? 'N/A');

        // Resultados del análisis
        $sheet->setCellValue('A18', 'Resultados del análisis');
        $sheet->setCellValue('A19', 'Identificación');
        $sheet->setCellValue('B19', 'Peso cápsula (g)');
        $sheet->setCellValue('C19', 'Peso muestra (g)');
        $sheet->setCellValue('D19', 'Peso cápsula + muestra húmeda (g)');
        $sheet->setCellValue('E19', 'Peso cápsula + muestra seca (g)');
        $sheet->setCellValue('F19', 'Porcentaje de humedad (%)');
        $sheet->setCellValue('G19', 'Observaciones');

        $row = 20;
        $sheet->setCellValue('A' . $row, $humidityAnalysis->codigo_interno ?? 'N/A');
        $sheet->setCellValue('B' . $row, $humidityAnalysis->peso_capsula ?? 'N/A');
        $sheet->setCellValue('C' . $row, $humidityAnalysis->peso_muestra ?? 'N/A');
        $sheet->setCellValue('D' . $row, $humidityAnalysis->peso_capsula_muestra_humedad ?? 'N/A');
        $sheet->setCellValue('E' . $row, $humidityAnalysis->peso_capsula_muestra_seca ?? 'N/A');
        $sheet->setCellValue('F' . $row, $humidityAnalysis->porcentaje_humedad ?? 'N/A');
        $sheet->setCellValue('G' . $row, $humidityAnalysis->observaciones ?? '');

        // Ajustar ancho de columnas
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setWidth(25);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(30);

        // Crear el archivo Excel
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        // Generar nombre del archivo
        $filename = 'Reporte_Humedad_' . $humidityAnalysis->consecutivo_no . '_' . date('Y-m-d') . '.xlsx';
        
        // Configurar headers para descarga
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        // Enviar el archivo al navegador
        $writer->save('php://output');
        exit;
    }
}
