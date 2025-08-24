<?php

namespace Modules\LSCEFA\Console\Commands;

use Illuminate\Console\Command;
use Modules\LSCEFA\Entities\AnalyticalControl;
use Modules\LSCEFA\Entities\HumidityAnalysis;
use Modules\LSCEFA\Models\ServiceProcessDetail;

class CheckAnalyticalControls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lscefa:check-controls {process_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificar controles analíticos en la base de datos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $processId = $this->argument('process_id');
        
        $this->info('=== VERIFICACIÓN DE CONTROLES ANALÍTICOS ===');
        
        if ($processId) {
            $this->checkProcessControls($processId);
        } else {
            $this->checkAllControls();
        }
        
        return 0;
    }
    
    private function checkProcessControls($processId)
    {
        $this->info("\n--- Controles para Process ID: {$processId} ---");
        
        // Verificar controles analíticos
        $controls = AnalyticalControl::where('process_id', $processId)->get();
        $this->info("Controles analíticos encontrados: {$controls->count()}");
        
        foreach ($controls as $control) {
            $this->line("  - ID: {$control->id}");
            $this->line("    Analysis ID: {$control->analysis_id}");
            $this->line("    Humidity Analysis ID: {$control->humidity_analysis_id}");
            $this->line("    Process ID: {$control->process_id}");
            $this->line("    Created: {$control->created_at}");
        }
        
        // Verificar análisis de humedad
        $humidityAnalyses = HumidityAnalysis::where('process_id', $processId)->get();
        $this->info("\nAnálisis de humedad encontrados: {$humidityAnalyses->count()}");
        
        foreach ($humidityAnalyses as $analysis) {
            $this->line("  - ID: {$analysis->id}");
            $this->line("    Analysis ID: {$analysis->analysis_id}");
            $this->line("    Process ID: {$analysis->process_id}");
            $this->line("    Consecutivo: {$analysis->consecutivo_no}");
        }
        
        // Verificar detalles de servicio
        $serviceDetails = ServiceProcessDetail::where('process_id', $processId)->get();
        $this->info("\nDetalles de servicio encontrados: {$serviceDetails->count()}");
        
        foreach ($serviceDetails as $detail) {
            $this->line("  - ID: {$detail->id}");
            $this->line("    Process ID: {$detail->process_id}");
            $this->line("    Service ID: {$detail->service_id}");
            $this->line("    Status: {$detail->status}");
        }
    }
    
    private function checkAllControls()
    {
        $this->info("\n--- RESUMEN GENERAL ---");
        
        $totalControls = AnalyticalControl::count();
        $totalHumidity = HumidityAnalysis::count();
        $totalServiceDetails = ServiceProcessDetail::count();
        
        $this->info("Total controles analíticos: {$totalControls}");
        $this->info("Total análisis de humedad: {$totalHumidity}");
        $this->info("Total detalles de servicio: {$totalServiceDetails}");
        
        // Verificar controles sin process_id
        $controlsWithoutProcess = AnalyticalControl::whereNull('process_id')->count();
        if ($controlsWithoutProcess > 0) {
            $this->warn("Controles sin process_id: {$controlsWithoutProcess}");
        }
        
        // Verificar controles sin analysis_id
        $controlsWithoutAnalysis = AnalyticalControl::whereNull('analysis_id')->count();
        if ($controlsWithoutAnalysis > 0) {
            $this->warn("Controles sin analysis_id: {$controlsWithoutAnalysis}");
        }
        
        // Verificar controles sin humidity_analysis_id
        $controlsWithoutHumidity = AnalyticalControl::whereNull('humidity_analysis_id')->count();
        if ($controlsWithoutHumidity > 0) {
            $this->warn("Controles sin humidity_analysis_id: {$controlsWithoutHumidity}");
        }
    }
}
