<?php

namespace Modules\LSCEFA\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Modules\LSCEFA\Models\Process;
use Modules\LSCEFA\Models\Service;
use Modules\LSCEFA\Models\ServiceProcessDetail;
use Modules\LSCEFA\Models\PhAnalysis;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

use Illuminate\Routing\Controller;

class PhAnalysisController extends Controller
{
    /**
     * Display a listing of pH analyses for technical staff, including pending and returned (not approved) analyses.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            Log::info('User accessing PhAnalysisController@index:', [
                'user_id' => Auth::id(),
                'user_role' => Auth::user()->role ?? 'N/A',
            ]);

            // 1. Obtener servicios que contienen 'ph' en la descripción
            $phServices = \Modules\LSCEFA\Models\Service::whereRaw('LOWER(descripcion) LIKE ?', ['%ph%'])
                ->get(['services_id', 'descripcion']);
                
            Log::info('Servicios con "ph" en la descripción:', [
                'count' => $phServices->count(),
                'services' => $phServices->toArray()
            ]);

            // 2. Obtener análisis rechazados
            $rejectedAnalyses = \Modules\LSCEFA\Models\ServiceProcessDetail::with(['process', 'service'])
                ->where('status', 'rejected')
                ->whereHas('service', function($q) {
                    $q->whereRaw('LOWER(descripcion) LIKE ?', ['%ph%']);
                })
                ->get();

            Log::info('Análisis de pH rechazados:', [
                'count' => $rejectedAnalyses->count()
            ]);

            // 3. Obtener procesos con análisis de pH pendientes
            $processes = \Modules\LSCEFA\Models\Process::where('status', 'pending')
                ->whereHas('serviceProcessDetails', function($query) {
                    $query->where('status', 'pending')
                          ->whereHas('service', function($q) {
                              $q->whereRaw('LOWER(descripcion) LIKE ?', ['%ph%']);
                          });
                })
                ->with(['serviceProcessDetails' => function($query) {
                    $query->where('status', 'pending')
                          ->whereHas('service', function($q) {
                              $q->whereRaw('LOWER(descripcion) LIKE ?', ['%ph%']);
                          })
                          ->with('service');
                }])
                ->get();

            Log::info('Procesos con análisis de pH pendientes:', [
                'count' => $processes->count()
            ]);

            return view('lscefa::ph_analyses.index', [
                'processes' => $processes,
                'phAnalyses' => $rejectedAnalyses
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in PhAnalysisController@index: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('lscefa.technical.analyses.index')
                           ->with('error', 'Error al cargar la gestión de análisis de pH: ' . $e->getMessage());
        }
    }

    /**
     * Recibe los IDs seleccionados, los guarda en sesión y redirige a processAll
     */
    public function batchPhAnalysis(Request $request)
    {
        $request->validate([
            'analyses' => 'required|array|max:20',
            'analyses.*' => 'exists:service_process_details,id',
        ]);
        session(['batch_ph_analysis_ids' => $request->input('analyses')]);
        return redirect()->route('lscefa.ph_analysis.process_all');
    }

