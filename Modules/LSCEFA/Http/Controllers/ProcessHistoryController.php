<?php

namespace Modules\LSCEFA\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Modules\LSCEFA\Models\Process;
use Modules\LSCEFA\Models\ServiceProcessDetail;
use Modules\LSCEFA\Entities\PhosphorusAnalysis;

class ProcessHistoryController extends Controller
{
    public function index()
    {
        $processes = Process::with(['quote', 'quote.customer'])
            ->latest('created_at')
            ->paginate(15);

        return view('lscefa::process_history.index', [
            'processes' => $processes,
        ]);
    }

    public function show($processId)
    {
        $process = Process::with([
            'quote',
            'quote.customer',
            'serviceProcessDetails.service',
            'serviceProcessDetails.phAnalysis',
            'serviceProcessDetails.conductivityAnalysis',
        ])->findOrFail($processId);

        // Communications and files from process/quote
        $quote = $process->quote;
        $customer = $quote?->customer;

        // Build services + analyses snapshot
        $services = [];
        foreach ($process->serviceProcessDetails as $spd) {
            $service = $spd->service;
            $entry = [
                'service_id' => $spd->service_id,
                'description' => $service->descripcion ?? 'Servicio',
                'status' => $spd->status,
                'result' => $spd->result,
                'file' => $spd->file,
                'observations' => $spd->observations,
                'ph' => null,
                'conductivity' => null,
                'phosphorus' => null,
            ];
            if ($spd->phAnalysis) {
                $entry['ph'] = [
                    'fecha_analisis' => $spd->phAnalysis->fecha_analisis ?? null,
                    'review_status' => $spd->phAnalysis->review_status ?? null,
                ];
            }
            if ($spd->conductivityAnalysis) {
                $entry['conductivity'] = [
                    'fecha_analisis' => $spd->conductivityAnalysis->fecha_analisis ?? null,
                    'review_status' => $spd->conductivityAnalysis->review_status ?? null,
                ];
            }
            // Map phosphorus by process/service
            $paQuery = PhosphorusAnalysis::where('process_id', $process->process_id)
                ->where('service_id', $spd->service_id);

            // Prefer English column names when present
            $dateColumn = Schema::hasColumn('phosphorus_analyses', 'analysis_date') ? 'analysis_date' : 'fecha_analisis';

            $pa = $paQuery->orderBy($dateColumn, 'desc')->first();
            if ($pa) {
                $entry['phosphorus'] = [
                    // Expose as fecha_analisis for legacy view key, but prefer analysis_date value if present
                    'fecha_analisis' => $pa->analysis_date ?? $pa->fecha_analisis ?? null,
                    'review_status' => $pa->review_status ?? null,
                    'fosforo_disponible_mg_kg' => $pa->fosforo_disponible_mg_kg ?? null,
                ];
            }
            $services[] = $entry;
        }

        // Build report rows reusing logic from ReportsController@show (simplified)
        $reportRows = app(ReportsController::class)->show($processId)->getData()['rows'] ?? [];

        return view('lscefa::process_history.show', [
            'process' => $process,
            'quote' => $quote,
            'customer' => $customer,
            'services' => $services,
            'reportRows' => $reportRows,
        ]);
    }
}
