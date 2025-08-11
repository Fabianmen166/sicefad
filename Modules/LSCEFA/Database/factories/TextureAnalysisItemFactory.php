<?php

namespace Modules\LSCEFA\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\LSCEFA\Entities\TextureAnalysisItem;

class TextureAnalysisItemFactory extends Factory
{
    protected $model = TextureAnalysisItem::class;

    public function definition()
    {
        $pesoArena = $this->faker->randomFloat(4, 0.1, 10.0);
        $pesoLimo = $this->faker->randomFloat(4, 0.1, 10.0);
        $pesoArcilla = $this->faker->randomFloat(4, 0.1, 10.0);
        $pesoTotal = $pesoArena + $pesoLimo + $pesoArcilla;
        
        $porcentajeArena = ($pesoArena / $pesoTotal) * 100;
        $porcentajeLimo = ($pesoLimo / $pesoTotal) * 100;
        $porcentajeArcilla = ($pesoArcilla / $pesoTotal) * 100;

        return [
            'texture_analysis_id' => $this->faker->numberBetween(1, 100),
            'codigo_interno' => $this->faker->unique()->regexify('MUESTRA-[0-9]{3}'),
            'peso_arena' => $pesoArena,
            'peso_limo' => $pesoLimo,
            'peso_arcilla' => $pesoArcilla,
            'peso_total' => $pesoTotal,
            'porcentaje_arena' => $porcentajeArena,
            'porcentaje_limo' => $porcentajeLimo,
            'porcentaje_arcilla' => $porcentajeArcilla,
            'clase_textural' => $this->faker->randomElement([
                'Arena', 'Arena Limosa', 'Arena Arcillosa', 'Limo', 'Limo Arenoso',
                'Limo Arcilloso', 'Arcilla', 'Arcilla Arenosa', 'Arcilla Limosa',
                'Franco Arenoso', 'Franco Limoso', 'Franco Arcilloso', 'Franco'
            ]),
            'observaciones' => $this->faker->optional()->sentence(),
        ];
    }
}
