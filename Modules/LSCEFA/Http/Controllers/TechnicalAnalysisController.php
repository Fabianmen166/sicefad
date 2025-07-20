<?php

namespace Modules\LSCEFA\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\LSCEFA\Models\Process;
use Illuminate\Support\Facades\Auth;

class TechnicalAnalysisController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        // Obtener todos los procesos pendientes con sus detalles de servicio
        $processes = Process::with(['quote', 'serviceProcessDetails.service'])
            ->orderBy('reception_date', 'desc')
            ->paginate(20);

        // Agrupar los detalles de servicio por proceso y estado
        $pending = [];
        $completed = [];
        $returned = [];
        foreach ($processes as $process) {
            $pendingDetails = [];
            $completedDetails = [];
            $returnedDetails = [];
            foreach ($process->serviceProcessDetails ?? [] as $spd) {
                if ($spd->status === 'pending') {
                    $pendingDetails[] = $spd;
                } elseif ($spd->status === 'completed') {
                    $completedDetails[] = $spd;
                } elseif ($spd->status === 'rejected') {
                    $returnedDetails[] = $spd;
                }
            }
            $pending[$process->process_id] = $pendingDetails;
            $completed[$process->process_id] = $completedDetails;
            $returned[$process->process_id] = $returnedDetails;
        }
        return view('lscefa::technical.analyses_index', compact('processes', 'pending', 'completed', 'returned'));
    }
} 