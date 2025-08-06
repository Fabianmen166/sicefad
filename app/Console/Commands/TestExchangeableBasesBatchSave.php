<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Modules\LSCEFA\Http\Controllers\ExchangeableBasesAnalysisController;
use Modules\LSCEFA\Models\Process;
use Modules\LSCEFA\Models\Service;
use Modules\LSCEFA\Models\ServiceProcessDetail;
use Illuminate\Support\Facades\Log;

class TestExchangeableBasesBatchSave extends Command
{
    protected $signature = 'test:exchangeable-bases-batch-save';
    protected $description = 'Test the exchangeable bases batch save functionality';

    public function handle()
    {
        $this->info('Testing Exchangeable Bases Batch Save...');

        // Buscar procesos pendientes de bases cambiables
        $pendingProcesses = Process::with(['serviceProcessDetails.service'])
            ->whereHas('serviceProcessDetails', function ($query) {
                $query->whereHas('service', function ($serviceQuery) {
                    $serviceQuery->where('descripcion', 'like', '%bases cambiables%')
                                ->orWhere('descripcion', 'like', '%exchangeable bases%')
                                ->orWhere('descripcion', 'like', '%intercambio cationico%')
                                ->orWhere('descripcion', 'like', '%cationic exchange%');
                })->where('status', 'pending');
            })
            ->get();

        if ($pendingProcesses->isEmpty()) {
            $this->error('No hay procesos pendientes de bases cambiables');
            return;
        }

        $this->info("Encontrados {$pendingProcesses->count()} procesos pendientes");

        // Crear datos de prueba
        $testData = [
            'process_ids' => $pendingProcesses->pluck('process_id')->toArray(),
            'consecutivo_no' => 'TEST-' . date('Ymd-His'),
            'metodo' => 'Método de Bases Cambiables',
            'fecha_analisis' => date('Y-m-d'),
            'equipo_utilizado' => 'Equipo de Prueba',
            'intervalo_metodo' => '0-50 cmol(+)/kg',
            'nombre_analista' => 'Analista de Prueba',
            
            // Blanco del método
            'blanco_metodo' => [
                0 => [
                    'identificacion' => 'Blanco Na',
                    'resultado' => 1.5,
                    'lcm' => 2.0,
                    'aceptabilidad' => 'Aceptable'
                ],
                1 => [
                    'identificacion' => 'Blanco K',
                    'resultado' => 1.2,
                    'lcm' => 2.0,
                    'aceptabilidad' => 'Aceptable'
                ],
                2 => [
                    'identificacion' => 'Blanco Ca',
                    'resultado' => 1.8,
                    'lcm' => 2.0,
                    'aceptabilidad' => 'Aceptable'
                ],
                3 => [
                    'identificacion' => 'Blanco Mg',
                    'resultado' => 1.3,
                    'lcm' => 2.0,
                    'aceptabilidad' => 'Aceptable'
                ]
            ],
            
            // Duplicado muestra
            'duplicado_muestra' => [
                0 => [
                    'process_id' => $pendingProcesses->first()->process_id,
                    'identificacion_muestra' => 'Muestra Test',
                    'replica_1' => 15.2,
                    'replica_2' => 15.5,
                    'dpr_1' => 1.9,
                    'elemento' => 'Na',
                    'dpr_2' => 1.9,
                    'aceptabilidad' => 'Aceptable'
                ]
            ],
            
            // Controles de calidad
            'controles_calidad' => [
                0 => [
                    'identificacion' => 'Material de Referencia o MRC Na',
                    'valor_esperado' => 25.0,
                    'valor_leido' => 24.5,
                    'porcentaje_recuperacion' => 98.0,
                    'aceptabilidad' => 'Aceptable',
                    'observaciones' => 'Prueba'
                ],
                1 => [
                    'identificacion' => 'Material de Referencia o MRC K',
                    'valor_esperado' => 20.0,
                    'valor_leido' => 19.8,
                    'porcentaje_recuperacion' => 99.0,
                    'aceptabilidad' => 'Aceptable',
                    'observaciones' => 'Prueba'
                ],
                2 => [
                    'identificacion' => 'Material de Referencia o MRC Ca',
                    'valor_esperado' => 30.0,
                    'valor_leido' => 29.5,
                    'porcentaje_recuperacion' => 98.3,
                    'aceptabilidad' => 'Aceptable',
                    'observaciones' => 'Prueba'
                ],
                3 => [
                    'identificacion' => 'Material de Referencia o MRC Mg',
                    'valor_esperado' => 15.0,
                    'valor_leido' => 14.8,
                    'porcentaje_recuperacion' => 98.7,
                    'aceptabilidad' => 'Aceptable',
                    'observaciones' => 'Prueba'
                ]
            ],
            
            // Control de estándar
            'control_estandar' => [
                0 => [
                    'estandar' => 'Na',
                    'concentracion' => 10.0,
                    'valor_leido' => 9.8,
                    'porcentaje_error' => 2.0,
                    'aceptabilidad' => 'Aceptable',
                    'observaciones' => 'Prueba'
                ],
                1 => [
                    'estandar' => 'K',
                    'concentracion' => 8.0,
                    'valor_leido' => 7.9,
                    'porcentaje_error' => 1.25,
                    'aceptabilidad' => 'Aceptable',
                    'observaciones' => 'Prueba'
                ],
                2 => [
                    'estandar' => 'Ca',
                    'concentracion' => 12.0,
                    'valor_leido' => 11.8,
                    'porcentaje_error' => 1.67,
                    'aceptabilidad' => 'Aceptable',
                    'observaciones' => 'Prueba'
                ],
                3 => [
                    'estandar' => 'Mg',
                    'concentracion' => 6.0,
                    'valor_leido' => 5.9,
                    'porcentaje_error' => 1.67,
                    'aceptabilidad' => 'Aceptable',
                    'observaciones' => 'Prueba'
                ]
            ],
            
            // Curva de calibración
            'curva_calibracion' => [
                0 => [
                    'elemento' => 'Na',
                    'r2_obtenido' => 0.998,
                    'r2_esperado' => 0.995,
                    'aceptabilidad' => 'Aceptable',
                    'observaciones' => 'Prueba'
                ],
                1 => [
                    'elemento' => 'K',
                    'r2_obtenido' => 0.997,
                    'r2_esperado' => 0.995,
                    'aceptabilidad' => 'Aceptable',
                    'observaciones' => 'Prueba'
                ],
                2 => [
                    'elemento' => 'Ca',
                    'r2_obtenido' => 0.999,
                    'r2_esperado' => 0.995,
                    'aceptabilidad' => 'Aceptable',
                    'observaciones' => 'Prueba'
                ],
                3 => [
                    'elemento' => 'Mg',
                    'r2_obtenido' => 0.996,
                    'r2_esperado' => 0.995,
                    'aceptabilidad' => 'Aceptable',
                    'observaciones' => 'Prueba'
                ]
            ]
        ];

        // Agregar items de ensayo para cada proceso
        foreach ($pendingProcesses as $process) {
            $testData['items_ensayo'][$process->process_id] = [
                0 => [
                    'codigo_interno' => 'TEST-' . $process->process_id,
                    'peso_muestra' => 10.0000,
                    'humedad' => 5.0,
                    'volumen_final' => 50.0,
                    // Na
                    'na_lectura' => 25.5,
                    'na_blanco' => 1.5,
                    'na_factor' => 1.0,
                    'na_resultado' => 0.25,
                    // K
                    'k_lectura' => 20.0,
                    'k_blanco' => 1.2,
                    'k_factor' => 1.0,
                    'k_resultado' => 0.20,
                    // Ca
                    'ca_lectura' => 30.0,
                    'ca_blanco' => 1.8,
                    'ca_factor' => 1.0,
                    'ca_resultado' => 0.30,
                    // Mg
                    'mg_lectura' => 15.0,
                    'mg_blanco' => 1.3,
                    'mg_factor' => 1.0,
                    'mg_resultado' => 0.15,
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
            $controller = new ExchangeableBasesAnalysisController();
            
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