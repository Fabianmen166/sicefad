<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DebugTexture extends Command
{
    protected $signature = 'debug:texture';
    protected $description = 'Debug texture analysis data';

    public function handle()
    {
        $analysis = \Modules\LSCEFA\Entities\BatchTextureAnalysis::where('review_status', 'rejected')->first();

        if ($analysis) {
            $this->info("=== ANÁLISIS RECHAZADO ENCONTRADO ===");
            $this->info("ID: " . $analysis->id);
            $this->info("Consecutivo: " . $analysis->consecutive_no);
            
            // Buscar los controles analíticos en analytical_controls
            $analyticalControls = \Modules\LSCEFA\Entities\AnalyticalControl::where('analysis_id', $analysis->id)
                ->where('analysis_type', 'texture')
                ->get();
            
            $this->info("\n=== CONTROLES ANALÍTICOS ENCONTRADOS ===");
            $this->info("Total: " . $analyticalControls->count());
            
            foreach ($analyticalControls as $control) {
                $this->info("\n--- Control ID: " . $control->id . " ---");
                $this->info("Identificación: " . ($control->identificacion ?? 'NULL'));
                $this->info("Código interno: " . ($control->codigo_interno ?? 'NULL'));
                $this->info("Arena 1: " . ($control->arena_1 ?? 'NULL'));
                $this->info("Arcilla 1: " . ($control->arcilla_1 ?? 'NULL'));
                $this->info("Limo 1: " . ($control->limo_1 ?? 'NULL'));
                $this->info("DPR Arena: " . ($control->dpr_arena ?? 'NULL'));
                $this->info("DPR Arcilla: " . ($control->dpr_arcilla ?? 'NULL'));
                $this->info("DPR Limo: " . ($control->dpr_limo ?? 'NULL'));
                $this->info("Aceptabilidad: " . ($control->aceptabilidad_control ?? 'NULL'));
                $this->info("Observaciones: " . ($control->observaciones ?? 'NULL'));
                
                if (isset($control->controles_analiticos)) {
                    $this->info("\n--- JSON controles_analiticos ---");
                    $jsonData = json_decode($control->controles_analiticos, true);
                    if ($jsonData) {
                        foreach ($jsonData as $key => $value) {
                            $this->info("$key: " . (is_array($value) ? json_encode($value) : $value));
                        }
                    } else {
                        $this->info("Error decodificando JSON");
                    }
                }
            }
            
        } else {
            $this->info("No se encontraron análisis rechazados.");
            
            $all = \Modules\LSCEFA\Entities\BatchTextureAnalysis::all();
            $this->info("Total análisis: " . $all->count());
            
            foreach ($all as $item) {
                $this->info("ID: {$item->id}, Status: {$item->review_status}, Consecutivo: {$item->consecutive_no}");
            }
        }
    }
}
