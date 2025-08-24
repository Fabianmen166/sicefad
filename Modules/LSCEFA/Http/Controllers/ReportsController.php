<?php

namespace Modules\LSCEFA\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\LSCEFA\Models\Process;
use Modules\LSCEFA\Models\ServiceProcessDetail;
use Modules\LSCEFA\Models\Service;
use Modules\LSCEFA\Models\PhAnalysis;
use Modules\LSCEFA\Entities\PhosphorusAnalysis;

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
            ->whereIn('status', ['pending', 'processing', 'in_progress']);

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
                $q->with(['service', 'phAnalysis', 'conductivityAnalysis']);
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
                    // Si es un arreglo de objetos {identificacion, resultado}
                    if (isset($decoded[0]) && is_array($decoded[0]) && array_key_exists('resultado', $decoded[0])) {
                        $resultadoDisplay = (string) ($decoded[0]['resultado'] ?? '');
                    } else {
                        // Si es un arreglo simple, tomar el primer valor
                        $first = reset($decoded);
                        $resultadoDisplay = is_array($first) ? (string) ($first['resultado'] ?? '') : (string) $first;
                    }
                } elseif (is_scalar($spd->result)) {
                    $resultadoDisplay = (string) $spd->result;
                }
            }

            // pH
            if (strpos($serviceName, 'ph') !== false) {
                // Fecha solo desde tabla de pH
                $fechaAnalisis = '';
                if ($spd->phAnalysis && !empty($spd->phAnalysis->fecha_analisis)) {
                    $fechaAnalisis = $spd->phAnalysis->fecha_analisis;
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
                // Fecha solo desde tabla de Conductividad
                $fechaAnalisis = '';
                if ($spd->conductivityAnalysis && !empty($spd->conductivityAnalysis->fecha_analisis)) {
                    $fechaAnalisis = $spd->conductivityAnalysis->fecha_analisis;
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
                    if (isset($decoded[0]) && is_array($decoded[0]) && array_key_exists('resultado', $decoded[0])) {
                        $resultadoDisplay = (string) ($decoded[0]['resultado'] ?? '');
                    } else {
                        $first = reset($decoded);
                        $resultadoDisplay = is_array($first) ? (string) ($first['resultado'] ?? '') : (string) $first;
                    }
                } elseif (is_scalar($spd->result)) {
                    $resultadoDisplay = (string) $spd->result;
                }
            }

            if (strpos($serviceName, 'ph') !== false) {
                $fechaAnalisis = '';
                if ($spd->phAnalysis && !empty($spd->phAnalysis->fecha_analisis)) {
                    $fechaAnalisis = $spd->phAnalysis->fecha_analisis;
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
                $fechaAnalisis = '';
                if ($spd->conductivityAnalysis && !empty($spd->conductivityAnalysis->fecha_analisis)) {
                    $fechaAnalisis = $spd->conductivityAnalysis->fecha_analisis;
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
