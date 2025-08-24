<?php

namespace Modules\LSCEFA\Console\Commands;

use Illuminate\Console\Command;
use Modules\LSCEFA\Entities\AnalyticalControl;

class FixSpecificControl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lscefa:fix-specific-control';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corregir un control analítico específico';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== CORRECCIÓN DE CONTROL ESPECÍFICO ===');
        
        try {
            // Corregir el control ID 2
            $control = AnalyticalControl::find(2);
            
            if ($control) {
                $control->analysis_id = 2; // Corregir a 2
                $control->humidity_analysis_id = 3; // Relacionar con el primer análisis de humedad
                $control->save();
                
                $this->info("✅ Control ID 2 corregido:");
                $this->line("   - analysis_id: {$control->analysis_id}");
                $this->line("   - humidity_analysis_id: {$control->humidity_analysis_id}");
            } else {
                $this->error("❌ No se encontró el control ID 2");
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
