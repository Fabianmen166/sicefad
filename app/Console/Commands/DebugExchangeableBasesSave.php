<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Modules\LSCEFA\Http\Controllers\ExchangeableBasesAnalysisController;
use Modules\LSCEFA\Models\Process;
use Modules\LSCEFA\Models\Service;
use Modules\LSCEFA\Entities\ExchangeableBasesAnalysis;
use Illuminate\Support\Facades\Log;

class DebugExchangeableBasesSave extends Command
{
    protected $signature = 'debug:exchangeable-bases-save';
    protected $description = 'Debug the exchangeable bases save process';

    public function handle()
    {
        $this->info('Debugging Exchangeable Bases Save...');

        // Buscar procesos pendientes
        $pendingProcesses = Process::with(['serviceProcessDetails.service'])
            ->whereHas('serviceProcessDetails', function ($query) {
                $query->whereHas('service', function ($serviceQuery) {
                    $serviceQuery->where('descripcion', 'like', '%bases cambiables%')
                                ->orWhere('descripcion', 'like', '%exchangeable bases%');
                })->where('status', 'pending');
            })
            ->get();

        if ($pendingProcesses->isEmpty()) {
            $this->error('No hay procesos pendientes de bases cambiables');
            return;
        }

        $this->info("Encontrados {$pendingProcesses->count()} procesos pendientes");

        // Crear datos de prueba simples
        $testData = [
            'process_ids' => [$pendingProcesses->first()->process_id],
            'consecutivo_no' => 'DEBUG-' . date('Ymd-His'),
            'metodo' => 'Método de Prueba',
            'fecha_analisis' => date('Y-m-d'),
            'equipo_utilizado' => 'Equipo de Prueba',
            'intervalo_metodo' => '0-50 cmol(+)/kg',
            'nombre_analista' => 'Analista de Prueba',
            
            // Solo un item de ensayo simple
            'items_ensayo' => [
                $pendingProcesses->first()->process_id => [
                    0 => [
                        'codigo_interno' => 'DEBUG-001',
                        'peso_muestra' => 10.0,
                        'humedad' => 5.0,
                        'volumen_final' => 50.0,
                        'na_lectura' => 25.5,
                        'na_blanco' => 1.5,
                        'na_factor' => 1.0,
                        'na_resultado' => 0.25,
                        'k_lectura' => 20.0,
                        'k_blanco' => 1.2,
                        'k_factor' => 1.0,
                        'k_resultado' => 0.20,
                        'ca_lectura' => 30.0,
                        'ca_blanco' => 1.8,
                        'ca_factor' => 1.0,
                        'ca_resultado' => 0.30,
                        'mg_lectura' => 15.0,
                        'mg_blanco' => 1.3,
                        'mg_factor' => 1.0,
                        'mg_resultado' => 0.15,
                        'observaciones' => 'Debug test'
                    ]
                ]
            ]
        ];

        $this->info('Datos de prueba creados');
        $this->info('Process ID: ' . $pendingProcesses->first()->process_id);

        // Crear request simulado
        $request = new Request($testData);

        try {
            // Instanciar el controlador
            $controller = new ExchangeableBasesAnalysisController();
            
            // Llamar al método batchStore
            $response = $controller->batchStore($request);
            
            $this->info('Batch save ejecutado');
            
            // Verificar si se guardó algo
            $count = ExchangeableBasesAnalysis::count();
            $this->info("Total análisis en BD: {$count}");
            
            if ($count > 0) {
                $latest = ExchangeableBasesAnalysis::latest()->first();
                $this->info("Último análisis: Process ID: {$latest->process_id}, Internal Code: {$latest->internal_code}");
            }
            
        } catch (\Exception $e) {
            $this->error('Error en batch save: ' . $e->getMessage());
            $this->error('Trace: ' . $e->getTraceAsString());
        }
    }
} 