<?php

namespace Modules\LSCEFA\Http\Controllers;

use Illuminate\Http\Request;
use Modules\LSCEFA\Models\PhAnalysis;
use Illuminate\Support\Facades\DB;

class PhAnalysisDataController extends Controller
{
    public function showData()
    {
        // Obtener todos los registros de ph_analyses
        $phAnalyses = PhAnalysis::select([
                'id',
                'consecutivo_no',
                'fecha_analisis',
                'user_id',
                'items_ensayo',
                'review_status',
                'created_at',
                'updated_at'
            ])
            ->orderBy('consecutivo_no', 'desc')
            ->get();

        // Procesar los datos para mostrarlos de manera legible
        $results = [];
        foreach ($phAnalyses as $analysis) {
            $items = is_array($analysis->items_ensayo) ? count($analysis->items_ensayo) : 0;
            
            $results[] = [
                'id' => $analysis->id,
                'consecutivo_no' => $analysis->consecutivo_no,
                'fecha_analisis' => $analysis->fecha_analisis,
                'user_id' => $analysis->user_id,
                'total_items' => $items,
                'review_status' => $analysis->review_status,
                'created_at' => $analysis->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $analysis->updated_at->format('Y-m-d H:i:s'),
                'items_sample' => $items > 0 ? array_slice($analysis->items_ensayo, 0, 1) : []
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $results
        ]);
    }

    public function showByConsecutivo($consecutivo)
    {
        // Buscar análisis por número de consecutivo
        $analysis = PhAnalysis::where('consecutivo_no', $consecutivo)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $analysis
        ]);
    }
}
