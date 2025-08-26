<?php

namespace Modules\LSCEFA\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Modules\LSCEFA\Models\Process;
use Modules\LSCEFA\Models\ServiceProcessDetail;
use Modules\LSCEFA\Models\Service;
use Modules\LSCEFA\Models\PhAnalysis;
use Modules\LSCEFA\Entities\PhosphorusAnalysis;
use Modules\LSCEFA\Entities\BatchTextureAnalysis;
use Modules\LSCEFA\Entities\HumidityAnalysis;

class ReportsController extends Controller
{
    /**
     * Listado de "Procesos en realización" con filtro por código de ítem.
     */
    public function index(Request $request)
    {
        $itemFilter = trim((string) $request->query('item', ''));

        $query = Process::with([
                'serviceProcessDetails' => function ($q) {
                    $q->with('service');
                },
                'quote',
                'quote.customer',
                'customer',
            ])
            ->where(function($q) {
                $q->whereIn('status', ['pending', 'processing', 'in_progress'])
                  ->orWhereHas('serviceProcessDetails', function($subQ) {
                      $subQ->where('status', 'approved')
                           ->whereHas('service', function($serviceQ) {
                               $serviceQ->where(function($serviceSubQ) {
                                   $serviceSubQ->whereRaw('LOWER(descripcion) LIKE ?', ['%textura%'])
                                           ->orWhereRaw('LOWER(descripcion) LIKE ?', ['%texture%']);
                               });
                           });
                  });

                // Solo agregar condición de boro si la columna review_status existe
                if (Schema::hasColumn('boron_analysis_details', 'review_status')) {
                    $q->orWhereExists(function($existsQ) {
                        $existsQ->select(DB::raw(1))
                            ->from('boron_analysis_details')
                            ->whereColumn('boron_analysis_details.process_id', 'processes.process_id')
                            ->where('boron_analysis_details.review_status', 'approved');
                    });
                }
            });

        if ($itemFilter !== '') {
            $query->where('item_code', 'LIKE', "%{$itemFilter}%");
        }

        $processes = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        return view('lscefa::reports.index', [
            'processes' => $processes,
            'itemFilter' => $itemFilter,
        ]);
    }

