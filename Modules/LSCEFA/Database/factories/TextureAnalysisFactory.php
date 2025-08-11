<?php

namespace Modules\LSCEFA\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\LSCEFA\Entities\TextureAnalysis;

class TextureAnalysisFactory extends Factory
{
    protected $model = TextureAnalysis::class;

    public function definition()
    {
        return [
            'process_id' => $this->faker->unique()->regexify('[A-Z]{2}[0-9]{6}'),
            'service_id' => $this->faker->numberBetween(1, 100),
            'consecutivo_no' => $this->faker->unique()->regexify('TXT-[0-9]{4}-[0-9]{3}'),
            'fecha_analisis' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'equipo_utilizado' => $this->faker->randomElement(['Tamiz 2mm', 'Tamiz 0.05mm', 'Tamiz 0.002mm', 'Balanza analítica']),
            'intervalo_metodo' => $this->faker->randomElement(['Método del hidrómetro', 'Método de la pipeta', 'Método del tamiz']),
            'analista' => $this->faker->name(),
            'user_id' => $this->faker->numberBetween(1, 50),
        ];
    }
}
