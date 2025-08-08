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
        'item_name',
        'peso',
        'vol_extractante',
        'lectura_blanco',
        'factor_dilucion',
        'concentracion_mg_l',
        'concentracion_mg_kg',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'peso' => 'decimal:4',
        'vol_extractante' => 'decimal:2',
        'lectura_blanco' => 'decimal:4',
        'factor_dilucion' => 'decimal:4',
        'concentracion_mg_l' => 'decimal:4',
        'concentracion_mg_kg' => 'decimal:4'
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