    /**
     * Vista previa del informe para un proceso.
     */
    public function show($processId)
    {
        $process = Process::with([
            'serviceProcessDetails' => function ($q) {
                $q->with(['service', 'phAnalysis', 'conductivityAnalysis', 'humidityAnalysis']);
            },
            'quote',
            'customer',
        ])->findOrFail($processId);

        // Construir filas de RESULTADOS a partir de servicios asignados
        $rows = [];
        foreach ($process->serviceProcessDetails as $spd) {
            $serviceName = strtolower($spd->service->descripcion ?? '');
            // Normalizar tildes para coincidencia simple
            $serviceNameNorm = str_replace(['á','é','í','ó','ú'], ['a','e','i','o','u'], $serviceName);
            // Extraer resultado desde ServiceProcessDetail->result
            $resultadoDisplay = '';
            if (!empty($spd->result)) {
                // result puede ser JSON (arreglo de objetos) o escalar
                $decoded = null;
                try {
                    $decoded = is_string($spd->result) ? json_decode($spd->result, true) : $spd->result;
                } catch (\Throwable $e) {
                    $decoded = null;
                }
                if (is_array($decoded)) {
                    $keys = ['resultado','result','valor','value','lectura','reading','valor_leido','resultado_reportado'];
                    if (array_key_exists(0, $decoded)) {
                        // Lista de items
                        $first = $decoded[0];
                        if (is_array($first)) {
                            foreach ($keys as $k) {
                                if (array_key_exists($k, $first) && $first[$k] !== null && $first[$k] !== '') {
                                    $resultadoDisplay = (string) $first[$k];
                                    break;
                                }
                            }
                        } else {
                            $resultadoDisplay = (string) $first;
                        }
                    } else {
                        // Arreglo asociativo: buscar claves directamente
                        foreach ($keys as $k) {
                            if (array_key_exists($k, $decoded) && $decoded[$k] !== null && $decoded[$k] !== '') {
                                $resultadoDisplay = (string) $decoded[$k];
                                break;
                            }
                        }
                        if ($resultadoDisplay === '') {
                            // como fallback, tomar primer valor escalar
                            $firstVal = reset($decoded);
                            if (is_scalar($firstVal)) {
                                $resultadoDisplay = (string) $firstVal;
                            }
                        }
                    }
                } elseif (is_scalar($spd->result)) {
                    $resultadoDisplay = (string) $spd->result;
                }
            }

            // pH
            if (strpos($serviceName, 'ph') !== false) {
                // Fecha solo desde tabla de pH (formateada Y-m-d)
                $fechaAnalisis = '';
                if ($spd->phAnalysis && !empty($spd->phAnalysis->fecha_analisis)) {
                    try {
                        $fechaAnalisis = \Illuminate\Support\Carbon::parse($spd->phAnalysis->fecha_analisis)->format('Y-m-d');
                    } catch (\Throwable $e) {
                        $fechaAnalisis = (string) $spd->phAnalysis->fecha_analisis;
                    }
                }
                $rows[] = [
                    'ensayo' => 'Determinación de pH',
                    'resultado' => $resultadoDisplay, // desde service_process_details.result
                    'unidad' => 'Unidades de pH',
                    'fecha_analisis' => $fechaAnalisis, // desde ph_analyses.fecha_analisis
                    'tecnica' => 'Potenciométrico relación 1:1 suelo-agua',
                    'documento' => 'NTC 5264:2023',
                ];
                continue;
            }

            // Conductividad eléctrica
            if (strpos($serviceName, 'conductividad') !== false || strpos($serviceName, 'conductivity') !== false) {
                // Resultado: preferir ServiceProcessDetail->result; si vacío, intentar desde items_ensayo
                if ($resultadoDisplay === '' && $spd->conductivityAnalysis && is_array($spd->conductivityAnalysis->items_ensayo ?? null)) {
                    $items = $spd->conductivityAnalysis->items_ensayo;
                    if (isset($items[0]) && is_array($items[0])) {
                        if (array_key_exists('resultado', $items[0])) {
                            $resultadoDisplay = (string) ($items[0]['resultado'] ?? '');
                        } elseif (array_key_exists('resultado_valor', $items[0])) {
                            $resultadoDisplay = (string) ($items[0]['resultado_valor'] ?? '');
                        } elseif (array_key_exists('lectura', $items[0])) {
                            $resultadoDisplay = (string) ($items[0]['lectura'] ?? '');
                        }
                    }
                }

                // Fecha solo desde tabla de Conductividad (formateada Y-m-d)
                $fechaAnalisis = '';
                if ($spd->conductivityAnalysis && !empty($spd->conductivityAnalysis->fecha_analisis)) {
                    try {
                        $fechaAnalisis = \Illuminate\Support\Carbon::parse($spd->conductivityAnalysis->fecha_analisis)->format('Y-m-d');
                    } catch (\Throwable $e) {
                        $fechaAnalisis = (string) $spd->conductivityAnalysis->fecha_analisis;
                    }
                }
                $rows[] = [
                    'ensayo' => 'Determinación de Conductividad eléctrica',
                    'resultado' => $resultadoDisplay,
                    'unidad' => 'dS/m',
                    'fecha_analisis' => $fechaAnalisis, // desde conductivity_analyses.fecha_analisis
                    'tecnica' => 'Electrométrico. Valor corregido a 25 °C',
                    'documento' => 'NTC 5596:2022 Método B',
                ];
                continue;
            }

            // Fósforo disponible
            if (strpos($serviceNameNorm, 'fosforo') !== false) {
                // Buscar fecha_analisis en tabla phosphorus_analyses por process_id y service_id
                $fechaAnalisis = '';
                try {
                    $pa = PhosphorusAnalysis::where('process_id', $spd->process_id)
                        ->where('service_id', $spd->service_id)
                        ->latest('fecha_analisis')
                        ->first();
                    if ($pa && !empty($pa->fecha_analisis)) {
                        $fechaAnalisis = $pa->fecha_analisis;
                    }
                } catch (\Throwable $e) {
                    Log::warning('No se pudo obtener fecha_analisis de fósforo para reporte', [
                        'process_id' => $spd->process_id,
                        'service_id' => $spd->service_id,
                        'error' => $e->getMessage(),
                    ]);
                }

                $rows[] = [
                    'ensayo' => 'Determinación de Fósforo disponible',
                    'resultado' => $resultadoDisplay,
                    'unidad' => 'mg/kg',
                    'fecha_analisis' => $fechaAnalisis,
                    'tecnica' => 'Fotométrico',
                    'documento' => 'NTC 5350:2020 Bray II',
                ];
                continue;
            }

            // Textura
            if (strpos($serviceNameNorm, 'textura') !== false || strpos($serviceNameNorm, 'texture') !== false) {
                // Buscar fecha_analisis en tabla batch_texture_analyses por process_id y service_id
                $fechaAnalisis = '';
                try {
                    $ta = \Modules\LSCEFA\Entities\BatchTextureAnalysis::where('process_id', $spd->process_id)
                        ->where('service_id', $spd->service_id)
                        ->latest('analysis_date')
                        ->first();
                    if ($ta && !empty($ta->analysis_date)) {
                        $fechaAnalisis = $ta->analysis_date;
                    }
                } catch (\Throwable $e) {
                    Log::warning('No se pudo obtener fecha_analisis de textura para reporte', [
                        'process_id' => $spd->process_id,
                        'service_id' => $spd->service_id,
                        'error' => $e->getMessage(),
                    ]);
                }

                $rows[] = [
                    'ensayo' => 'Determinación de Textura',
                    'resultado' => $resultadoDisplay,
                    'unidad' => 'Clase textural',
                    'fecha_analisis' => $fechaAnalisis,
                    'tecnica' => 'Hidrómetro de Bouyoucos',
                    'documento' => 'NTC 5264:2023',
                ];
                continue;
            }

            // Humedad
            if (strpos($serviceNameNorm, 'humedad') !== false || strpos($serviceNameNorm, 'humidity') !== false) {
                // Buscar fecha_analisis en tabla humidity_analyses por process_id y service_id
                $fechaAnalisis = '';
                try {
                    $ha = HumidityAnalysis::where('process_id', $spd->process_id)
                        ->where('service_id', $spd->service_id)
                        ->latest('fecha_analisis')
                        ->first();
                    if ($ha && !empty($ha->fecha_analisis)) {
                        $fechaAnalisis = $ha->fecha_analisis;
                    }
                } catch (\Throwable $e) {
                    Log::warning('No se pudo obtener fecha_analisis de humedad para reporte', [
                        'process_id' => $spd->process_id,
                        'service_id' => $spd->service_id,
                        'error' => $e->getMessage(),
                    ]);
                }

                $rows[] = [
                    'ensayo' => 'Determinación de Humedad',
                    'resultado' => $resultadoDisplay,
                    'unidad' => '%',
                    'fecha_analisis' => $fechaAnalisis,
                    'tecnica' => 'Gravimétrico por secado en estufa',
                    'documento' => 'NTC 5264:2023',
                ];
                continue;
            }

            // Fallback genérico para otros servicios
            $rows[] = [
                'ensayo' => $spd->service->descripcion ?? 'Ensayo',
                'resultado' => $resultadoDisplay,
                'unidad' => $spd->service->unidad ?? '',
                'fecha_analisis' => '',
                'tecnica' => $spd->service->metodo ?? '',
                'documento' => $spd->service->norma ?? '',
            ];
        }

        return view('lscefa::reports.show', [
            'process' => $process,
            'rows' => $rows,
        ]);
    }

