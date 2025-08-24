<?php

namespace Modules\LSCEFA\Console\Commands;

use Illuminate\Console\Command;
use Modules\LSCEFA\Entities\AnalyticalControl;
use Modules\LSCEFA\Entities\HumidityAnalysis;
use Illuminate\Support\Facades\DB;

class FixAnalyticalControls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lscefa:fix-controls';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corregir las relaciones de los controles analíticos con los análisis de humedad';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== CORRECCIÓN DE RELACIONES DE CONTROLES ANALÍTICOS ===');
        
        DB::beginTransaction();
        
        try {
            // Obtener todos los controles analíticos que no tienen humidity_analysis_id
            $controls = AnalyticalControl::whereNull('humidity_analysis_id')->get();
            
            $this->info("Controles a corregir: {$controls->count()}");
            
            foreach ($controls as $control) {
                $this->line("Procesando control ID: {$control->id}");
                
                // Buscar el primer análisis de humedad que coincida con process_id y analysis_id
                $humidityAnalysis = HumidityAnalysis::where('process_id', $control->process_id)
                    ->where('analysis_id', $control->analysis_id)
                    ->first();
                
                if ($humidityAnalysis) {
                    $control->humidity_analysis_id = $humidityAnalysis->id;
                    $control->save();
                    
                    $this->line("  ✓ Relacionado con análisis de humedad ID: {$humidityAnalysis->id}");
                } else {
                    $this->warn("  ✗ No se encontró análisis de humedad para process_id: {$control->process_id}, analysis_id: {$control->analysis_id}");
                }
            }
            
            DB::commit();
            $this->info("\n✅ Corrección completada exitosamente");
            
            // Mostrar resumen final
            $this->showSummary();
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Error durante la corrección: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
    
    private function showSummary()
    {
        $this->info("\n--- RESUMEN FINAL ---");
        
        $totalControls = AnalyticalControl::count();
        $controlsWithHumidity = AnalyticalControl::whereNotNull('humidity_analysis_id')->count();
        $controlsWithoutHumidity = AnalyticalControl::whereNull('humidity_analysis_id')->count();
        
        $this->info("Total controles analíticos: {$totalControls}");
        $this->info("Controles con humidity_analysis_id: {$controlsWithHumidity}");
        $this->info("Controles sin humidity_analysis_id: {$controlsWithoutHumidity}");
        
        if ($controlsWithoutHumidity > 0) {
            $this->warn("⚠️  Aún hay {$controlsWithoutHumidity} controles sin relación");
        } else {
            $this->info("🎉 Todos los controles están correctamente relacionados");
        }
    }
}
