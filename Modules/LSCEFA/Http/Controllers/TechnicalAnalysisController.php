<?php

namespace Modules\LSCEFA\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\LSCEFA\Models\Process;
use Illuminate\Support\Facades\Auth;
use Modules\LSCEFA\Models\ServiceProcessDetail;

class TechnicalAnalysisController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        // Obtener todos los procesos pendientes con sus detalles de servicio y análisis relacionados
        $processes = Process::with([
                'quote',
                'serviceProcessDetails' => function($q){
                    $q->with(['service', 'phAnalysis', 'conductivityAnalysis']);
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

        // Calcular 'devueltos' a nivel global (no paginado) para no perder análisis por páginas
        $returnedQuery = ServiceProcessDetail::with(['service', 'phAnalysis', 'conductivityAnalysis'])
            ->where('status', 'rejected')
            ->where(function($q){
                $q->whereHas('phAnalysis', function($qa){
                        $qa->whereIn('review_status', ['returned', 'rejected']);
                    })
                  ->orWhereHas('conductivityAnalysis', function($qb){
                        $qb->whereIn('review_status', ['returned', 'rejected']);
                    });
            })
            ->get();

        \Log::info('TechnicalAnalysis@index metrics', [
            'processes_page_count' => $processes->count(),
            'returned_total' => $returnedQuery->count(),
        ]);

        foreach ($returnedQuery as $spd) {
            $returned[$spd->process_id] = $returned[$spd->process_id] ?? [];
            $returned[$spd->process_id][] = $spd;
        }

        // Log per-process returned counts
        foreach ($returned as $pid => $arr) {
            \Log::info('Returned per process', [ 'process_id' => $pid, 'count' => count($arr) ]);
        }
        return view('lscefa::technical.analyses_index', compact('processes', 'pending', 'completed', 'returned'));
    }
} 