<?php

namespace Modules\LSCEFA\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServicesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $services = [
            [
                'services_id' => 1,
                'descripcion' => 'pH, potenciométrico, NTC 5264:2018*',
                'precio' => 15000,
                'acreditado' => 1,
            ],
            [
                'services_id' => 2,
                'descripcion' => 'Conductividad eléctrica (CE), potenciométrico, NTC 5596:2022 Método B',
                'precio' => 13000,
                'acreditado' => 0,
            ],
            [
                'services_id' => 3,
                'descripcion' => 'Azufre disponible, método turbidimétrico, NTC 5167:2018',
                'precio' => 18000,
                'acreditado' => 1,
            ],
            [
                'services_id' => 4,
                'descripcion' => 'Análisis de AZUFRE en suelos agrícolas, método colorimétrico',
                'precio' => 16000,
                'acreditado' => 0,
            ],
            [
                'services_id' => 5,
                'descripcion' => 'Determinación de SULFUR en muestras de suelo, método espectrofotométrico',
                'precio' => 20000,
                'acreditado' => 1,
            ],
            [
                'services_id' => 6,
                'descripcion' => 'AZUFRE asimilable en suelos, método turbidimétrico modificado',
                'precio' => 17000,
                'acreditado' => 0,
            ],
            [
                'services_id' => 7,
                'descripcion' => 'Análisis de azufre elemental en muestras de suelo, método gravimétrico',
                'precio' => 22000,
                'acreditado' => 1,
            ],
            [
                'services_id' => 8,
                'descripcion' => 'Boro disponible, método colorimétrico, NTC 5167:2018',
                'precio' => 19000,
                'acreditado' => 1,
            ],
            [
                'services_id' => 9,
                'descripcion' => 'Análisis de BORO en suelos agrícolas, método espectrofotométrico',
                'precio' => 17000,
                'acreditado' => 0,
            ],
            [
                'services_id' => 10,
                'descripcion' => 'Determinación de BORON en muestras de suelo, método turbidimétrico',
                'precio' => 21000,
                'acreditado' => 1,
            ],
            [
                'services_id' => 11,
                'descripcion' => 'BORO asimilable en suelos, método colorimétrico modificado',
                'precio' => 18000,
                'acreditado' => 0,
            ],
            [
                'services_id' => 12,
                'descripcion' => 'Análisis de boro elemental en muestras de suelo, método gravimétrico',
                'precio' => 24000,
                'acreditado' => 1,
            ],
        ];

        foreach ($services as $service) {
            DB::table('services')->updateOrInsert(
                ['services_id' => $service['services_id']],
                $service
            );
        }
    }
} 