    /**
     * Generar PDF del informe para un proceso.
     */
    public function pdf($processId)
    {
        $process = Process::with([
            'serviceProcessDetails' => function ($q) {
                $q->with(['service', 'phAnalysis', 'conductivityAnalysis']);
            },
            'quote',
            'customer',
        ])->findOrFail($processId);

        // Reutilizar la lógica de construcción de filas
        $rows = [];
        foreach ($process->serviceProcessDetails as $spd) {
            $serviceName = strtolower($spd->service->descripcion ?? '');
            $serviceNameNorm = str_replace(['á','é','í','ó','ú'], ['a','e','i','o','u'], $serviceName);

            $resultadoDisplay = '';
            if (!empty($spd->result)) {
                $decoded = null;
                try {
                    $decoded = is_string($spd->result) ? json_decode($spd->result, true) : $spd->result;
                } catch (\Throwable $e) {
                    $decoded = null;
                }
                if (is_array($decoded)) {
                    $keys = ['resultado','result','valor','value','lectura','reading','valor_leido','resultado_reportado'];
                    if (array_key_exists(0, $decoded)) {
                        $first = $decoded[0];
                        if (is_array($first)) {
                            foreach ($keys as $k) {
                                if (array_key_exists($k, $first) && $first[$k] !== null && $first[$k] !== '') {
                                    $resultadoDisplay = (string) $first[$k];
                                    break;
                                }
                            }
                        } else {
                            $resultadoDisplay = (string) $first;
                        }
                    } else {
                        foreach ($keys as $k) {
                            if (array_key_exists($k, $decoded) && $decoded[$k] !== null && $decoded[$k] !== '') {
                                $resultadoDisplay = (string) $decoded[$k];
                                break;
                            }
                        }
                        if ($resultadoDisplay === '') {
                            $firstVal = reset($decoded);
                            if (is_scalar($firstVal)) {
                                $resultadoDisplay = (string) $firstVal;
                            }
                        }
                    }
                } elseif (is_scalar($spd->result)) {
                    $resultadoDisplay = (string) $spd->result;
                }
            }

            if (strpos($serviceName, 'ph') !== false) {
                $fechaAnalisis = '';
                if ($spd->phAnalysis && !empty($spd->phAnalysis->fecha_analisis)) {
                    try {
                        $fechaAnalisis = \Illuminate\Support\Carbon::parse($spd->phAnalysis->fecha_analisis)->format('Y-m-d');
                    } catch (\Throwable $e) {
                        $fechaAnalisis = (string) $spd->phAnalysis->fecha_analisis;
                    }
                }
                $rows[] = [
                    'ensayo' => 'Determinación de pH',
                    'resultado' => $resultadoDisplay,
                    'unidad' => 'Unidades de pH',
                    'fecha_analisis' => $fechaAnalisis,
                    'tecnica' => 'Potenciométrico relación 1:1 suelo-agua',
                    'documento' => 'NTC 5264:2023',
                ];
                continue;
            }

            if (strpos($serviceName, 'conductividad') !== false || strpos($serviceName, 'conductivity') !== false) {
                // Resultado: preferir ServiceProcessDetail->result; si vacío, intentar desde items_ensayo
                if ($resultadoDisplay === '' && $spd->conductivityAnalysis && is_array($spd->conductivityAnalysis->items_ensayo ?? null)) {
                    $items = $spd->conductivityAnalysis->items_ensayo;
                    if (isset($items[0]) && is_array($items[0])) {
                        if (array_key_exists('resultado', $items[0])) {
                            $resultadoDisplay = (string) ($items[0]['resultado'] ?? '');
                        } elseif (array_key_exists('resultado_valor', $items[0])) {
                            $resultadoDisplay = (string) ($items[0]['resultado_valor'] ?? '');
                        } elseif (array_key_exists('lectura', $items[0])) {
                            $resultadoDisplay = (string) ($items[0]['lectura'] ?? '');
                        }
                    }
                }

                $fechaAnalisis = '';
                if ($spd->conductivityAnalysis && !empty($spd->conductivityAnalysis->fecha_analisis)) {
                    try {
                        $fechaAnalisis = \Illuminate\Support\Carbon::parse($spd->conductivityAnalysis->fecha_analisis)->format('Y-m-d');
                    } catch (\Throwable $e) {
                        $fechaAnalisis = (string) $spd->conductivityAnalysis->fecha_analisis;
                    }
                }
                $rows[] = [
                    'ensayo' => 'Determinación de Conductividad eléctrica',
                    'resultado' => $resultadoDisplay,
                    'unidad' => 'dS/m',
                    'fecha_analisis' => $fechaAnalisis,
                    'tecnica' => 'Electrométrico. Valor corregido a 25 °C',
                    'documento' => 'NTC 5596:2022 Método B',
                ];
                continue;
            }

            if (strpos($serviceNameNorm, 'fosforo') !== false) {
                $fechaAnalisis = '';
                try {
                    $pa = PhosphorusAnalysis::where('process_id', $spd->process_id)
                        ->where('service_id', $spd->service_id)
                        ->latest('fecha_analisis')
                        ->first();
                    if ($pa && !empty($pa->fecha_analisis)) {
                        $fechaAnalisis = $pa->fecha_analisis;
                    }
                } catch (\Throwable $e) {
                    Log::warning('No se pudo obtener fecha_analisis de fósforo para reporte (PDF)', [
                        'process_id' => $spd->process_id,
                        'service_id' => $spd->service_id,
                        'error' => $e->getMessage(),
                    ]);
                }

                $rows[] = [
                    'ensayo' => 'Determinación de Fósforo disponible',
                    'resultado' => $resultadoDisplay,
                    'unidad' => 'mg/kg',
                    'fecha_analisis' => $fechaAnalisis,
                    'tecnica' => 'Fotométrico',
                    'documento' => 'NTC 5350:2020 Bray II',
                ];
                continue;
            }

            $rows[] = [
                'ensayo' => $spd->service->descripcion ?? 'Ensayo',
                'resultado' => $resultadoDisplay,
                'unidad' => $spd->service->unidad ?? '',
                'fecha_analisis' => '',
                'tecnica' => $spd->service->metodo ?? '',
                'documento' => $spd->service->norma ?? '',
            ];
        }

        // Determinar si existe al menos un servicio acreditado en el proceso
        $hasAccredited = false;
        foreach ($process->serviceProcessDetails as $spd) {
            if ($spd->service && !empty($spd->service->acreditado)) {
                $hasAccredited = true;
                break;
            }
        }

        $issuedAt = now();

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('lscefa::reports.pdf', [
            'process' => $process,
            'rows' => $rows,
            'hasAccredited' => $hasAccredited,
            'issuedAt' => $issuedAt,
        ]);

        $filename = 'informe_' . $process->process_id . '.pdf';
        return $pdf->download($filename);
    }
}
