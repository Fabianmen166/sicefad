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
        DB::table('services')->insert([
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
        ]);
    }
} 