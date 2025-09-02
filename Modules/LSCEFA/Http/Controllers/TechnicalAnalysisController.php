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
use Modules\LSCEFA\Entities\SulfurAnalysis;

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
            // Mostrar únicamente procesos que tengan al menos un servicio pendiente
            ->whereHas('serviceProcessDetails', function($q){
                $q->where('status', 'pending');
            })
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

        // Incluir análisis de textura devueltos/rechazados (están en estado 'pending' cuando se rechazan)
        $textureReturned = collect();
        try {
            $textureReturned = ServiceProcessDetail::with(['service', 'batchTextureAnalysis'])
                ->where('status', 'pending')
                ->whereHas('batchTextureAnalysis', function($query) {
                    $query->where('review_status', 'rejected');
                })
                ->get();
            
            \Log::info('Texture returned query result', [
                'count' => $textureReturned->count(),
                'items' => $textureReturned->map(function($spd) {
                    $textureAnalysis = $spd->batchTextureAnalysis;
                    return [
                        'id' => $textureAnalysis->id ?? null,
                        'process_id' => $spd->process_id,
                        'service_id' => $spd->service_id,
                        'review_status' => $textureAnalysis->review_status ?? null,
                        'review_date' => $textureAnalysis->review_date ?? null,
                        'consecutivo_no' => $textureAnalysis->consecutive_no ?? null,
                    ];
                })->toArray()
            ]);
        } catch (\Throwable $e) {
            \Log::warning('No se pudieron cargar análisis de textura devueltos: ' . $e->getMessage());
        }

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

        // Incluir análisis de Intercambio Catiónico devueltos/rechazados (tabla independiente)
        $cationicReturned = collect();
        try {
            if (Schema::hasColumn('cationic_analyses', 'review_status')) {
                $cationicReturned = \Modules\LSCEFA\Entities\CationicAnalysis::with(['service'])
                    ->whereIn('review_status', ['returned', 'rejected'])
                    ->get();
            }
        } catch (\Throwable $e) {
            \Log::warning('No se pudieron cargar análisis de intercambio catiónico devueltos: ' . $e->getMessage());
        }

        // Incluir análisis de Azufre devueltos/rechazados (tabla independiente)
        $sulfurReturned = collect();
        try {
            if (Schema::hasColumn('sulfur_analyses', 'review_status')) {
                $sulfurReturned = \Modules\LSCEFA\Entities\SulfurAnalysis::with(['process.quote.customer'])
                    ->whereIn('review_status', ['returned', 'rejected'])
                    ->get();
            }
        } catch (\Throwable $e) {
            \Log::warning('No se pudieron cargar análisis de azufre devueltos: ' . $e->getMessage());
        }

        \Log::info('TechnicalAnalysis@index metrics', [
            'processes_page_count' => $processes->count(),
            'returned_total_ph_cond' => $returnedQuery->count(),
            'returned_total_texture' => $textureReturned->count(),
            'returned_total_phosphorus' => $phosphorusReturned->count(),
            'returned_total_boron' => $boronReturned->count(),
            'returned_total_cationic' => $cationicReturned->count(),
            'returned_total_sulfur' => $sulfurReturned->count(),
        ]);

        foreach ($returnedQuery as $spd) {
            $returned[$spd->process_id] = $returned[$spd->process_id] ?? [];
            $returned[$spd->process_id][] = $spd;
        }

        // Agregar análisis de textura al arreglo de devueltos (objetos simplificados compatibles con la vista)
        foreach ($textureReturned as $spd) {
            $textureAnalysis = $spd->batchTextureAnalysis;
            if ($textureAnalysis) {
                $returned[$spd->process_id] = $returned[$spd->process_id] ?? [];
                // Empaquetar un objeto con los campos esperados por la vista
                $returned[$spd->process_id][] = (object) [
                    'id' => $textureAnalysis->id, // Agregar ID para las rutas de acción
                    'process_id' => $spd->process_id,
                    'service_id' => $spd->service_id,
                    'service' => $spd->service ?? null,
                    'service_name' => optional($spd->service)->descripcion ?? 'Servicio',
                    'type' => 'texture',
                    'consecutivo_no' => $textureAnalysis->consecutive_no ?? null,
                    'review_date' => $textureAnalysis->review_date ?? $textureAnalysis->updated_at ?? null,
                    'review_observations' => $textureAnalysis->review_observations ?? null,
                ];
                
                \Log::info('Texture analysis added to returned array', [
                    'texture_id' => $textureAnalysis->id,
                    'process_id' => $spd->process_id,
                    'service_id' => $spd->service_id,
                    'type' => 'texture',
                    'review_status' => $textureAnalysis->review_status,
                    'review_date' => $textureAnalysis->review_date,
                ]);
            }
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

        // Agregar intercambio catiónico al arreglo de devueltos (objetos simplificados compatibles con la vista)
        foreach ($cationicReturned as $ca) {
            $returned[$ca->process_id] = $returned[$ca->process_id] ?? [];
            // Empaquetar un objeto con los campos esperados por la vista
            $returned[$ca->process_id][] = (object) [
                'id' => $ca->id, // Agregar ID para las rutas de acción
                'process_id' => $ca->process_id,
                'service_id' => $ca->service_id,
                'service' => $ca->service ?? null,
                'service_name' => optional($ca->service)->descripcion ?? 'Servicio',
                'type' => 'cationic',
                'consecutivo_no' => $ca->consecutivo_no ?? null,
                'review_date' => $ca->review_date ?? $ca->updated_at ?? null,
                'review_observations' => $ca->review_observations ?? null,
            ];
            
            \Log::info('Cationic analysis added to returned array', [
                'cationic_id' => $ca->id,
                'process_id' => $ca->process_id,
                'service_id' => $ca->service_id,
                'type' => 'cationic',
                'review_status' => $ca->review_status,
                'review_date' => $ca->review_date,
            ]);
        }

        // Agregar azufre al arreglo de devueltos (objetos simplificados compatibles con la vista)
        foreach ($sulfurReturned as $sa) {
            $returned[$sa->process_id] = $returned[$sa->process_id] ?? [];
            // Empaquetar un objeto con los campos esperados por la vista
            $returned[$sa->process_id][] = (object) [
                'id' => $sa->id, // Agregar ID para las rutas de acción
                'process_id' => $sa->process_id,
                'service_id' => $sa->service_id,
                'service' => null, // El servicio se obtiene del ServiceProcessDetail
                'service_name' => 'Azufre', // Nombre fijo del servicio
                'type' => 'sulfur',
                'consecutivo_no' => $sa->consecutive_no ?? null,
                'review_date' => $sa->review_date ?? $sa->updated_at ?? null,
                'review_observations' => $sa->review_observations ?? null,
            ];
            
            \Log::info('Sulfur analysis added to returned array', [
                'sulfur_id' => $sa->id,
                'process_id' => $sa->process_id,
                'service_id' => $sa->service_id,
                'type' => 'sulfur',
                'review_status' => $sa->review_status,
                'review_date' => $sa->review_date,
            ]);
        }

        // Log per-process returned counts
        foreach ($returned as $pid => $arr) {
            \Log::info('Returned per process', [ 'process_id' => $pid, 'count' => count($arr) ]);
        }
        
        // Log final para debuggear la vista
        \Log::info('Final returned array for view', [
            'total_processes' => count($returned),
            'total_items' => collect($returned)->flatten(1)->count(),
            'texture_items' => collect($returned)->flatten(1)->where('type', 'texture')->count(),
            'ph_items' => collect($returned)->flatten(1)->where('type', 'ph')->count(),
            'conductivity_items' => collect($returned)->flatten(1)->where('type', 'conductivity')->count(),
            'phosphorus_items' => collect($returned)->flatten(1)->where('type', 'phosphorus')->count(),
            'boron_items' => collect($returned)->flatten(1)->where('type', 'boron')->count(),
        ]);
        
        return view('lscefa::technical.analyses_index', compact('processes', 'pending', 'completed', 'returned'));
    }
} 