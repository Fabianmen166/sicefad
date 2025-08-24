<?php

namespace Modules\LSCEFA\Console\Commands;

use Illuminate\Console\Command;
use Modules\LSCEFA\Models\Process;
use Modules\LSCEFA\Models\Service;
use Illuminate\Support\Facades\Log;

class TestHumidityAnalysis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lscefa:test-humidity {process_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar la lógica del análisis de humedad';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $processId = $this->argument('process_id');
        
        if (!$processId) {
            $this->error('Debe proporcionar un process_id');
            return 1;
        }
        
        $this->info("=== PRUEBA DE ANÁLISIS DE HUMEDAD ===");
        $this->info("Process ID: {$processId}");
        
        try {
            // Obtener el proceso con sus relaciones necesarias
            $process = Process::with(['serviceProcessDetails.service'])
                            ->where('process_id', (string)$processId)
                            ->first();
            
            if (!$process) {
                $this->error("❌ No se encontró el proceso {$processId}");
                return 1;
            }
            
            $this->info("✅ Proceso encontrado: {$process->process_id}");
            
            // Obtener el servicio asociado si existe
            $service = optional($process->serviceProcessDetails->first())->service;
            
            if (!$service) {
                $this->error("❌ No se encontró servicio para el proceso");
                return 1;
            }
            
            $this->info("✅ Servicio encontrado: {$service->descripcion} (ID: {$service->services_id})");
            
            // Obtener el ServiceProcessDetail para este proceso y servicio
            $serviceProcessDetail = $process->serviceProcessDetails()
                ->where('service_id', $service->services_id)
                ->first();
            
            if (!$serviceProcessDetail) {
                $this->error("❌ No se encontró ServiceProcessDetail");
                return 1;
            }
            
            $this->info("✅ ServiceProcessDetail encontrado:");
            $this->line("   - ID: {$serviceProcessDetail->id}");
            $this->line("   - Process ID: {$serviceProcessDetail->process_id}");
            $this->line("   - Service ID: {$serviceProcessDetail->service_id}");
            $this->line("   - Status: {$serviceProcessDetail->status}");
            
            $this->info("\n🎉 Todas las relaciones están funcionando correctamente!");
            
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            Log::error('Error en test de humedad: ' . $e->getMessage(), [
                'processId' => $processId,
                'exception' => $e
            ]);
            return 1;
        }
        
        return 0;
    }
}
