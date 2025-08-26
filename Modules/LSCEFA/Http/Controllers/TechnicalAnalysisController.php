<?php

namespace Modules\LSCEFA\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\LSCEFA\Models\Process;
use Illuminate\Support\Facades\Auth;
use Modules\LSCEFA\Models\ServiceProcessDetail;
use Illuminate\Support\Facades\Schema;
use Modules\LSCEFA\Entities\PhosphorusAnalysis;
use Modules\LSCEFA\Entities\BoronAnalysisDetail;

class TechnicalAnalysisController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        // Obtener todos los procesos pendientes con sus detalles de servicio y análisis relacionados
        $processes = Process::with([
                'quote',
                'serviceProcessDetails' => function($q){
                    $q->with(['service', 'phAnalysis', 'conductivityAnalysis', 'batchTextureAnalysis']);
                }
            ])
            ->orderBy('reception_date', 'desc')
            ->paginate(20);

        // Agrupar los detalles de servicio por proceso y estado (pendiente/completado) para la página actual
        $pending = [];
        $completed = [];
        $returned = [];
        foreach ($processes as $process) {
            $pendingDetails = [];
            $completedDetails = [];
            foreach ($process->serviceProcessDetails ?? [] as $spd) {
                if ($spd->status === 'pending') {
                    $pendingDetails[] = $spd;
                } elseif ($spd->status === 'completed') {
                    $completedDetails[] = $spd;
                }
            }
            $pending[$process->process_id] = $pendingDetails;
            $completed[$process->process_id] = $completedDetails;
        }

        // Calcular 'devueltos' a nivel global usando SOLO el estado del detalle de servicio
        // Evitar depender de columnas review_status en tablas de análisis
        $returnedQuery = ServiceProcessDetail::with(['service', 'phAnalysis', 'conductivityAnalysis', 'batchTextureAnalysis'])
            ->where('status', 'rejected')
            ->get();

        // Incluir análisis de Boro devueltos/rechazados (tabla independiente)
        $boronReturned = collect();
        try {
            $boronReturned = BoronAnalysisDetail::with(['service'])
                ->whereIn('review_status', ['returned', 'rejected'])
                ->get();
            
            \Log::info('Boron returned query result', [
                'count' => $boronReturned->count(),
                'items' => $boronReturned->map(function($ba) {
                    return [
                        'id' => $ba->id,
                        'process_id' => $ba->process_id,
                        'service_id' => $ba->service_id,
                        'review_status' => $ba->review_status,
                        'review_date' => $ba->review_date,
                    ];
                })->toArray()
            ]);
        } catch (\Throwable $e) {
            \Log::warning('No se pudieron cargar análisis de boro devueltos: ' . $e->getMessage());
        }

        // Incluir análisis de Fósforo devueltos/rechazados (tabla independiente)
        $phosphorusReturned = collect();
        try {
            if (Schema::hasColumn('phosphorus_analyses', 'review_status')) {
                $phosphorusReturned = PhosphorusAnalysis::with(['service'])
                    ->whereIn('review_status', ['returned', 'rejected'])
                    ->get();
            }
        } catch (\Throwable $e) {
            \Log::warning('No se pudieron cargar análisis de fósforo devueltos: ' . $e->getMessage());
        }

        \Log::info('TechnicalAnalysis@index metrics', [
            'processes_page_count' => $processes->count(),
            'returned_total_ph_cond' => $returnedQuery->count(),
            'returned_total_phosphorus' => $phosphorusReturned->count(),
            'returned_total_boron' => $boronReturned->count(),
        ]);

        foreach ($returnedQuery as $spd) {
            $returned[$spd->process_id] = $returned[$spd->process_id] ?? [];
            $returned[$spd->process_id][] = $spd;
        }

        // Agregar fósforo al arreglo de devueltos (objetos simplificados compatibles con la vista)
        foreach ($phosphorusReturned as $pa) {
            $returned[$pa->process_id] = $returned[$pa->process_id] ?? [];
            // Empaquetar un objeto con los campos esperados por la vista
            $returned[$pa->process_id][] = (object) [
                'id' => $pa->id, // Agregar ID para las rutas de acción
                'process_id' => $pa->process_id,
                'service_id' => $pa->service_id,
                'service' => $pa->service ?? null,
                'service_name' => optional($pa->service)->descripcion ?? 'Servicio',
                'type' => 'phosphorus',
                'consecutivo_no' => $pa->consecutivo_no ?? null,
                'review_date' => $pa->review_date ?? $pa->updated_at ?? null,
                'review_observations' => $pa->review_observations ?? null,
            ];
        }

        // Agregar boro al arreglo de devueltos (objetos simplificados compatibles con la vista)
        foreach ($boronReturned as $ba) {
            $returned[$ba->process_id] = $returned[$ba->process_id] ?? [];
            // Empaquetar un objeto con los campos esperados por la vista
            $returned[$ba->process_id][] = (object) [
                'id' => $ba->id, // Agregar ID para las rutas de acción
                'process_id' => $ba->process_id,
                'service_id' => $ba->service_id,
                'service' => $ba->service ?? null,
                'service_name' => optional($ba->service)->descripcion ?? 'Servicio',
                'type' => 'boron',
                'consecutivo_no' => $ba->consecutive_no ?? null,
                'review_date' => $ba->review_date ?? $ba->updated_at ?? null,
                'review_observations' => $ba->review_observations ?? null,
            ];
            
            \Log::info('Boron analysis added to returned array', [
                'boron_id' => $ba->id,
                'process_id' => $ba->process_id,
                'service_id' => $ba->service_id,
                'type' => 'boron',
                'review_status' => $ba->review_status,
                'review_date' => $ba->review_date,
            ]);
        }

        // Log per-process returned counts
        foreach ($returned as $pid => $arr) {
            \Log::info('Returned per process', [ 'process_id' => $pid, 'count' => count($arr) ]);
        }
        return view('lscefa::technical.analyses_index', compact('processes', 'pending', 'completed', 'returned'));
    }
} 