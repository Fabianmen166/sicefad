<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\LSCEFA\Models\Service;
use Modules\LSCEFA\Models\Process;
use Modules\LSCEFA\Entities\ExchangeableBasesAnalysis;

class TestExchangeableBasesService extends Command
{
    protected $signature = 'test:exchangeable-bases-service';
    protected $description = 'Test if the exchangeable bases service is found correctly';

    public function handle()
    {
        $this->info('Testing Exchangeable Bases Service...');

        // Buscar todos los servicios
        $services = Service::all();
        $this->info("Total servicios: {$services->count()}");
        
        foreach ($services as $service) {
            $this->line("- ID: {$service->services_id}, Descripción: {$service->descripcion}");
        }

        // Buscar específicamente el servicio de bases cambiables
        $exchangeableBasesService = Service::where('descripcion', 'like', '%bases cambiables%')
                                    ->orWhere('descripcion', 'like', '%exchangeable bases%')
                                    ->orWhere('descripcion', 'like', '%intercambio cationico%')
                                    ->orWhere('descripcion', 'like', '%cationic exchange%')
                                    ->first();

        if ($exchangeableBasesService) {
            $this->info("✅ Servicio encontrado: ID {$exchangeableBasesService->services_id}, Descripción: {$exchangeableBasesService->descripcion}");
        } else {
            $this->error("❌ No se encontró el servicio de bases cambiables");
        }

        // Buscar procesos pendientes
        $pendingProcesses = Process::with(['serviceProcessDetails.service'])
            ->whereHas('serviceProcessDetails', function ($query) {
                $query->whereHas('service', function ($serviceQuery) {
                    $serviceQuery->where('descripcion', 'like', '%bases cambiables%')
                                ->orWhere('descripcion', 'like', '%exchangeable bases%');
                })->where('status', 'pending');
            })
            ->get();

        $this->info("Procesos pendientes de bases cambiables: {$pendingProcesses->count()}");
        
        foreach ($pendingProcesses as $process) {
            $this->line("- Process ID: {$process->process_id}");
            foreach ($process->serviceProcessDetails as $detail) {
                $this->line("  - Service: {$detail->service->descripcion} (ID: {$detail->service->services_id})");
            }
        }

        // Verificar si hay análisis existentes
        $analyses = ExchangeableBasesAnalysis::count();
        $this->info("Análisis de bases cambiables en BD: {$analyses}");
    }
} 