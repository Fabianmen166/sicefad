<?php

namespace Modules\LSCEFA\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\LSCEFA\Models\Process;
use Modules\LSCEFA\Models\ServiceProcessDetail;

class ProcessesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Crear usuario de prueba
        DB::table('users')->updateOrInsert(
            ['id' => 1],
            [
                'id' => 1,
                'person_id' => 1,
                'email' => 'admin@test.com',
                'nickname' => 'admin',
                'password' => bcrypt('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Crear customer_type de prueba
        DB::table('customer_types')->updateOrInsert(
            ['customer_type_id' => 1],
            [
                'customer_type_id' => 1,
                'name' => 'Empresa',
                'discount_percentage' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Crear cliente de prueba
        DB::table('customers')->updateOrInsert(
            ['customer_id' => 1],
            [
                'customer_id' => 1,
                'customer_type_id' => 1,
                'tax_id' => '123456789',
                'applicant' => 'Cliente de Prueba',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Crear cotizaciones de prueba
        $quotes = [
            [
                'quote_id' => 'TEST-QUOTE-001',
                'customer_id' => 1,
                'user_id' => 1,
                'total' => 18000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'quote_id' => 'TEST-QUOTE-002',
                'customer_id' => 1,
                'user_id' => 1,
                'total' => 20000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'quote_id' => 'TEST-QUOTE-003',
                'customer_id' => 1,
                'user_id' => 1,
                'total' => 17000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insertar cotizaciones
        foreach ($quotes as $quote) {
            DB::table('quotes')->updateOrInsert(
                ['quote_id' => $quote['quote_id']],
                $quote
            );
        }

        // Crear procesos de prueba
        $processes = [
            [
                'process_id' => 'P-2025-001',
                'quote_id' => 'TEST-QUOTE-001',
                'item_code' => 'ITEM-001',
                'status' => 'pending',
                'client_communication' => 'Análisis de azufre disponible en suelos agrícolas',
                'processing_days' => 5,
                'reception_date' => now(),
                'description' => 'Muestras de suelo para análisis de azufre disponible',
                'sampling_place' => 'Finca La Esperanza',
                'sampling_date' => now()->subDays(2),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'process_id' => 'P-2025-002',
                'quote_id' => 'TEST-QUOTE-002',
                'item_code' => 'ITEM-002',
                'status' => 'pending',
                'client_communication' => 'Determinación de sulfur en muestras de suelo',
                'processing_days' => 7,
                'reception_date' => now(),
                'description' => 'Análisis de sulfur por método espectrofotométrico',
                'sampling_place' => 'Hacienda San Pedro',
                'sampling_date' => now()->subDays(1),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'process_id' => 'P-2025-003',
                'quote_id' => 'TEST-QUOTE-003',
                'item_code' => 'ITEM-003',
                'status' => 'pending',
                'client_communication' => 'AZUFRE asimilable en suelos',
                'processing_days' => 6,
                'reception_date' => now(),
                'description' => 'Análisis de azufre asimilable por método turbidimétrico',
                'sampling_place' => 'Granja El Paraíso',
                'sampling_date' => now()->subDays(3),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'process_id' => 'P-2025-005',
                'quote_id' => 'TEST-QUOTE-002',
                'item_code' => 'ITEM-005',
                'status' => 'pending',
                'client_communication' => 'Análisis de azufre elemental en muestras de suelo',
                'processing_days' => 7,
                'reception_date' => now(),
                'description' => 'Muestras de suelo para análisis de azufre elemental',
                'sampling_place' => 'Parcela Norte',
                'sampling_date' => now()->subDays(3),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Procesos de Boro
            [
                'process_id' => 'P-2025-006',
                'quote_id' => 'TEST-QUOTE-003',
                'item_code' => 'ITEM-006',
                'status' => 'pending',
                'client_communication' => 'Análisis de boro disponible en suelos agrícolas',
                'processing_days' => 5,
                'reception_date' => now(),
                'description' => 'Muestras de suelo para análisis de boro disponible',
                'sampling_place' => 'Finca La Esperanza',
                'sampling_date' => now()->subDays(2),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'process_id' => 'P-2025-007',
                'quote_id' => 'TEST-QUOTE-001',
                'item_code' => 'ITEM-007',
                'status' => 'pending',
                'client_communication' => 'Análisis de BORO en suelos agrícolas',
                'processing_days' => 7,
                'reception_date' => now(),
                'description' => 'Muestras de suelo para análisis de BORO',
                'sampling_place' => 'Parcela Norte',
                'sampling_date' => now()->subDays(3),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'process_id' => 'P-2025-008',
                'quote_id' => 'TEST-QUOTE-002',
                'item_code' => 'ITEM-008',
                'status' => 'pending',
                'client_communication' => 'Determinación de BORON en muestras de suelo',
                'processing_days' => 6,
                'reception_date' => now(),
                'description' => 'Muestras de suelo para determinación de BORON',
                'sampling_place' => 'Campo Experimental',
                'sampling_date' => now()->subDays(1),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'process_id' => 'P-2025-009',
                'quote_id' => 'TEST-QUOTE-003',
                'item_code' => 'ITEM-009',
                'status' => 'pending',
                'client_communication' => 'BORO asimilable en suelos',
                'processing_days' => 5,
                'reception_date' => now(),
                'description' => 'Muestras de suelo para BORO asimilable',
                'sampling_place' => 'Finca La Esperanza',
                'sampling_date' => now()->subDays(2),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'process_id' => 'P-2025-010',
                'quote_id' => 'TEST-QUOTE-001',
                'item_code' => 'ITEM-010',
                'status' => 'pending',
                'client_communication' => 'Análisis de boro elemental en muestras de suelo',
                'processing_days' => 7,
                'reception_date' => now(),
                'description' => 'Muestras de suelo para análisis de boro elemental',
                'sampling_place' => 'Parcela Norte',
                'sampling_date' => now()->subDays(3),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Crear procesos y sus detalles de servicio
        foreach ($processes as $processData) {
            $process = Process::updateOrCreate(
                ['process_id' => $processData['process_id']],
                $processData
            );

            // Mapear procesos a servicios específicos
            $serviceMapping = [
                'P-2025-001' => 3, // Azufre disponible
                'P-2025-002' => 4, // Análisis de AZUFRE
                'P-2025-003' => 5, // Determinación de SULFUR
                'P-2025-005' => 7, // Análisis de azufre elemental
                'P-2025-006' => 8, // Boro disponible
                'P-2025-007' => 9, // Análisis de BORO
                'P-2025-008' => 10, // Determinación de BORON
                'P-2025-009' => 11, // BORO asimilable
                'P-2025-010' => 12, // Análisis de boro elemental
            ];

            $serviceId = $serviceMapping[$processData['process_id']] ?? 3;

            ServiceProcessDetail::updateOrCreate(
                [
                    'process_id' => $process->process_id,
                    'service_id' => $serviceId,
                ],
                [
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
