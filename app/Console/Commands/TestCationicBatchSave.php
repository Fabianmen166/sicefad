<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Modules\LSCEFA\Http\Controllers\CationicAnalysisController;
use Modules\LSCEFA\Models\Process;
use Modules\LSCEFA\Models\Service;
use Modules\LSCEFA\Models\ServiceProcessDetail;
use Illuminate\Support\Facades\Log;

class TestCationicBatchSave extends Command
{
    protected $signature = 'test:cationic-batch-save';
    protected $description = 'Test the cationic batch save functionality';

    public function handle()
    {
        $this->info('Testing Cationic Batch Save...');

        // Buscar procesos pendientes de intercambio catiónico
        $pendingProcesses = Process::with(['serviceProcessDetails.service'])
            ->whereHas('serviceProcessDetails', function ($query) {
                $query->whereHas('service', function ($serviceQuery) {
                    $serviceQuery->where('descripcion', 'like', '%intercambio%')
                                ->orWhere('descripcion', 'like', '%catiónico%')
                                ->orWhere('descripcion', 'like', '%cationic%');
                })->where('status', 'pending');
            })
            ->get();

        if ($pendingProcesses->isEmpty()) {
            $this->error('No hay procesos pendientes de intercambio catiónico');
            return;
        }

        $this->info("Encontrados {$pendingProcesses->count()} procesos pendientes");

        // Crear datos de prueba
        $testData = [
            'process_ids' => $pendingProcesses->pluck('process_id')->toArray(),
            'consecutivo_no' => 'TEST-' . date('Ymd-His'),
            'fecha_analisis' => date('Y-m-d'),
            'unidades_reporte_equipo' => 'cmol(+)/kg',
            'nombre_metodo' => 'Método de Intercambio Catiónico',
            'equipo_utilizado' => 'Equipo de Prueba',
            'intervalo_metodo' => '0-50 cmol(+)/kg',
            'nombre_analista' => 'Analista de Prueba',
            'resolucion_instrumental' => '0.01',
            'observaciones' => 'Prueba de batch save',
            
            // Controles de calidad
            'blanco_identificacion' => 'Blanco del método',
            'blanco_lcm' => 2.61,
            'blanco_valor_leido' => 1.5,
            'blanco_aceptable' => 'Aceptable',
            'blanco_observaciones' => 'Prueba',
            
            'error_identificacion' => 'Muestra Referencia Certificada',
            'error_valor_teorico' => 25.0,
            'error_valor_leido' => 24.5,
            'error_porcentaje' => 2.0,
            'error_aceptable' => 'Aceptable',
            'error_observaciones' => 'Prueba',
            
            'recuperacion_identificacion' => 'Muestra Fortificada',
            'recuperacion_valor_teorico' => 10.0,
            'recuperacion_valor_leido' => 9.8,
            'recuperacion_porcentaje' => 98.0,
            'recuperacion_aceptable' => 'Aceptable',
            'recuperacion_observaciones' => 'Prueba',
            
            'dpr_identificacion' => 'Muestra Duplicada',
            'dpr_replica1' => 15.2,
            'dpr_replica2' => 15.5,
            'dpr_porcentaje' => 1.9,
            'dpr_aceptable' => 'Aceptable',
            'dpr_observaciones' => 'Prueba',
        ];

        // Agregar items de ensayo para cada proceso
        foreach ($pendingProcesses as $process) {
            $testData['items_ensayo'][$process->process_id] = [
                0 => [
                    'codigo_interno' => 'TEST-' . $process->process_id,
                    'peso_muestra' => 10.0000,
                    'vol_naoh_muestra' => 25.50,
                    'vol_naoh_blanco' => 0.50,
                    'normalidad_naoh' => 0.1,
                    'humedad_porcentaje' => 5.0,
                    'cic_resultado' => 25.0,
                    'observaciones' => 'Item de prueba'
                ]
            ];
        }

        $this->info('Datos de prueba creados');
        $this->info('Process IDs: ' . implode(', ', $testData['process_ids']));

        // Crear request simulado
        $request = new Request($testData);

        try {
            // Instanciar el controlador
            $controller = new CationicAnalysisController();
            
            // Llamar al método batchStore
            $response = $controller->batchStore($request);
            
            $this->info('Batch save ejecutado exitosamente');
            $this->info('Response: ' . $response->getContent());
            
        } catch (\Exception $e) {
            $this->error('Error en batch save: ' . $e->getMessage());
            $this->error('Trace: ' . $e->getTraceAsString());
        }
    }
} 