<?php

namespace Modules\LSCEFA\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MicronutrientsAnalysisItem extends Model
{
    use HasFactory;

    protected $table = 'micronutrients_analysis_items';

    protected $fillable = [
        'analysis_id',
        'codigo_interno',
        'peso_muestra',
        'humedad',
        'volumen_final',

        // Mn
        'mn_lectura',
        'mn_factor',
        'mn_resultado',

        // Fe
        'fe_lectura',
        'fe_factor',
        'fe_resultado',

        // Zn
        'zn_lectura',
        'zn_factor',
        'zn_resultado',

        // Cu
        'cu_lectura',
        'cu_factor',
        'cu_resultado',

        'observaciones',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'peso_muestra' => 'decimal:4',
        'humedad' => 'decimal:2',
        'volumen_final' => 'decimal:2',

        'mn_lectura' => 'decimal:4',
        'mn_factor' => 'decimal:4',
        'mn_resultado' => 'decimal:4',

        'fe_lectura' => 'decimal:4',
        'fe_factor' => 'decimal:4',
        'fe_resultado' => 'decimal:4',

        'zn_lectura' => 'decimal:4',
        'zn_factor' => 'decimal:4',
        'zn_resultado' => 'decimal:4',

        'cu_lectura' => 'decimal:4',
        'cu_factor' => 'decimal:4',
        'cu_resultado' => 'decimal:4',
    ];

    public function analysis()
    {
        return $this->belongsTo(MicronutrientsAnalysis::class, 'analysis_id');
    }

    protected static function newFactory()
    {
        return \Modules\LSCEFA\Database\factories\MicronutrientsAnalysisItemFactory::new();
    }
}