    /**
     * Show the form for processing all pending pH analyses across all processes.
     *
     * @return \Illuminate\View\View
     */
    public function processAll()
    {
        try {
            // Si hay IDs en sesión, solo procesar esos análisis
            $selectedIds = session('batch_ph_analysis_ids', []);
            if (!empty($selectedIds)) {
                $analyses = \Modules\LSCEFA\Models\ServiceProcessDetail::with(['process', 'service', 'phAnalysis'])
                    ->whereIn('id', $selectedIds)
                    ->get();
                // Agrupar por proceso
                $processes = $analyses->groupBy('process_id')->map(function($items, $processId) {
                    $process = \Modules\LSCEFA\Models\Process::where('process_id', $processId)
                        ->with(['serviceProcessDetails' => function($query) use ($items) {
                            $query->whereIn('id', $items->pluck('id')->toArray());
                        }])
                        ->first();
                    return $process;
                })->filter();
            } else {
                // Lógica para todos los procesos con análisis de pH pendientes (case-insensitive match)
                $processes = Process::where('status', 'pending')
                    ->with([
                        'serviceProcessDetails' => function ($query) {
                            $query->where('status', 'pending')
                                ->whereHas('service', function($q) {
                                    $q->whereRaw('LOWER(descripcion) LIKE ?', ['%ph%']);
                                })
                                ->with(['service', 'process', 'phAnalysis']);
                        },
                    ])
                    ->get()
                    ->filter(function ($process) {
                        return $process->serviceProcessDetails->isNotEmpty();
                    });
            }
            if ($processes->isEmpty()) {
                return redirect()->route('lscefa.ph_analysis.index')
                                ->with('error', 'No hay análisis de pH pendientes para procesar.');
            }

            // Initialize an array to hold all pending items across all analyses
            $pendingItems = [];
            $pendingAnalyses = collect();
            foreach ($processes as $process) {
                $analyses = $process->serviceProcessDetails;
                foreach ($analyses as $analysis) {
                    $pendingAnalyses->push($analysis);
                    $phAnalysis = $analysis->phAnalysis;
                    if ($phAnalysis && isset($phAnalysis->items_ensayo)) {
                        foreach ($phAnalysis->items_ensayo as $index => $item) {
                            if (!isset($item['valor_leido']) || $item['valor_leido'] === '') {
                                $pendingItems[] = $item + ['analysis_id' => $analysis->id];
                            }
                        }
                    } else {
                        // Add default item if no PhAnalysis exists
                        $pendingItems[] = [
                            'identificacion' => 'Muestra ' . (count($pendingItems) + 1),
                            'peso' => '',
                            'volumen_agua' => '',
                            'temperatura' => '',
                            'valor_leido' => '',
                            'observaciones' => '',
                            'analysis_id' => $analysis->id,
                        ];
                    }
                }
            }

            // Debug: verificar si la muestra de referencia quedó en controles_analiticos
            try {
                $hasRef = false;
                foreach ($controles as $ctrl) {
                    if (is_array($ctrl) && ($ctrl['tipo'] ?? '') === 'muestra_referencia') {
                        $hasRef = true;
                        break;
                    }
                }
                \Log::info('storePhAnalysis - resumen de controles_analiticos', [
                    'total_controles' => count($controles),
                    'incluye_muestra_referencia' => $hasRef ? 'sí' : 'no',
                    'index_3_tipo' => isset($controles[3]['tipo']) ? $controles[3]['tipo'] : null,
                ]);
            } catch (\Throwable $e) {
                \Log::warning('storePhAnalysis - no se pudo registrar log de controles: ' . $e->getMessage());
            }

            Log::info('PhAnalysisController@processAll loaded data:', [
                'pending_analyses_count' => $pendingAnalyses->count(),
                'pending_items_count' => count($pendingItems),
            ]);

            return view('lscefa::ph_analyses.process', [
                'pendingAnalyses' => $pendingAnalyses,
                'pendingItems' => $pendingItems,
                'user' => Auth::user(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error in PhAnalysisController@processAll: ' . $e->getMessage(), [
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('lscefa.ph_analysis.index')
                           ->with('error', 'Error al cargar el formulario de análisis de pH: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for processing a specific pH analysis with pending items.
     *
     * @param string $processId
     * @param int $serviceId
     * @return \Illuminate\View\View
     */
    public function phAnalysis($processId, $serviceId)
    {
        try {
            // Obtener el detalle del proceso de servicio que tenga un servicio con 'ph' en la descripción
            $serviceProcessDetail = ServiceProcessDetail::with(['process', 'service'])
                ->where('process_id', $processId)
                ->whereHas('service', function($q) use ($serviceId) {
                    $q->where('service_id', $serviceId)
                      ->whereRaw('LOWER(descripcion) LIKE ?', ['%ph%']);
                })
                ->firstOrFail();
            $phAnalysis = PhAnalysis::where('analysis_id', $serviceProcessDetail->id)->first();

            // Filtrar ítems pendientes (asumimos que los ítems están en items_ensayo y un ítem está pendiente si no tiene valor_leido)
            $pendingItems = [];
            if ($phAnalysis && isset($phAnalysis->items_ensayo)) {
                foreach ($phAnalysis->items_ensayo as $index => $item) {
                    if (!isset($item['valor_leido']) || $item['valor_leido'] === '') {
                        $pendingItems[$index] = $item;
                    }
                }
            }

            Log::info('PhAnalysisController@phAnalysis loaded data:', [
                'process_id' => $process->id ?? null,
                'process_process_id' => $process->process_id,
                'service_services_id' => $service->services_id,
                'analysis_id' => $analysis->id,
                'phAnalysis_exists' => !is_null($phAnalysis),
                'pending_items_count' => count($pendingItems),
            ]);

            return view('lscefa::ph_analyses.process', [
                'process' => $process,
                'service' => $service,
                'analysis' => $analysis,
                'phAnalysis' => $phAnalysis,
                'pendingItems' => $pendingItems,
                'user' => Auth::user(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error in PhAnalysisController@phAnalysis: ' . $e->getMessage(), [
                'processId' => $processId,
                'serviceId' => $serviceId,
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('lscefa.ph_analysis.index')
                           ->with('error', 'Error al cargar el formulario de análisis de pH: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created pH analysis in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storePhAnalysis(Request $request)
    {
        $request->validate([
            'consecutivo_no' => 'required|string|max:255',
            'fecha_analisis' => 'required|date',
            'codigo_probeta' => 'required|string|max:255',
            'codigo_equipo' => 'required|string|max:255',
            'serial_electrodo' => 'required|string|max:255',
            'serial_sonda_temperatura' => 'required|string|max:255',
            'controles_analiticos' => 'required|array|min:1',
            'controles_analiticos.*.lote' => 'nullable|string',
            'controles_analiticos.*.valor_leido' => ['nullable','regex:/^-?\d+(\.\d{1,4})?$/'],
            'controles_analiticos.*.valor_esperado' => ['nullable','regex:/^-?\d+(\.\d{1,4})?$/'],
            'controles_analiticos.*.observaciones' => 'nullable|string',
            'precision_analitica' => 'required|array',
            'precision_analitica.duplicado_a.valor_leido' => ['required','regex:/^-?\d+(\.\d{1,5})?$/'],
            'precision_analitica.duplicado_b.valor_leido' => ['required','regex:/^-?\d+(\.\d{1,5})?$/'],
            'items_ensayo' => 'required|array|min:1',
            'items_ensayo.*.identificacion' => 'required|string',
            'items_ensayo.*.peso' => ['required','regex:/^-?\d+(\.\d{1,5})?$/'],
            'items_ensayo.*.volumen_agua' => ['required','regex:/^-?\d+(\.\d{1,5})?$/'],
            'items_ensayo.*.temperatura' => ['required','regex:/^-?\d+(\.\d{1,5})?$/'],
            'items_ensayo.*.valor_leido' => ['required','regex:/^-?\d+(\.\d{1,5})?$/'],
            'observaciones' => 'nullable|string',
        ]);

        // Validar que al menos un control tenga valor_leido y valor_esperado
        $controles = $request->input('controles_analiticos', []);
        $controlesCompletos = collect($controles)->filter(function($control) {
            return !empty($control['valor_leido']) && !empty($control['valor_esperado']);
        });
        if ($controlesCompletos->isEmpty()) {
            return back()->withErrors(['controles_analiticos' => 'Debe completar al menos un control analítico con valor leído y valor esperado.'])->withInput();
        }

        // Filtrar controles vacíos antes de guardar
        $controles = $controlesCompletos->values()->all();

        // Incorporar la muestra de referencia dentro de controles_analiticos (para Veracidad)
        $muestraRefInput = $request->input('muestra_referencia', []);
        if (is_array($muestraRefInput) && !empty($muestraRefInput)) {
            // Normalizar campos y calcular métricas si es posible
            $valorLeidoRef = isset($muestraRefInput['valor_leido']) && $muestraRefInput['valor_leido'] !== '' ? (float) $muestraRefInput['valor_leido'] : null;
            $valorEsperadoRef = isset($muestraRefInput['valor_esperado']) && $muestraRefInput['valor_esperado'] !== '' ? (float) $muestraRefInput['valor_esperado'] : null;

            $errorRef = null;
            $aceptabilidadRef = null;
            if ($valorLeidoRef !== null && $valorEsperadoRef !== null && $valorEsperadoRef != 0) {
                $errorRef = abs(($valorLeidoRef - $valorEsperadoRef) / $valorEsperadoRef) * 100;
                $aceptabilidadRef = ($errorRef >= 0 && $errorRef <= 20) ? 'Aceptable' : 'No aceptable';
            }

            $muestraRef = [
                'tipo' => 'muestra_referencia',
                'identificacion' => $muestraRefInput['identificacion'] ?? 'Muestra de referencia o MRC',
                'lote' => $muestraRefInput['lote'] ?? null,
                'peso' => $muestraRefInput['peso'] ?? null,
                'volumen_agua' => $muestraRefInput['volumen_agua'] ?? null,
                'temperatura' => $muestraRefInput['temperatura'] ?? null,
                'valor_leido' => $muestraRefInput['valor_leido'] ?? null,
                'valor_esperado' => $muestraRefInput['valor_esperado'] ?? null,
                'error' => $errorRef,
                'aceptabilidad' => $aceptabilidadRef,
                'observaciones' => $muestraRefInput['observaciones'] ?? null,
            ];

            // Insertar en el índice 3 para mantener compatibilidad con vistas existentes
            $insertIndex = 3;
            if ($insertIndex >= 0) {
                array_splice($controles, $insertIndex, 0, [$muestraRef]);
            } else {
                $controles[] = $muestraRef;
            }

            // Log de verificación de inserción
            Log::info('storePhAnalysis - muestra_referencia preparada e insertada en controles_analiticos', [
                'insert_index' => $insertIndex,
                'muestra_referencia' => $muestraRef,
                'total_controles_post_insert' => count($controles),
            ]);
        }

        try {
            DB::beginTransaction();

            // Procesar controles analíticos
            foreach ($controles as &$control) {
                if (!empty($control['valor_leido']) && !empty($control['valor_esperado'])) {
                    $valorLeido = floatval($control['valor_leido']);
                    $valorEsperado = floatval($control['valor_esperado']);
                    $control['error'] = ($valorEsperado != 0) ? abs(($valorLeido - $valorEsperado) / $valorEsperado) * 100 : 0;
                    $control['aceptabilidad'] = ($control['error'] >= 0 && $control['error'] <= 20) ? 'Aceptable' : 'No aceptable';
                }
            }

            // Log resumen de controles antes de guardar
            try {
                $hasRef = false;
                $refIdx = null;
                foreach ($controles as $idx => $ctrl) {
                    if (is_array($ctrl) && (($ctrl['tipo'] ?? null) === 'muestra_referencia')) {
                        $hasRef = true;
                        $refIdx = $idx;
                        break;
                    }
                }
                Log::info('storePhAnalysis - resumen antes de persistir', [
                    'controles_count' => count($controles),
                    'has_muestra_referencia' => $hasRef ? 'sí' : 'no',
                    'muestra_referencia_index' => $refIdx,
                ]);
            } catch (\Throwable $e) {
                Log::warning('storePhAnalysis - error al generar resumen de controles: ' . $e->getMessage());
            }

            // Procesar precisión analítica
            $precision = $request->precision_analitica;
            $duplicadoA = floatval($precision['duplicado_a']['valor_leido']);
            $duplicadoB = floatval($precision['duplicado_b']['valor_leido']);
            $precision['promedio'] = ($duplicadoA + $duplicadoB) / 2;
            $precision['diferencia'] = abs($duplicadoA - $duplicadoB);
            $limite = 0.15;
            if ($precision['promedio'] > 7.00 && $precision['promedio'] < 7.5) {
                $limite = 0.20;
            } elseif ($precision['promedio'] >= 7.5 && $precision['promedio'] <= 8.00) {
                $limite = 0.30;
            } elseif ($precision['promedio'] > 8.00) {
                $limite = 0.40;
            }
            $precision['aceptabilidad'] = ($precision['diferencia'] <= $limite) ? 'Aceptable' : 'No aceptable';

            // Procesar ítems de ensayo
            $itemsEnsayo = $request->items_ensayo;
            $analyses = $request->input('analyses', []);

            foreach ($analyses as $analysisData) {
                $analysisId = $analysisData['analysis_id'];
                $analysis = ServiceProcessDetail::findOrFail($analysisId);

                // Filtrar los ítems de ensayo que corresponden a este análisis
                $analysisItems = array_filter($itemsEnsayo, function ($item) use ($analysisId) {
                    return $item['analysis_id'] == $analysisId;
                });

                // Reindexar los ítems para evitar problemas con claves
                $analysisItems = array_values($analysisItems);

                // Crear o actualizar el PhAnalysis
                $phAnalysis = PhAnalysis::updateOrCreate(
                    ['analysis_id' => $analysisId],
                    [
                        'consecutivo_no' => $request->consecutivo_no,
                        'fecha_analisis' => $request->fecha_analisis,
                        'user_id' => Auth::id(),
                        'codigo_probeta' => $request->codigo_probeta,
                        'codigo_equipo' => $request->codigo_equipo,
                        'serial_electrodo' => $request->serial_electrodo,
                        'serial_sonda_temperatura' => $request->serial_sonda_temperatura,
                        'controles_analiticos' => $controles,
                        'precision_analitica' => $precision,
                        'items_ensayo' => $analysisItems,
                        'observaciones' => $request->observaciones,
                        'review_status' => 'pending',
                    ]
                );

                // Actualizar el estado del análisis
                $analysis->status = 'completed';
                $analysis->save();
            }

            DB::commit();

            // Log post-guardado
            Log::info('storePhAnalysis - análisis de pH guardados OK', [
                'analyses_saved' => count($analyses),
            ]);

            return redirect()->route('lscefa.technical.analyses.index')
                            ->with('success', 'Análisis de pH guardados exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in PhAnalysisController@storePhAnalysis: ' . $e->getMessage(), [
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Error al guardar los análisis de pH: ' . $e->getMessage())->withInput();
        }
    }

    public function downloadPhReport($analysisId)
    {
        $phAnalysis = PhAnalysis::where('analysis_id', $analysisId)->firstOrFail();
        $analyst = \App\Models\User::find($phAnalysis->user_id);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'LABORATORIO DE CIENCIAS BÁSICAS');
        $sheet->setCellValue('A2', 'PROCEDIMIENTO DETERMINACIÓN DE pH EN SUELOS');
        $sheet->setCellValue('A3', 'FORMATO REPORTE RESULTADOS pH EN SUELOS');
        $sheet->setCellValue('D3', 'Versión: 6');
        $sheet->setCellValue('D4', 'Código: F-PSS-001');
        $sheet->setCellValue('D5', 'Página: 1 de 1');

        $sheet->setCellValue('A6', 'Consecutivo No.:');
        $sheet->setCellValue('B6', $phAnalysis->consecutivo_no);
        $sheet->setCellValue('A7', 'Fecha del análisis:');
        $sheet->setCellValue('B7', $phAnalysis->fecha_analisis);
        $sheet->setCellValue('A8', 'Nombre Analista:');
        $sheet->setCellValue('B8', $analyst->name);
        $sheet->setCellValue('A9', 'Código de la probeta:');
        $sheet->setCellValue('B9', $phAnalysis->codigo_probeta);
        $sheet->setCellValue('A10', 'Código Equipo potenciométrico:');
        $sheet->setCellValue('B10', $phAnalysis->codigo_equipo);
        $sheet->setCellValue('A11', 'Serial del electrodo:');
        $sheet->setCellValue('B11', $phAnalysis->serial_electrodo);
        $sheet->setCellValue('A12', 'Serial sonda de temperatura:');
        $sheet->setCellValue('B12', $phAnalysis->serial_sonda_temperatura);

        $sheet->setCellValue('A14', 'Controles analíticos');
        $sheet->setCellValue('A15', 'Identificación');
        $sheet->setCellValue('B15', 'Lote de identificación');
        $sheet->setCellValue('C15', 'Valor leído (Unidades de pH)');
        $sheet->setCellValue('D15', 'Valor esperado (Unidades de pH)');
        $sheet->setCellValue('E15', '% Error');
        $sheet->setCellValue('F15', 'Aceptabilidad del control');
        $sheet->setCellValue('G15', 'Observaciones');

        $row = 16;
        foreach ($phAnalysis->controles_analiticos as $control) {
            $sheet->setCellValue('A' . $row, $control['identificacion']);
            $sheet->setCellValue('B' . $row, $control['lote']);
            $sheet->setCellValue('C' . $row, $control['valor_leido']);
            $sheet->setCellValue('D' . $row, $control['valor_esperado']);
            $sheet->setCellValue('E' . $row, $control['error']);
            $sheet->setCellValue('F' . $row, $control['aceptabilidad']);
            $sheet->setCellValue('G' . $row, $control['observaciones']);
            $row++;
        }

        $sheet->setCellValue('A' . ($row + 1), 'Precisión analítica');
        $sheet->setCellValue('A' . ($row + 2), 'Identificación');
        $sheet->setCellValue('B' . ($row + 2), 'Valor leído (Unidades de pH)');
        $sheet->setCellValue('C' . ($row + 2), 'Promedio');
        $sheet->setCellValue('D' . ($row + 2), 'Diferencia');
        $sheet->setCellValue('E' . ($row + 2), 'Aceptabilidad');
        $sheet->setCellValue('F' . ($row + 2), 'Observaciones');

        $row += 3;
        $sheet->setCellValue('A' . $row, $phAnalysis->precision_analitica['duplicado_a']['identificacion']);
        $sheet->setCellValue('B' . $row, $phAnalysis->precision_analitica['duplicado_a']['valor_leido']);
        $sheet->setCellValue('C' . $row, $phAnalysis->precision_analitica['promedio']);
        $sheet->setCellValue('D' . $row, $phAnalysis->precision_analitica['diferencia']);
        $sheet->setCellValue('E' . $row, $phAnalysis->precision_analitica['aceptabilidad']);
        $sheet->setCellValue('F' . $row, $phAnalysis->precision_analitica['duplicado_a']['observaciones']);

        $row++;
        $sheet->setCellValue('A' . $row, $phAnalysis->precision_analitica['duplicado_b']['identificacion']);
        $sheet->setCellValue('B' . $row, $phAnalysis->precision_analitica['duplicado_b']['valor_leido']);
        $sheet->setCellValue('C' . $row, '');
        $sheet->setCellValue('D' . $row, '');
        $sheet->setCellValue('E' . $row, '');
        $sheet->setCellValue('F' . $row, $phAnalysis->precision_analitica['duplicado_b']['observaciones']);

        $row += 2;
        $sheet->setCellValue('A' . $row, 'Ítems de ensayo');
        $sheet->setCellValue('A' . ($row + 1), 'Identificación');
        $sheet->setCellValue('B' . ($row + 1), 'Peso (g)');
        $sheet->setCellValue('C' . ($row + 1), 'Volumen H₂O (mL)');
        $sheet->setCellValue('D' . ($row + 1), 'Temperatura (°C)');
        $sheet->setCellValue('E' . ($row + 1), 'Valor leído (Unidades de pH)');
        $sheet->setCellValue('F' . ($row + 1), 'Observaciones');

        $row += 2;
        foreach ($phAnalysis->items_ensayo as $item) {
            $sheet->setCellValue('A' . $row, $item['identificacion']);
            $sheet->setCellValue('B' . $row, $item['peso']);
            $sheet->setCellValue('C' . $row, $item['volumen_agua']);
            $sheet->setCellValue('D' . $row, $item['temperatura']);
            $sheet->setCellValue('E' . $row, $item['valor_leido']);
            $sheet->setCellValue('F' . $row, $item['observaciones']);
            $row++;
        }

        $sheet->setCellValue('A' . ($row + 1), 'Observaciones:');
        $sheet->setCellValue('A' . ($row + 2), $phAnalysis->observaciones);

        // Auto-size columns
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'reporte_ph_' . $phAnalysis->consecutivo_no . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
} 